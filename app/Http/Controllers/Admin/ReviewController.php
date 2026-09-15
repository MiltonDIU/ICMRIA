<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaperEvaluation;
use App\Models\PaperManuscriptVersion;
use App\Models\PaperReviewerAssignment;
use App\Services\ChairScope;
use App\Services\Discussion;
use App\Services\ReviewConsolidation;
use App\Services\SubmissionRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * The reviewer's panel (requirement document, Phase 4): the papers they were given,
 * the invitation to accept or decline, and the evaluation form.
 *
 * Everything here is reached through the reviewer's own assignment, never through the
 * paper, so a reviewer can only ever open what was handed to them.
 */
class ReviewController extends Controller
{
    /** Statuses in which the evaluation can still be written. */
    private const EDITABLE = ['invited', 'accepted', 'in_progress'];

    public function index()
    {
        abort_if(Gate::denies('review_submit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignments = PaperReviewerAssignment::with(['paper.track', 'paper.subTrack', 'evaluation'])
            ->where('reviewer_id', auth()->id())
            ->whereHas('paper')
            // What needs the reviewer's attention first, finished work last.
            ->orderByRaw("FIELD(status, 'invited', 'accepted', 'in_progress', 'completed', 'declined')")
            ->orderBy('assigned_at')
            ->get();

        return view('admin.reviews.index', [
            'assignments' => $assignments,
            'counts' => $assignments->countBy('status'),
            'doubleBlind' => SubmissionRules::isDoubleBlind(),
        ]);
    }

    public function show(PaperReviewerAssignment $assignment)
    {
        $this->authoriseReviewer($assignment);

        $assignment->load(['paper.track', 'paper.subTrack', 'paper.authors', 'paper.decision', 'paper.discussionMessages.user',
                           'paper.reviewerAssignments.reviewer', 'paper.reviewerAssignments.evaluation', 'evaluation']);
        abort_if(!$assignment->paper, Response::HTTP_NOT_FOUND, 'That paper has been withdrawn.');

        return view('admin.reviews.show', [
            'assignment' => $assignment,
            'paper' => $assignment->paper,
            'evaluation' => $assignment->evaluation ?? new PaperEvaluation(),
            'editable' => $this->isEditable($assignment),
            'doubleBlind' => SubmissionRules::isDoubleBlind(),
            'criteria' => PaperEvaluation::CRITERIA,
            'recommendations' => PaperEvaluation::RECOMMENDATIONS,
            'review' => ReviewConsolidation::for($assignment->paper),
            // A reviewer joins the discussion only once their own evaluation is in.
            'inDiscussion' => $assignment->paper->discussion_opened_at !== null
                && Discussion::isReviewerWhoSubmitted($assignment->paper, auth()->user()),
            'discussionClosed' => (bool) $assignment->paper->decision?->isApproved(),
        ]);
    }

    public function accept(PaperReviewerAssignment $assignment)
    {
        $this->authoriseReviewer($assignment);

        if ($assignment->status !== 'invited') {
            return back()->with('error', 'This invitation has already been answered.');
        }

        $assignment->update(['status' => 'accepted', 'responded_at' => now()]);

        return redirect()->route('admin.reviews.show', $assignment->id)
            ->with('success', 'Thank you for taking this paper. Your evaluation can be saved as a draft until you are ready to submit it.');
    }

    /**
     * Turning the paper down. It stops counting against the reviewer's workload and
     * towards the paper's reviewers, so the chair can assign someone else in its place.
     */
    public function decline(Request $request, PaperReviewerAssignment $assignment)
    {
        $this->authoriseReviewer($assignment);

        if (!$this->isEditable($assignment)) {
            return back()->with('error', 'This review can no longer be declined.');
        }

        $data = $request->validate([
            'decline_reason' => 'required|string|max:500',
        ], [
            'decline_reason.required' => 'Please say briefly why, so the chair can find someone suitable.',
        ]);

        DB::transaction(function () use ($assignment, $data) {
            // An unfinished draft means nothing once the paper is handed back.
            $assignment->evaluation()->whereNull('submitted_at')->delete();

            $assignment->update([
                'status' => 'declined',
                'responded_at' => now(),
                'decline_reason' => $data['decline_reason'],
            ]);
        });

        return redirect()->route('admin.reviews.index')
            ->with('success', 'You have declined ' . $assignment->paper->submission_id . '. The track chair will assign it to someone else.');
    }

    /**
     * Saves the form, as a draft or for good. A draft may be incomplete; submitting
     * requires every scored field, the recommendation and feedback for the authors,
     * and locks the evaluation.
     */
    public function save(Request $request, PaperReviewerAssignment $assignment)
    {
        $this->authoriseReviewer($assignment);
        $assignment->load(['paper', 'evaluation']);

        if (!$this->isEditable($assignment)) {
            return back()->with('error', $assignment->evaluation?->isSubmitted()
                ? 'This evaluation has been submitted and can no longer be changed.'
                : 'This paper is no longer assigned to you for review.');
        }

        // Only reviewers score. Someone on the committee deciding this paper cannot, even if
        // they were assigned to it before becoming a chair.
        if (ChairScope::for(auth()->user())->canSee($assignment->paper)) {
            return back()->with('error', 'You are on the committee that decides this paper, so you cannot score it. Only its reviewers can.');
        }

        $submitting = $request->input('action') === 'submit';
        $required = $submitting ? 'required' : 'nullable';
        $score = [$required, 'integer', 'between:1,5'];

        $data = $request->validate([
            'originality' => $score,
            'soundness' => $score,
            'relevance' => $score,
            'recommendation' => [$required, Rule::in(array_keys(PaperEvaluation::RECOMMENDATIONS))],
            'feedback_for_authors' => array_merge([$required, 'string', 'max:10000'], $submitting ? ['min:50'] : []),
            'confidential_comments' => ['nullable', 'string', 'max:5000'],
        ], [
            'feedback_for_authors.min' => 'Feedback for the authors should be at least :min characters, so they know what to improve.',
        ], array_merge(PaperEvaluation::CRITERIA, [
            'recommendation' => 'Overall recommendation',
            'feedback_for_authors' => 'Feedback for authors',
            'confidential_comments' => 'Confidential comments',
        ]));

        if ($submitting && !$assignment->paper->hasManuscript()) {
            return back()->withInput()
                ->with('error', 'The author has not uploaded the manuscript yet, so the evaluation cannot be submitted. Your draft can still be saved.');
        }

        DB::transaction(function () use ($assignment, $data, $submitting) {
            $evaluation = $assignment->evaluation ?? new PaperEvaluation([
                'assignment_id' => $assignment->id,
                'paper_id' => $assignment->paper_id,
                'reviewer_id' => $assignment->reviewer_id,
            ]);

            $evaluation->fill($data);

            if ($submitting) {
                $evaluation->submitted_at = now();
                $evaluation->manuscript_version = PaperManuscriptVersion::where('paper_id', $assignment->paper_id)->max('version');
            }

            $evaluation->save();

            $assignment->update([
                'status' => $submitting ? 'completed' : 'in_progress',
                'responded_at' => $assignment->responded_at ?? now(),
            ]);
        });

        return redirect()->route('admin.reviews.show', $assignment->id)->with('success', $submitting
            ? 'Evaluation submitted. Thank you for your review.'
            : 'Draft saved. Nobody else sees it until you submit.');
    }

    private function isEditable(PaperReviewerAssignment $assignment): bool
    {
        return in_array($assignment->status, self::EDITABLE, true)
            && !$assignment->evaluation?->isSubmitted();
    }

    private function authoriseReviewer(PaperReviewerAssignment $assignment): void
    {
        abort_if(Gate::denies('review_submit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if((int) $assignment->reviewer_id !== (int) auth()->id(), Response::HTTP_FORBIDDEN,
            '403 Forbidden - that review is assigned to someone else.');
    }
}
