<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperBid;
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

        $papers = $this->papersInScope($scope)
            ->with(['track', 'subTrack', 'reviewerAssignments.reviewer'])
            ->withCount([
                'bids as want_bids_count' => fn ($q) => $q->where('preference', PaperBid::WANT),
                'bids as can_bids_count' => fn ($q) => $q->where('preference', PaperBid::CAN),
            ])
            ->when($request->filled('state'), function ($query) use ($request) {
                $wanted = $request->string('state')->toString();
                $query->when($wanted === 'unassigned', fn ($q) => $q->whereDoesntHave('reviewerAssignments', fn ($a) => $a->active()))
                      ->when($wanted === 'assigned', fn ($q) => $q->whereHas('reviewerAssignments', fn ($a) => $a->active()));
            })
            ->orderBy('id')
            ->get();

        $minimum = $this->matcher->minimumReviewers();

        return view('admin.review_assignments.index', [
            'papers' => $papers,
            'minimum' => $minimum,
            'hasNoScope' => $scope->isEmpty(),
            'state' => $request->string('state')->toString(),
            // Reviewers who stepped back recently, so their places are not forgotten.
            'recentDeclines' => PaperReviewerAssignment::with(['paper', 'reviewer'])
                ->where('status', 'declined')
                ->where('responded_at', '>=', now()->subDays(14))
                ->whereIn('paper_id', $this->papersInScope($scope)->pluck('papers.id'))
                ->latest('responded_at')
                ->get(),
        ]);
    }

    /** The one paper a chair is working on, with the candidates ranked. */
    public function show(Paper $paper)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);

        $paper->load(['track', 'subTrack', 'authors', 'conflicts.conflictedUser', 'bids',
                      'reviewerAssignments.reviewer', 'reviewerAssignments.assignedBy', 'reviewerAssignments.evaluation']);

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

        if ($paper->status === 'rejected') {
            return back()->with('error', 'The abstract was rejected, so this paper is not sent for review.');
        }

        $data = $request->validate([
            'reviewer_ids' => 'required|array|min:1',
            'reviewer_ids.*' => 'integer|exists:users,id',
        ], [
            'reviewer_ids.required' => 'Choose at least one reviewer.',
        ]);

        // A reviewer brought in because the evaluations disagree is recorded as such.
        $source = $request->input('source') === 'discussion' ? 'discussion' : 'manual';

        $refused = [];
        $assigned = 0;

        foreach ($data['reviewer_ids'] as $reviewerId) {
            $reviewer = User::find($reviewerId);
            $reason = $this->matcher->reasonToRefuse($paper, $reviewer);

            if ($reason) {
                $refused[] = $reviewer->name . ' — ' . $reason;
                continue;
            }

            $this->assign($paper, $reviewer, $source);
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

        if ($paper->status === 'rejected') {
            return back()->with('error', 'The abstract was rejected, so this paper is not sent for review.');
        }

        $paper->load(['track', 'authors', 'conflicts', 'bids', 'reviewerAssignments']);

        $shortfall = $this->matcher->reviewersWanted($paper) - $paper->reviewerAssignments->where('status', '!=', 'declined')->count();

        if ($shortfall <= 0) {
            return back()->with('error', 'This paper already has the number of reviewers the track asks for.');
        }

        $added = $this->topUp($paper, $shortfall);

        if ($added === 0) {
            return back()->with('error',
                'Nobody in this track\'s pool can take the paper. The reason against each name is listed below.');
        }

        $short = $shortfall - $added;

        return back()->with('success', $added . ' reviewer' . ($added === 1 ? '' : 's')
            . ' assigned automatically.' . ($short > 0 ? " {$short} place(s) could not be filled." : ''));
    }

    /**
     * Automatic assignment across every paper the chair can reach, for the day the
     * submission window closes and a whole track needs reviewers at once.
     *
     * Papers are taken one at a time, and each reviewer's workload is read afresh for
     * every paper, so the ceiling holds across the whole batch and not just within
     * one paper.
     */
    public function autoAll()
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $papers = $this->papersInScope(ChairScope::for(auth()->user()))
            ->with(['track', 'authors', 'conflicts', 'bids', 'reviewerAssignments'])
            ->orderBy('id')
            ->get();

        $assigned = 0;
        $papersFilled = 0;
        $stillShort = [];

        foreach ($papers as $paper) {
            $shortfall = $this->matcher->reviewersWanted($paper) - $paper->reviewerAssignments->where('status', '!=', 'declined')->count();

            if ($shortfall <= 0) {
                continue;
            }

            $added = $this->topUp($paper, $shortfall);
            $assigned += $added;
            $papersFilled += $added > 0 ? 1 : 0;

            if ($added < $shortfall) {
                $stillShort[] = $paper->submission_id;
            }
        }

        if ($assigned === 0 && !$stillShort) {
            return back()->with('success', 'Every paper already has the number of reviewers its track asks for.');
        }

        return back()
            ->with($assigned ? 'success' : 'error', $assigned
                ? "{$assigned} reviewer" . ($assigned === 1 ? '' : 's') . " assigned across {$papersFilled} paper" . ($papersFilled === 1 ? '' : 's') . '.'
                : 'Nobody could be assigned.')
            ->with('short', $stillShort);
    }

    public function destroy(Paper $paper, PaperReviewerAssignment $assignment)
    {
        abort_if(Gate::denies('review_assign'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authoriseScope($paper);
        abort_if($assignment->paper_id !== $paper->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Removing the assignment would take the evaluation with it.
        if ($assignment->evaluation()->submitted()->exists()) {
            return back()->with('error', 'This reviewer has already submitted an evaluation, so they stay on the paper as the record of it.');
        }

        $assignment->delete();

        return back()->with('success', 'Reviewer removed from this paper.');
    }

    /**
     * Assigns up to $howMany of the best candidates and says how many it managed.
     * A pairing the reviewer asked for is recorded as coming from their bid.
     */
    private function topUp(Paper $paper, int $howMany): int
    {
        $proposed = $this->matcher->propose($paper, $howMany);

        foreach ($proposed as $candidate) {
            $source = PaperBid::rank($candidate['bid']) > 0 ? 'bid' : 'auto';
            $this->assign($paper, $candidate['reviewer'], $source, $candidate['score']);
        }

        return $proposed->count();
    }

    /** The papers this chair may assign, with rejected abstracts left out. */
    private function papersInScope(ChairScope $scope)
    {
        return $scope->papers();
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
