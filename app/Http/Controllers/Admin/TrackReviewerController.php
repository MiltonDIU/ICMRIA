<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubTrack;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\ChairScope;
use Gate;
use Illuminate\Http\Request;
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
 */
class TrackReviewerController extends Controller
{
    private const ROLE_REVIEWER = 4;

    public function index()
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());

        $reviewers = TrackAssignment::with(['user', 'track', 'subTrack'])
            ->where('role', 'reviewer')
            ->whereIn('track_id', $scope->trackIds() ?: [0])
            ->get()
            ->groupBy(fn ($a) => $this->scopeKey($a->track_id, $a->sub_track_id));

        return view('admin.track_reviewers.index', [
            'scopes' => $scope->manageableScopes(),
            'reviewersByScope' => $reviewers,
            'seesEverything' => $scope->seesEverything(),
            'hasNoScope' => $scope->isEmpty(),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'sub_track_id' => 'nullable|exists:sub_tracks,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'expertise' => 'nullable|string|max:1000',
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

        $created = false;

        DB::transaction(function () use ($data, $subTrackId, &$created) {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    // The reviewer sets their own through the password reset link.
                    'password' => Hash::make(Str::random(32)),
                ]);
                $created = true;
            }

            // No profile is created: that record describes a delegate who registers
            // and pays. A reviewer is a user with track assignments.
            $user->roles()->syncWithoutDetaching([self::ROLE_REVIEWER]);

            $assignment = TrackAssignment::firstOrNew([
                'user_id' => $user->id,
                'track_id' => $data['track_id'],
                'sub_track_id' => $subTrackId,
                'role' => 'reviewer',
            ]);

            if (!empty($data['expertise'])) {
                $assignment->expertise = \App\Services\SubmissionRules::splitKeywords($data['expertise']);
            }

            $assignment->save();
        });

        return back()->with('success', $created
            ? 'Reviewer account created and added. They will need to set a password through the reset link.'
            : 'Reviewer added to this track.');
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
