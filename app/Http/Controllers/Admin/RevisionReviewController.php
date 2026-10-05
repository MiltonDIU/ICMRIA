<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CameraReadyUpdate;
use App\Models\Paper;
use App\Services\ChairScope;
use App\Services\PaperProgress;
use App\Services\RevisionReviewers;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revised manuscripts of papers accepted with minor revisions, checked by the paper's
 * Track Chair or Sub-Track Chair, or the TPC Chair (organisers, 2026-10-05). Only those
 * holding the revision_review permission reach this screen; an approved revision is
 * what lets the paper go on to be confirmed for the proceedings.
 */
class RevisionReviewController extends Controller
{
    private const FILTERS = ['pending', 'changes_requested', 'approved'];

    public function index(Request $request)
    {
        abort_if(Gate::denies('revision_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());
        $filter = in_array($request->string('filter')->toString(), self::FILTERS, true)
            ? $request->string('filter')->toString()
            : 'pending';

        $papers = $scope->constrainPapers(Paper::accepted())
            ->whereHas('decision', fn ($q) => $q->where('decision', 'minor_revisions'))
            ->whereHas('cameraReady', fn ($q) => $q->whereNotNull('revised_path'))
            ->with(['track', 'subTrack', 'cameraReady.revisionReviewedBy', 'authors', 'conflicts', 'user'])
            ->orderBy('id')
            ->get()
            // A chair who wrote the paper, or with whom a conflict was declared, stays out.
            ->filter(fn ($paper) => $scope->conflictWith($paper) === null)
            ->values();

        $counts = [];
        foreach (self::FILTERS as $key) {
            $counts[$key] = $papers->filter(fn ($paper) => $paper->cameraReady->revision_status === $key)->count();
        }

        return view('admin.revisions.index', [
            'papers' => $papers->filter(fn ($paper) => $paper->cameraReady->revision_status === $filter)->values(),
            'counts' => $counts,
            'filter' => $filter,
            'hasNoScope' => $scope->isEmpty(),
        ]);
    }

    public function approve(Paper $paper)
    {
        $final = $this->authoriseAndLoad($paper);

        if ($final->revision_status === 'approved') {
            return back()->with('error', 'The revised manuscript for ' . $paper->submission_id . ' is already approved.');
        }

        $final->update([
            'revision_status' => 'approved',
            'revision_reviewed_by' => auth()->id(),
            'revision_reviewed_at' => now(),
        ]);

        PaperProgress::record($paper, 'revision_approved');
        $this->tellAuthors($paper, 'revision_approved');

        return back()->with('success', 'Revised manuscript for ' . $paper->submission_id . ' approved. The authors have been told to upload the camera-ready version.');
    }

    public function requestChanges(Request $request, Paper $paper)
    {
        $final = $this->authoriseAndLoad($paper);

        // The chain has moved on: the camera-ready version was built on this approval.
        if ($final->revision_status === 'approved' && $final->camera_ready_path) {
            return back()->with('error', 'The authors have already uploaded the camera-ready version on this approval, so the revision can no longer be sent back.');
        }

        $data = $request->validate([
            'revision_note' => 'required|string|max:2000',
        ], [
            'revision_note.required' => 'Tell the authors what still needs to change.',
        ]);

        $final->update([
            'revision_status' => 'changes_requested',
            'revision_note' => $data['revision_note'],
            'revision_reviewed_by' => auth()->id(),
            'revision_reviewed_at' => now(),
        ]);

        PaperProgress::record($paper, 'revision_changes_requested', $data['revision_note']);
        $this->tellAuthors($paper, 'revision_changes');

        return back()->with('success', 'Changes requested from the authors of ' . $paper->submission_id . '.');
    }

    private function authoriseAndLoad(Paper $paper)
    {
        abort_if(Gate::denies('revision_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'cameraReady', 'authors', 'conflicts', 'user']);

        abort_unless(RevisionReviewers::canReview(auth()->user(), $paper), Response::HTTP_FORBIDDEN,
            '403 Forbidden - that paper is outside your tracks, or you have a conflict with it.');

        $final = $paper->cameraReady;
        abort_unless($final && $final->revised_path, Response::HTTP_NOT_FOUND, 'No revised manuscript is on record for this paper.');

        abort_if($final->isConfirmed(), Response::HTTP_UNPROCESSABLE_ENTITY,
            'This paper is already confirmed for the proceedings.');

        return $final;
    }

    private function tellAuthors(Paper $paper, string $kind): void
    {
        try {
            Mail::to($paper->notificationRecipients())->queue(new CameraReadyUpdate($paper->fresh(), $kind));
        } catch (\Exception $e) {
            Log::error('Revision review mail failed', ['paper' => $paper->id, 'kind' => $kind, 'error' => $e->getMessage()]);
        }
    }
}
