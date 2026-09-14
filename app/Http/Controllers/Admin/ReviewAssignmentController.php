<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperReviewerAssignment;
use App\Models\User;
use App\Services\ChairScope;
use App\Services\ReviewerMatcher;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * Where a chair hands papers to reviewers (requirement document, Phase 3).
 *
 * Both kinds of chair work here — a Track Chair across their whole track, a
 * Sub-Track Chair within their own sub-track — and ChairScope decides which papers
 * each of them sees. The TPC Chair does not assign; they grant final approval later.
 */
class ReviewAssignmentController extends Controller
{
    private ReviewerMatcher $matcher;

    public function __construct(ReviewerMatcher $matcher)
    {
        $this->matcher = $matcher;
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());

        $papers = Paper::with(['track', 'subTrack', 'reviewerAssignments.reviewer'])
            ->whereIn('track_id', $scope->trackIds() ?: [0])
            ->when(!$scope->seesEverything(), function ($query) use ($scope) {
                $query->where(function ($q) use ($scope) {
                    $q->whereIn('sub_track_id', $scope->subTrackIds() ?: [0])
                      ->orWhereIn('track_id', $scope->wholeTrackIds() ?: [0]);
                });
            })
            ->when($request->filled('state'), function ($query) use ($request) {
                $wanted = $request->string('state')->toString();
                $query->when($wanted === 'unassigned', fn ($q) => $q->doesntHave('reviewerAssignments'))
                      ->when($wanted === 'assigned', fn ($q) => $q->has('reviewerAssignments'));
            })
            ->orderBy('id')
            ->get();

        $minimum = $this->matcher->minimumReviewers();

        return view('admin.review_assignments.index', [
            'papers' => $papers,
            'minimum' => $minimum,
            'hasNoScope' => $scope->isEmpty(),
            'state' => $request->string('state')->toString(),
        ]);
    }

    /** The one paper a chair is working on, with the candidates ranked. */
    public function show(Paper $paper)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);

        $paper->load(['track', 'subTrack', 'authors', 'conflicts.conflictedUser',
                      'reviewerAssignments.reviewer', 'reviewerAssignments.assignedBy']);

        return view('admin.review_assignments.show', [
            'paper' => $paper,
            'candidates' => $this->matcher->candidatesFor($paper),
            'wanted' => $this->matcher->reviewersWanted($paper),
            'minimum' => $this->matcher->minimumReviewers(),
            'capacity' => $this->matcher->capacityFor($paper),
        ]);
    }

    /** A chair picking reviewers by hand. */
    public function store(Request $request, Paper $paper)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);

        $data = $request->validate([
            'reviewer_ids' => 'required|array|min:1',
            'reviewer_ids.*' => 'integer|exists:users,id',
        ], [
            'reviewer_ids.required' => 'Choose at least one reviewer.',
        ]);

        $refused = [];
        $assigned = 0;

        foreach ($data['reviewer_ids'] as $reviewerId) {
            $reviewer = User::find($reviewerId);
            $reason = $this->matcher->reasonToRefuse($paper, $reviewer);

            if ($reason) {
                $refused[] = $reviewer->name . ' — ' . $reason;
                continue;
            }

            $this->assign($paper, $reviewer, 'manual');
            $assigned++;
        }

        return back()
            ->with($assigned ? 'success' : 'error', $assigned
                ? $assigned . ' reviewer' . ($assigned === 1 ? '' : 's') . ' assigned.'
                : 'Nobody was assigned.')
            ->with('refused', $refused);
    }

    /**
     * The system choosing, on the four criteria the document names. It tops the paper
     * up to the wanted number rather than replacing anyone already assigned.
     */
    public function auto(Paper $paper)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);

        $paper->load(['authors', 'conflicts', 'reviewerAssignments']);

        $shortfall = $this->matcher->reviewersWanted($paper) - $paper->reviewerAssignments->count();

        if ($shortfall <= 0) {
            return back()->with('error', 'This paper already has the number of reviewers the track asks for.');
        }

        $proposed = $this->matcher->propose($paper, $shortfall);

        if ($proposed->isEmpty()) {
            return back()->with('error',
                'Nobody in this track\'s pool can take the paper. The reason against each name is listed below.');
        }

        foreach ($proposed as $candidate) {
            $this->assign($paper, $candidate['reviewer'], 'auto', $candidate['score']);
        }

        $short = $shortfall - $proposed->count();

        return back()->with('success', $proposed->count() . ' reviewer' . ($proposed->count() === 1 ? '' : 's')
            . ' assigned by subject match.' . ($short > 0 ? " {$short} place(s) could not be filled." : ''));
    }

    public function destroy(Paper $paper, PaperReviewerAssignment $assignment)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);
        abort_if($assignment->paper_id !== $paper->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->delete();

        return back()->with('success', 'Reviewer removed from this paper.');
    }

    private function assign(Paper $paper, User $reviewer, string $source, ?int $score = null): void
    {
        DB::transaction(function () use ($paper, $reviewer, $source, $score) {
            PaperReviewerAssignment::create([
                'paper_id' => $paper->id,
                'reviewer_id' => $reviewer->id,
                'assigned_by' => auth()->id(),
                'assignment_source' => $source,
                'status' => 'invited',
                'match_score' => $score,
                'assigned_at' => now(),
            ]);
        });

        try {
            Mail::to($reviewer->email)->queue(new \App\Mail\ReviewerAssigned($paper, $reviewer));
        } catch (\Exception $e) {
            Log::error('Reviewer invitation mail failed', [
                'paper' => $paper->id, 'reviewer' => $reviewer->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    private function authoriseScope(Paper $paper): void
    {
        abort_if(
            !ChairScope::for(auth()->user())->canManage($paper->track_id, $paper->sub_track_id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden - that paper is outside the track you chair.'
        );
    }
}
