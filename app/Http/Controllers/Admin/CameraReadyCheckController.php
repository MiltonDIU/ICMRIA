<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CameraReadyUpdate;
use App\Models\Paper;
use App\Services\ChairScope;
use App\Services\PaperProgress;
use App\Services\ProceedingsConfirmation;
use App\Services\ProceedingsRules;
use App\Services\RevisionReviewers;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

/**
 * The camera-ready check (organisers, 2026-10-06): once an accepted paper's camera-ready
 * manuscript and signed copyright form are in, the paper's Track Chair or Sub-Track Chair
 * approves the files or sends them back, before the author pays. Administrators see every
 * track and may act too. Who may act is the camera_ready_approve permission.
 */
class CameraReadyCheckController extends Controller
{
    private const FILTERS = ['pending', 'changes_requested', 'approved'];

    public function index(Request $request)
    {
        abort_if(Gate::denies('camera_ready_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = ChairScope::for(auth()->user());
        $filter = in_array($request->string('filter')->toString(), self::FILTERS, true)
            ? $request->string('filter')->toString()
            : 'pending';

        $papers = $scope->constrainPapers(Paper::accepted())
            ->whereHas('cameraReady', fn ($q) => $q->whereNotNull('camera_ready_path')->whereNotNull('copyright_path'))
            ->with(['track', 'subTrack', 'decision', 'cameraReady.filesReviewedBy', 'authors', 'conflicts', 'user'])
            ->orderBy('id')
            ->get()
            ->filter(fn ($paper) => $scope->conflictWith($paper) === null)
            ->values();

        $group = fn ($paper) => match ($paper->cameraReady->status) {
            'submitted' => 'pending',
            'changes_requested' => 'changes_requested',
            default => 'approved', // approved, or approved and paid (confirmed)
        };

        $counts = [];
        foreach (self::FILTERS as $key) {
            $counts[$key] = $papers->filter(fn ($paper) => $group($paper) === $key)->count();
        }

        return view('admin.camera_ready_checks.index', [
            'papers' => $papers->filter(fn ($paper) => $group($paper) === $filter)->values(),
            'counts' => $counts,
            'filter' => $filter,
            'hasNoScope' => $scope->isEmpty(),
        ]);
    }

    public function approve(Paper $paper)
    {
        $final = $this->authoriseAndLoad($paper);

        if ($final->filesApproved()) {
            return back()->with('error', 'The camera-ready files for ' . $paper->submission_id . ' are already approved.');
        }

        if ($final->status === 'changes_requested') {
            return back()->with('error', 'Changes were requested for ' . $paper->submission_id . '. Wait for the author to upload the corrected files.');
        }

        $final->update([
            'status' => 'approved',
            'admin_note' => null,
            'files_reviewed_by' => auth()->id(),
            'files_reviewed_at' => now(),
        ]);

        PaperProgress::record($paper, 'camera_ready_approved');

        // Paid under the earlier order of steps: nothing is left, so confirm now.
        if (ProceedingsConfirmation::confirmIfReady($paper, 'Automatically, on file approval (fee already paid)')) {
            return back()->with('success', 'Files for ' . $paper->submission_id . ' approved. The fee was already paid, so the paper is now Confirmed for Proceedings.');
        }

        $this->tellAuthors($paper, 'files_approved');

        return back()->with('success', 'Files for ' . $paper->submission_id . ' approved. The authors have been told they can now pay the registration fee.');
    }

    public function requestChanges(Request $request, Paper $paper)
    {
        $final = $this->authoriseAndLoad($paper);

        if (ProceedingsRules::isPaid($paper) && !Gate::allows('camera_ready_review')) {
            return back()->with('error', 'The fee for ' . $paper->submission_id . ' is already paid. Ask the conference administrators to request changes.');
        }

        $data = $request->validate([
            'admin_note' => 'required|string|max:2000',
        ], [
            'admin_note.required' => 'Tell the authors what to change.',
        ]);

        $final->update([
            'status' => 'changes_requested',
            'admin_note' => $data['admin_note'],
            'files_reviewed_by' => auth()->id(),
            'files_reviewed_at' => now(),
        ]);

        PaperProgress::record($paper, 'camera_ready_changes_requested', $data['admin_note']);
        $this->tellAuthors($paper, 'changes');

        return back()->with('success', 'Changes requested from the authors of ' . $paper->submission_id . '.');
    }

    private function authoriseAndLoad(Paper $paper)
    {
        abort_if(Gate::denies('camera_ready_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'cameraReady', 'authors', 'conflicts', 'user']);

        abort_unless(RevisionReviewers::canReview(auth()->user(), $paper), Response::HTTP_FORBIDDEN,
            '403 Forbidden - that paper is outside your tracks, or you have a conflict with it.');

        $final = $paper->cameraReady;
        abort_unless($final && $final->camera_ready_path && $final->copyright_path, Response::HTTP_NOT_FOUND,
            'The camera-ready manuscript and copyright form are not both on record for this paper.');

        abort_if($final->isConfirmed(), Response::HTTP_UNPROCESSABLE_ENTITY,
            'This paper is already confirmed for the proceedings.');

        return $final;
    }

    private function tellAuthors(Paper $paper, string $kind): void
    {
        try {
            Mail::to($paper->notificationRecipients())->queue(new CameraReadyUpdate($paper->fresh(), $kind));
        } catch (\Exception $e) {
            Log::error('Camera-ready check mail failed', ['paper' => $paper->id, 'kind' => $kind, 'error' => $e->getMessage()]);
        }
    }
}
