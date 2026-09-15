<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperDecision;
use App\Models\PaperDecisionComment;
use App\Services\ChairScope;
use App\Services\ReviewConsolidation;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Where a chair brings the evaluations together and decides (requirement document,
 * Phase 5: review consolidation, discussion, and final decision entry).
 *
 * Track and Sub-Track Chairs decide within their scope. The TPC Chair and
 * administrators can read every paper here, but the TPC Chair does not enter decisions;
 * they approve them under Final Approval.
 */
class DecisionController extends Controller
{
    private const FILTERS = ['ready', 'conflict', 'awaiting', 'returned', 'approved'];

    public function index(Request $request)
    {
        abort_if(Gate::denies('decision_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());
        $filter = in_array($request->string('filter')->toString(), self::FILTERS, true)
            ? $request->string('filter')->toString()
            : '';

        $rows = $scope->papers()
            ->with(['track', 'subTrack', 'decision', 'reviewerAssignments.evaluation'])
            ->orderBy('id')
            ->get()
            ->map(fn ($paper) => ['paper' => $paper, 'review' => ReviewConsolidation::for($paper)]);

        $matches = [
            'ready' => fn ($row) => $this->needsDecision($row['paper']) && $row['review']->isReady(),
            'conflict' => fn ($row) => $this->needsDecision($row['paper']) && $row['review']->hasConflict(),
            'awaiting' => fn ($row) => $row['paper']->decision?->status === 'pending_approval',
            'returned' => fn ($row) => $row['paper']->decision?->status === 'returned',
            'approved' => fn ($row) => $row['paper']->decision?->status === 'approved',
        ];

        $counts = ['all' => $rows->count()];
        foreach ($matches as $key => $match) {
            $counts[$key] = $rows->filter($match)->count();
        }

        return view('admin.decisions.index', [
            'rows' => $filter ? $rows->filter($matches[$filter])->values() : $rows,
            'counts' => $counts,
            'filter' => $filter,
            'minimum' => app(\App\Services\ReviewerMatcher::class)->minimumReviewers(),
            'hasNoScope' => $scope->isEmpty(),
        ]);
    }

    public function show(Paper $paper)
    {
        abort_if(Gate::denies('decision_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorisePaper($paper);

        $paper->load(['track', 'subTrack', 'decision.decidedBy', 'decision.approvedBy',
                      'reviewerAssignments.reviewer', 'reviewerAssignments.evaluation',
                      'discussionMessages.user', 'discussionOpenedBy', 'decisionComments.user']);

        $decision = $paper->decision;
        $canDecide = Gate::allows('decision_make') && !$decision?->isApproved();

        // Each side gets its own comment form and never the other's: a chair the chair's, the
        // TPC Chair the TPC Chair's once there is a decision to comment on. Nobody comments
        // after approval.
        $commentForm = null;
        if (!$decision?->isApproved()) {
            if (Gate::allows('decision_make')) {
                $commentForm = 'chair';
            } elseif (Gate::allows('final_approval') && $decision) {
                $commentForm = 'tpc';
            }
        }

        return view('admin.decisions.show', [
            'paper' => $paper,
            'review' => ReviewConsolidation::for($paper),
            'decision' => $decision,
            'canDecide' => $canDecide,
            'cannotDecideReason' => !$canDecide && !$decision?->isApproved()
                ? 'Decisions are entered by the track chairs. The TPC Chair approves them under Final Approval.'
                : null,
            'decisions' => PaperDecision::DECISIONS,
            'commentForm' => $commentForm,
        ]);
    }

    /**
     * Enters or revises the decision, which then waits for the TPC Chair. A returned
     * decision comes back here and goes out again the same way.
     */
    public function store(Request $request, Paper $paper)
    {
        abort_if(Gate::denies('decision_make'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorisePaper($paper);

        $paper->load(['decision', 'reviewerAssignments.evaluation']);

        if ($paper->status === 'rejected') {
            return back()->with('error', 'The abstract was rejected, so this paper is not up for a decision.');
        }

        if ($paper->decision?->isApproved()) {
            return back()->with('error', 'The TPC Chair has already approved the decision on this paper, so it can no longer be changed.');
        }

        $review = ReviewConsolidation::for($paper);

        if (!$review->isReady()) {
            return back()->with('error', "A decision needs at least {$review->minimum()} submitted evaluations; this paper has {$review->submittedCount()}.");
        }

        $data = $request->validate([
            'decision' => ['required', Rule::in(array_keys(PaperDecision::DECISIONS))],
            'note_to_authors' => 'nullable|string|max:5000',
            'comment' => 'nullable|string|max:5000',
        ], [
            'decision.required' => 'Choose Accept, Accept with Minor Revisions or Reject.',
        ]);

        DB::transaction(function () use ($paper, $data) {
            $existing = $paper->decision;

            // Sending a returned decision again opens a new round, so each exchange with the TPC
            // Chair stays together. Revising before the TPC Chair has acted stays in the round.
            $round = !$existing ? 1 : ($existing->status === 'returned' ? $existing->round + 1 : $existing->round);

            $decision = PaperDecision::updateOrCreate(['paper_id' => $paper->id], [
                'decision' => $data['decision'],
                'note_to_authors' => $data['note_to_authors'] ?? null,
                'status' => 'pending_approval',
                'round' => $round,
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
            ]);

            if (filled($data['comment'] ?? null)) {
                $this->addComment($paper, $decision, $data['comment'], 'decision');
            }
        });

        return redirect()->route('admin.decisions.show', $paper->id)
            ->with('success', 'Decision recorded and sent to the TPC Chair for approval.');
    }

    /**
     * Opens the internal discussion and invites the reviewers who have submitted. Those
     * still writing are let in once they submit, and told so on their review page.
     */
    public function openDiscussion(Paper $paper)
    {
        abort_if(Gate::denies('decision_make'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorisePaper($paper);

        if ($paper->discussion_opened_at) {
            return back()->with('error', 'A discussion is already open for this paper.');
        }

        $paper->update(['discussion_opened_at' => now(), 'discussion_opened_by' => auth()->id()]);
        $paper->load(['reviewerAssignments.reviewer', 'reviewerAssignments.evaluation']);

        $invited = 0;
        foreach (ReviewConsolidation::for($paper)->submitted() as $row) {
            try {
                Mail::to($row['assignment']->reviewer->email)
                    ->queue(new \App\Mail\DiscussionOpened($paper, $row['assignment']));
                $invited++;
            } catch (\Exception $e) {
                Log::error('Discussion invitation mail failed', [
                    'paper' => $paper->id, 'assignment' => $row['assignment']->id, 'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Discussion opened. ' . $invited . ' reviewer' . ($invited === 1 ? '' : 's')
            . ' who have submitted were invited to join.');
    }

    /** A chair's comment, with or without a decision, until the decision is approved. */
    public function comment(Request $request, Paper $paper)
    {
        abort_if(Gate::denies('decision_make'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorisePaper($paper);
        $paper->load('decision');

        if ($paper->decision?->isApproved()) {
            return back()->with('error', 'The decision has been approved, so no further comments can be added.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:5000',
        ], [
            'comment.required' => 'Write a comment before adding it.',
        ]);

        $this->addComment($paper, $paper->decision, $data['comment'], 'comment');

        return back()->with('success', 'Comment added.');
    }

    private function addComment(Paper $paper, ?PaperDecision $decision, string $body, string $kind): void
    {
        PaperDecisionComment::create([
            'paper_id' => $paper->id,
            'paper_decision_id' => $decision?->id,
            'user_id' => auth()->id(),
            'author_role' => 'chair',
            'kind' => $kind,
            'round' => $decision?->round ?? 1,
            'body' => $body,
        ]);
    }

    /** No decision yet, or one the TPC Chair sent back. */
    private function needsDecision(Paper $paper): bool
    {
        return !$paper->decision || $paper->decision->status === 'returned';
    }

    private function authorisePaper(Paper $paper): void
    {
        $scope = ChairScope::for(auth()->user());

        abort_if(!$paper->track_id || !$scope->canManage($paper->track_id, $paper->sub_track_id),
            Response::HTTP_FORBIDDEN, '403 Forbidden - that paper is outside the track you chair.');

        if ($reason = $scope->conflictWith($paper)) {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden - ' . $reason);
        }
    }
}
