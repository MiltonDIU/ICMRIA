<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaperDecision;
use App\Models\PaperDecisionComment;
use App\Services\ChairScope;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * The TPC Chair's desk (requirement document, Phase 5): "TPC Chair grants final approval
 * for all track decisions", then "System sends bulk decision notifications".
 *
 * Approval and notification are separate steps on purpose. Approving does not email
 * anyone, so a decision approved by mistake can be caught before an author reads it,
 * and the results can be released for every track at once.
 */
class FinalApprovalController extends Controller
{
    private const TABS = [
        'pending' => 'pending_approval',
        'returned' => 'returned',
        'approved' => 'approved',
    ];

    public function index(Request $request)
    {
        abort_if(Gate::denies('final_approval'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tab = array_key_exists($request->string('tab')->toString(), self::TABS)
            ? $request->string('tab')->toString()
            : 'pending';

        $decisions = PaperDecision::with(['paper.track', 'paper.subTrack', 'paper.reviewerAssignments.evaluation', 'paper.decisionComments.user',
                                          'decidedBy', 'approvedBy'])
            ->whereHas('paper')
            ->where('status', self::TABS[$tab])
            ->orderBy('decided_at')
            ->get();

        $byStatus = PaperDecision::whereHas('paper')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.final_approval.index', [
            'decisions' => $decisions,
            'tab' => $tab,
            'counts' => collect(self::TABS)->map(fn ($status) => (int) ($byStatus[$status] ?? 0))->all(),
            'unnotified' => PaperDecision::whereHas('paper')->where('status', 'approved')->whereNull('notified_at')->count(),
        ]);
    }

    /** Approves the ticked decisions, across any tracks, leaving out any the approver is conflicted on. */
    public function approve(Request $request)
    {
        abort_if(Gate::denies('final_approval'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'decision_ids' => 'required|array|min:1',
            'decision_ids.*' => 'integer',
        ], [
            'decision_ids.required' => 'Tick at least one decision to approve.',
        ]);

        $scope = ChairScope::for(auth()->user());
        $approved = 0;
        $skipped = [];

        $decisions = PaperDecision::with(['paper.authors', 'paper.conflicts', 'paper.user'])
            ->whereIn('id', $data['decision_ids'])
            ->where('status', 'pending_approval')
            ->whereHas('paper')
            ->get();

        foreach ($decisions as $decision) {
            if ($reason = $scope->conflictWith($decision->paper)) {
                $skipped[] = $decision->paper->submission_id . ' — ' . $reason;
                continue;
            }

            $decision->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            $approved++;
        }

        return back()
            ->with($approved ? 'success' : 'error', $approved
                ? $approved . ' decision' . ($approved === 1 ? '' : 's') . ' approved. Authors are not told until the decision emails are sent.'
                : 'Nothing was approved.')
            ->with('skipped', $skipped);
    }

    public function returnToChair(Request $request, PaperDecision $decision)
    {
        abort_if(Gate::denies('final_approval'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($decision->status !== 'pending_approval') {
            return back()->with('error', 'Only a decision awaiting approval can be returned.');
        }

        if ($reason = ChairScope::for(auth()->user())->conflictWith($decision->paper)) {
            return back()->with('error', $reason);
        }

        $data = $request->validate([
            'comment' => 'required|string|max:2000',
        ], [
            'comment.required' => 'Tell the chair what to reconsider.',
        ]);

        DB::transaction(function () use ($decision, $data) {
            $decision->update(['status' => 'returned']);
            $this->addComment($decision, $data['comment'], 'returned');
        });

        return back()->with('success', 'Decision on ' . $decision->paper->submission_id . ' returned to the chair.');
    }

    /** A comment from the TPC Chair on a decision that is not yet approved. */
    public function comment(Request $request, PaperDecision $decision)
    {
        abort_if(Gate::denies('final_approval'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($reason = ChairScope::for(auth()->user())->conflictWith($decision->paper)) {
            return back()->with('error', $reason);
        }

        if ($decision->isApproved()) {
            return back()->with('error', 'The decision has been approved, so no further comments can be added.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:5000',
        ], [
            'comment.required' => 'Write a comment before adding it.',
        ]);

        $this->addComment($decision, $data['comment'], 'comment');

        return back()->with('success', 'Comment added.');
    }

    private function addComment(PaperDecision $decision, string $body, string $kind): void
    {
        PaperDecisionComment::create([
            'paper_id' => $decision->paper_id,
            'paper_decision_id' => $decision->id,
            'user_id' => auth()->id(),
            'author_role' => 'tpc',
            'kind' => $kind,
            'round' => $decision->round,
            'body' => $body,
        ]);
    }

    /**
     * Emails the corresponding author of each approved, not yet notified paper: the
     * decision, the chair's note, and every reviewer's scores and feedback with the
     * reviewers' names, recommendations and confidential comments left out.
     */
    public function notify(Request $request)
    {
        abort_if(Gate::denies('final_approval'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'decision_ids' => 'nullable|array',
            'decision_ids.*' => 'integer',
        ]);

        $decisions = PaperDecision::with('paper.user')
            ->where('status', 'approved')
            ->whereNull('notified_at')
            ->whereHas('paper')
            ->when(!empty($data['decision_ids']), fn ($q) => $q->whereIn('id', $data['decision_ids']))
            ->get();

        if ($decisions->isEmpty()) {
            return back()->with('error', 'There are no approved decisions waiting to be sent.');
        }

        $sent = 0;
        $failed = [];

        foreach ($decisions as $decision) {
            $email = $decision->paper->user?->email;

            if (!$email) {
                $failed[] = $decision->paper->submission_id;
                continue;
            }

            try {
                Mail::to($email)->queue(new \App\Mail\DecisionNotification($decision->paper));
                $decision->update(['notified_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                Log::error('Decision notification failed', ['decision' => $decision->id, 'error' => $e->getMessage()]);
                $failed[] = $decision->paper->submission_id;
            }
        }

        return back()
            ->with($sent ? 'success' : 'error', $sent
                ? 'Decision emails sent for ' . $sent . ' paper' . ($sent === 1 ? '' : 's') . '.'
                : 'No decision emails could be sent.')
            ->with('skipped', $failed ? ['Could not be sent: ' . implode(', ', $failed)] : []);
    }
}
