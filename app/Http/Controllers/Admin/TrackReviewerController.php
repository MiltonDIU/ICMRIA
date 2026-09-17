<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaperReviewerAssignment;
use App\Models\Setting;
use App\Models\SubTrack;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\ChairScope;
use App\Services\SubmissionRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a chair build the reviewer pool for the scope they are responsible for.
 *
 * Both kinds of chair use this screen: an overall Track Chair manages the track and
 * every sub-track under it, a Sub-Track Chair only their own. Which scopes appear is
 * decided by ChairScope, so nobody can add reviewers somewhere they do not belong.
 *
 * There are two ways in, because they answer different needs. Picking from the people
 * who already hold the Reviewer role lets one reviewer serve several tracks without a
 * second account &mdash; typing an address by hand was the only route before, and one
 * wrong letter silently created a duplicate person. Inviting by name and address stays
 * for people the conference has not met yet.
 */
class TrackReviewerController extends Controller
{
    private const ROLE_REVIEWER = 4;

    public function index()
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());
        $scopes = $scope->manageableScopes();

        $reviewers = TrackAssignment::with(['user', 'track', 'subTrack'])
            ->where('role', 'reviewer')
            ->whereIn('track_id', $scope->trackIds() ?: [0])
            ->get();

        $pool = $this->existingReviewers();

        // One query for everybody on the page: the chair needs to see how much each
        // reviewer is already carrying before adding another paper to the pile.
        $loads = $this->loadsFor(
            $pool->pluck('id')->merge($reviewers->pluck('user_id'))->filter()->unique()->values()->all()
        );

        return view('admin.track_reviewers.index', [
            'scopes' => $scopes,
            'reviewersByScope' => $reviewers->groupBy(fn ($a) => $this->scopeKey($a->track_id, $a->sub_track_id)),
            'pool' => $pool->map(fn (array $person) => $person + ['load' => $loads[$person['id']]['open'] ?? 0]),
            'scopeTopics' => $this->topicsByScope($scopes),
            'seesEverything' => $scope->seesEverything(),
            'hasNoScope' => $scope->isEmpty(),
            'loads' => $loads,
            'defaultCapacity' => $this->defaultCapacity(),
        ]);
    }

    /**
     * One reviewer's profile: who they are, what they cover, and every paper already
     * sitting with them.
     *
     * Scoped to the viewer's tracks: a chair may only open profiles of reviewers who
     * serve at least one track they manage. Papers from other tracks are still counted
     * for an honest workload picture, but their title and authors are withheld.
     */
    public function show(User $user)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(
            !$user->roles->contains('id', self::ROLE_REVIEWER),
            Response::HTTP_NOT_FOUND,
            'That person does not hold the Reviewer role.'
        );

        $viewer = ChairScope::for(auth()->user());

        // Chairs may only open profiles of reviewers who serve at least one
        // track they manage. Admins and TPC Chair see everyone.
        if (!$viewer->seesEverything()) {
            $viewerTrackIds = $viewer->trackIds();
            $reviewerInScope = TrackAssignment::where('user_id', $user->id)
                ->where('role', 'reviewer')
                ->whereIn('track_id', $viewerTrackIds ?: [0])
                ->exists();
            abort_if(!$reviewerInScope, Response::HTTP_FORBIDDEN, '403 Forbidden — that reviewer is not in your tracks.');
        }

        $assignments = PaperReviewerAssignment::with(['paper.track', 'paper.subTrack', 'evaluation'])
            ->where('reviewer_id', $user->id)
            ->whereHas('paper')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.track_reviewers.show', [
            'reviewer' => $user->load('profile.country'),
            'expertise' => $user->allExpertise(),
            'scopes' => TrackAssignment::with(['track', 'subTrack'])
                ->where('user_id', $user->id)
                ->where('role', 'reviewer')
                ->get()
                ->sortBy(fn ($row) => [$row->track->name ?? '', $row->subTrack->name ?? '']),
            'rows' => $assignments->map(fn (PaperReviewerAssignment $assignment) => [
                'assignment' => $assignment,
                'paper' => $assignment->paper,
                'visible' => $viewer->canSee($assignment->paper),
            ]),
            'load' => $this->loadsFor([$user->id])[$user->id] ?? ['open' => 0, 'done' => 0, 'declined' => 0, 'total' => 0],
            'defaultCapacity' => $this->defaultCapacity(),
        ]);
    }

    /**
     * How many papers each of these reviewers holds, split by what the count means:
     * "open" is the live workload the matcher weighs, "done" the evaluations already in,
     * "declined" the ones they turned back. Papers that have since been deleted are left
     * out, so a count never points at something a chair cannot open.
     *
     * @param array<int, int> $userIds
     * @return array<int, array{open: int, done: int, declined: int, total: int}>
     */
    private function loadsFor(array $userIds): array
    {
        if (!$userIds) {
            return [];
        }

        return PaperReviewerAssignment::whereIn('reviewer_id', $userIds)
            ->whereHas('paper')
            ->selectRaw("reviewer_id,
                SUM(CASE WHEN status IN ('invited', 'accepted', 'in_progress') THEN 1 ELSE 0 END) AS open_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS done_count,
                SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) AS declined_count,
                COUNT(*) AS total_count")
            ->groupBy('reviewer_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->reviewer_id => [
                'open' => (int) $row->open_count,
                'done' => (int) $row->done_count,
                'declined' => (int) $row->declined_count,
                'total' => (int) $row->total_count,
            ]])
            ->all();
    }

    /** The conference-wide ceiling, which a track may raise or lower for itself. */
    private function defaultCapacity(): int
    {
        return (int) (Setting::where('key', 'max_papers_per_reviewer')->value('value') ?: 10);
    }

    /**
     * Everyone who already holds the Reviewer role, conference-wide.
     *
     * Deliberately not narrowed to the chair's own tracks: the whole point is to reach
     * a reviewer who came in through somebody else's track. Each person carries the
     * union of the expertise recorded against their assignments, so the screen can
     * show a chair what they cover before adding them.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function existingReviewers(): Collection
    {
        $byUser = TrackAssignment::with('track:id,name')
            ->where('role', 'reviewer')
            ->get()
            ->groupBy('user_id');

        return User::whereHas('roles', fn ($q) => $q->where('roles.id', self::ROLE_REVIEWER))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'research_keywords'])
            ->map(function (User $user) use ($byUser) {
                $rows = $byUser[$user->id] ?? collect();
                $trackExpertise = $this->expertiseAcross($rows);
                $expertise = SubmissionRules::splitKeywords(array_merge(
                    $trackExpertise,
                    SubmissionRules::splitKeywords($user->research_keywords)
                ));

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'expertise' => $expertise,
                    'slugs' => array_map([SubmissionRules::class, 'normaliseKeyword'], $expertise),
                    // "Track 4: Engineering, Robotics &hellip;" is too long for an option
                    // label; the number alone tells a chair where they already serve.
                    'tracks' => $rows->pluck('track.name')
                        ->filter()
                        ->map(fn ($name) => Str::of($name)->before(':')->trim()->toString())
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->values();
    }

    /**
     * The comparable topics of each scope on screen, so the picker can mark the people
     * whose expertise overlaps the subject.
     *
     * @param Collection<int, array{track: \App\Models\Track, sub_track: SubTrack|null}> $scopes
     * @return array<string, array<int, string>>
     */
    private function topicsByScope(Collection $scopes): array
    {
        $topics = [];

        foreach ($scopes as $scope) {
            $key = $this->scopeKey($scope['track']->id, $scope['sub_track']->id ?? null);

            $topics[$key] = array_map(
                [SubmissionRules::class, 'normaliseKeyword'],
                SubmissionRules::topicsFrom($scope['sub_track']->name ?? $scope['track']->name)
            );
        }

        return $topics;
    }

    /**
     * The union of the expertise recorded across a set of assignments.
     *
     * @param Collection<int, TrackAssignment> $rows
     * @return array<int, string>
     */
    private function expertiseAcross(Collection $rows): array
    {
        return SubmissionRules::splitKeywords(
            $rows->flatMap(fn ($row) => SubmissionRules::splitKeywords($row->expertise))->all()
        );
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'sub_track_id' => 'nullable|exists:sub_tracks,id',
            // Either pick somebody who already reviews, or name a new person.
            'reviewer_id' => 'nullable|integer|exists:users,id',
            'name' => 'required_without:reviewer_id|nullable|string|max:255',
            'email' => 'required_without:reviewer_id|nullable|email|max:255',
            'expertise' => 'nullable|string|max:1000',
        ], [
            'name.required_without' => 'Give a name, or choose somebody from the existing reviewers.',
            'email.required_without' => 'Give an email address, or choose somebody from the existing reviewers.',
        ]);

        $subTrackId = $data['sub_track_id'] ?: null;

        abort_if(
            !ChairScope::for(auth()->user())->canManage((int) $data['track_id'], $subTrackId ? (int) $subTrackId : null),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden - that track is not yours to manage.'
        );

        if ($subTrackId && !SubTrack::where('id', $subTrackId)->where('track_id', $data['track_id'])->exists()) {
            return back()->with('error', 'That sub-track does not belong to the chosen track.');
        }

        $outcome = 'added';
        $who = null;

        DB::transaction(function () use ($data, $subTrackId, &$outcome, &$who) {
            if (!empty($data['reviewer_id'])) {
                $user = User::with('roles')->find($data['reviewer_id']);

                // The picker moves people between tracks; it does not hand out the role.
                // Granting Reviewer stays with the administrator (document, Phase 1).
                if (!$user->roles->contains('id', self::ROLE_REVIEWER)) {
                    $outcome = 'not_a_reviewer';
                    $who = $user->name;

                    return;
                }
            } else {
                $user = User::where('email', $data['email'])->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        // The reviewer sets their own through the password reset link.
                        'password' => Hash::make(Str::random(32)),
                    ]);
                    $outcome = 'created';
                }
            }

            $who = $user->name;

            // No profile is created: that record describes a delegate who registers
            // and pays. A reviewer is a user with track assignments.
            $user->roles()->syncWithoutDetaching([self::ROLE_REVIEWER]);

            $assignment = TrackAssignment::firstOrNew([
                'user_id' => $user->id,
                'track_id' => $data['track_id'],
                'sub_track_id' => $subTrackId,
                'role' => 'reviewer',
            ]);

            $alreadyHere = $assignment->exists;

            $existingTopics = $user->allExpertise();
            $newTopics = !empty($data['expertise']) ? SubmissionRules::splitKeywords($data['expertise']) : [];

            $combinedTopics = !empty($newTopics)
                ? SubmissionRules::splitKeywords(array_merge($existingTopics, $newTopics))
                : (!empty($existingTopics) ? $existingTopics : SubmissionRules::splitKeywords(
                    \App\Models\SubTrack::where('id', $subTrackId)->value('name') ?? \App\Models\Track::where('id', $data['track_id'])->value('name')
                ));

            if ($alreadyHere) {
                $outcome = empty($data['expertise']) ? 'already' : 'updated';
            }

            $assignment->expertise = $combinedTopics;
            $assignment->save();

            // Accumulate into user profile and keep all track assignments unified
            $user->update(['research_keywords' => $combinedTopics]);
            TrackAssignment::where('user_id', $user->id)
                ->where('role', 'reviewer')
                ->update(['expertise' => $combinedTopics]);

            \Illuminate\Support\Facades\Cache::forget('suggested_research_areas');
        });

        $messages = [
            'created' => "Account created for {$who} and added to this track. They set their own password through the reset link.",
            'added' => "{$who} added to this track. Their other tracks are untouched.",
            'updated' => "{$who} was already a reviewer here; their expertise for this track has been updated.",
            'already' => "{$who} is already a reviewer here, so nothing changed. Fill in the expertise field to update what they cover.",
            'not_a_reviewer' => "{$who} does not hold the Reviewer role. An administrator grants it under Users.",
        ];

        return back()->with(
            in_array($outcome, ['already', 'not_a_reviewer'], true) ? 'error' : 'success',
            $messages[$outcome]
        );
    }

    public function destroy(TrackAssignment $trackAssignment)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if($trackAssignment->role !== 'reviewer', Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(
            !ChairScope::for(auth()->user())->canManage($trackAssignment->track_id, $trackAssignment->sub_track_id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden - that track is not yours to manage.'
        );

        $trackAssignment->delete();

        return back()->with('success', 'Reviewer removed from this track. Their account and other tracks are untouched.');
    }

    private function scopeKey(int $trackId, ?int $subTrackId): string
    {
        return $trackId . ':' . ($subTrackId ?? 'all');
    }
}
