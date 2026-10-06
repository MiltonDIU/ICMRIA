<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperCameraReady;
use App\Models\PaperPaymentProof;
use App\Services\ChairScope;
use App\Services\PaperProgress;
use App\Services\ProceedingsRules;
use App\Services\RevisionReviewers;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * The author's side of Phase 6: the camera-ready manuscript, the signed copyright
 * transfer form, and a registration fee paid by transfer. Everything is reached from
 * the author's own paper page, and only the submitting author may upload.
 */
class CameraReadyController extends Controller
{
    public const CAMERA_READY_MIMES = ['pdf', 'doc', 'docx'];
    public const DOCUMENT_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * Uploads either file, or both at once. The camera-ready version replaces any
     * earlier one until the paper is confirmed.
     */
    public function upload(Request $request, Paper $paper)
    {
        abort_unless($paper->user_id === Auth::id(), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'cameraReady']);

        if (!ProceedingsRules::isAccepted($paper)) {
            return back()->with('error', 'Camera-ready files can be uploaded once your paper has been accepted.');
        }

        // The chain: a revision asked for must be approved before the camera-ready step opens.
        if (!ProceedingsRules::revisionCleared($paper)) {
            return back()->with('error', 'Your revised manuscript must be approved by the track chair before you upload the camera-ready version.');
        }

        $final = $paper->cameraReady;

        // ...and the copyright form follows the camera-ready manuscript, never the other way round.
        if ($request->hasFile('copyright_form') && !$request->hasFile('camera_ready') && !$final?->camera_ready_path) {
            return back()->with('error', 'Upload the camera-ready manuscript first. The copyright form comes after it.');
        }

        if ($final?->isConfirmed()) {
            return back()->with('error', 'Your paper is already confirmed for the proceedings, so its files can no longer be replaced.');
        }

        // Approved or paid: nothing changes unless a chair or administrator asked for corrected files.
        if ($reason = ProceedingsRules::filesLockedReason($paper)) {
            return back()->with('error', $reason);
        }

        if (!ProceedingsRules::cameraReadyWindowIsOpen()) {
            return back()->with('error', 'The camera-ready deadline has passed.');
        }

        $hasCameraReady = $request->hasFile('camera_ready');

        $request->validate([
            'camera_ready' => ['nullable', 'file', 'mimes:' . implode(',', self::CAMERA_READY_MIMES), 'max:20480', 'required_without:copyright_form'],
            'copyright_form' => ['nullable', 'file', 'mimes:' . implode(',', self::DOCUMENT_MIMES), 'max:5120'],
            'names_confirmed' => $hasCameraReady ? ['accepted'] : ['nullable'],
            'revision_summary' => ['nullable', 'string', 'max:5000'],
        ], [
            'camera_ready.required_without' => 'Choose the camera-ready manuscript or the copyright form to upload.',
            'camera_ready.mimes' => 'The camera-ready manuscript must be a PDF or Word document.',
            'copyright_form.mimes' => 'The copyright form must be a PDF or an image (JPG or PNG).',
            'names_confirmed.accepted' => 'Please confirm the camera-ready version carries every author name and affiliation.',
            'revision_summary.required' => 'Your paper was accepted with minor revisions. Please summarise how you addressed the reviewers\' comments.',
        ]);

        $final ??= new PaperCameraReady(['paper_id' => $paper->id]);

        // Kept off the public disk, as the review manuscripts are.
        if ($hasCameraReady) {
            $file = $request->file('camera_ready');
            $final->fill([
                'camera_ready_path' => $file->store('camera_ready/' . $paper->id),
                'camera_ready_name' => $file->getClientOriginalName(),
                'camera_ready_uploaded_at' => now(),
            ]);
        }

        if ($request->hasFile('copyright_form')) {
            $file = $request->file('copyright_form');
            $final->fill([
                'copyright_path' => $file->store('copyright_forms/' . $paper->id),
                'copyright_name' => $file->getClientOriginalName(),
                'copyright_uploaded_at' => now(),
            ]);
        }

        if ($request->filled('revision_summary')) {
            $final->revision_summary = $request->input('revision_summary');
        }

        // A re-upload answers a request for changes, and goes back to the chairs.
        $final->status = 'submitted';
        $final->save();

        if ($hasCameraReady) {
            PaperProgress::record($paper, 'camera_ready_uploaded', $final->camera_ready_name);
        }
        if ($request->hasFile('copyright_form')) {
            PaperProgress::record($paper, 'copyright_uploaded', $final->copyright_name);
        }

        if (!$final->copyright_path) {
            return back()->with('success', 'Camera-ready manuscript uploaded. Next, upload the signed copyright transfer form.');
        }

        // Both files are in: the track's chairs check them before the fee.
        foreach (\App\Services\CameraReadyCheckers::toNotify($paper) as $chair) {
            try {
                Mail::to($chair->email)->queue(new \App\Mail\CameraReadySubmitted($paper, $chair));
            } catch (\Exception $e) {
                Log::error('Camera-ready notification failed', ['paper' => $paper->id, 'chair' => $chair->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Uploaded. Your track chair will now check the files. You can pay the registration fee once they are approved; we will email you.');
    }

    /**
     * The revised manuscript for a paper accepted with minor revisions, with a summary of
     * how the reviewers' comments were addressed, due by the revision deadline. The
     * camera-ready version follows it.
     */
    public function uploadRevision(Request $request, Paper $paper)
    {
        abort_unless($paper->user_id === Auth::id(), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'cameraReady']);

        if (!ProceedingsRules::needsRevision($paper)) {
            return back()->with('error', 'A revised manuscript is only asked for when a paper is accepted with minor revisions.');
        }

        if ($paper->cameraReady?->isConfirmed()) {
            return back()->with('error', 'Your paper is already confirmed for the proceedings, so its files can no longer be replaced.');
        }

        if ($paper->cameraReady?->revision_status === 'approved') {
            return back()->with('error', 'Your revised manuscript has already been approved by the track chair.');
        }

        if ($reason = ProceedingsRules::filesLockedReason($paper)) {
            return back()->with('error', $reason);
        }

        if (!ProceedingsRules::revisionWindowIsOpen()) {
            return back()->with('error', 'The deadline for the revised manuscript has passed.');
        }

        $request->validate([
            'revised_manuscript' => ['required', 'file', 'mimes:' . implode(',', self::CAMERA_READY_MIMES), 'max:20480'],
            'revision_summary' => ['required', 'string', 'max:5000'],
        ], [
            'revised_manuscript.required' => 'Choose the revised manuscript to upload.',
            'revised_manuscript.mimes' => 'The revised manuscript must be a PDF or Word document.',
            'revision_summary.required' => 'Please summarise how you addressed each of the reviewer comments.',
        ]);

        $file = $request->file('revised_manuscript');
        $final = $paper->cameraReady ?? new PaperCameraReady(['paper_id' => $paper->id, 'status' => 'submitted']);

        $final->fill([
            'revised_path' => $file->store('revisions/' . $paper->id),
            'revised_name' => $file->getClientOriginalName(),
            'revised_uploaded_at' => now(),
            'revision_summary' => $request->input('revision_summary'),
            // Every upload, a replacement included, goes back to the chairs to check.
            'revision_status' => 'pending',
            'revision_reviewed_by' => null,
            'revision_reviewed_at' => null,
        ]);

        // A new revision answers a request for changes.
        if ($final->status === 'changes_requested') {
            $final->status = 'submitted';
        }

        $final->save();

        PaperProgress::record($paper, 'revision_uploaded', $final->revised_name);

        foreach (RevisionReviewers::toNotify($paper) as $chair) {
            try {
                Mail::to($chair->email)->queue(new \App\Mail\RevisionSubmitted($paper, $chair));
            } catch (\Exception $e) {
                Log::error('Revision notification failed', ['paper' => $paper->id, 'chair' => $chair->id, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Revised manuscript received. Once the track chair approves it, you can upload the camera-ready version.');
    }

    /** $file is "camera-ready", "copyright" or "revised". */
    public function download(Paper $paper, string $file)
    {
        $this->authoriseReading($paper);

        $final = $paper->cameraReady;
        [$path, $name, $kind] = match ($file) {
            'copyright' => [$final?->copyright_path, $final?->copyright_name, 'copyright-form'],
            'revised' => [$final?->revised_path, $final?->revised_name, 'revised-manuscript'],
            default => [$final?->camera_ready_path, $final?->camera_ready_name, 'camera-ready'],
        };

        abort_if(!$path || !Storage::exists($path), Response::HTTP_NOT_FOUND, 'That file is not on record.');

        return Storage::download($path, $paper->downloadName($kind, $name));
    }

    /** A registration fee paid by transfer, reported for an administrator to verify. */
    public function storePaymentProof(Request $request, Paper $paper)
    {
        abort_unless($paper->user_id === Auth::id(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (!ProceedingsRules::manualPaymentEnabled()) {
            return back()->with('error', 'Please pay online with the Pay button in your Papers list.');
        }

        $paper->load(['decision', 'cameraReady', 'paymentProofs']);

        if (!ProceedingsRules::needsPayment($paper)) {
            return back()->with('error', ProceedingsRules::isPaid($paper)
                ? 'The registration fee for this paper has already been paid.'
                : (ProceedingsRules::paymentLockedReason($paper) ?? 'No registration fee is due for this paper yet.'));
        }

        if ($reason = ProceedingsRules::paymentBlockReason()) {
            return back()->with('error', $reason);
        }

        if ($paper->paymentProofs->contains('status', 'submitted')) {
            return back()->with('error', 'Your earlier payment is still awaiting verification.');
        }

        $data = $request->validate([
            'method' => ['required', Rule::in(array_keys(PaperPaymentProof::METHODS))],
            // The same reference cannot pay for two papers; a rejected report may be corrected.
            'transaction_id' => ['required', 'string', 'max:100',
                Rule::unique('paper_payment_proofs', 'transaction_id')->where(fn ($q) => $q->where('status', '!=', 'rejected'))],
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'currency' => ['required', Rule::in(PaperPaymentProof::CURRENCIES)],
            'paid_on' => 'required|date|before_or_equal:today',
            'proof' => 'required|file|mimes:' . implode(',', self::DOCUMENT_MIMES) . '|max:5120',
        ], [
            'transaction_id.unique' => 'That transaction reference has already been reported.',
            'proof.required' => 'Please attach the receipt or a screenshot of the transfer.',
            'proof.mimes' => 'The proof must be a PDF or an image (JPG or PNG).',
            'paid_on.before_or_equal' => 'The payment date cannot be in the future.',
        ]);

        $file = $request->file('proof');

        $proof = PaperPaymentProof::create([
            'paper_id' => $paper->id,
            'user_id' => Auth::id(),
            'method' => $data['method'],
            'transaction_id' => trim($data['transaction_id']),
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'paid_on' => $data['paid_on'],
            'proof_path' => $file->store('payment_proofs/' . $paper->id),
            'proof_name' => $file->getClientOriginalName(),
            'status' => 'submitted',
        ]);

        PaperProgress::record($paper, 'payment_reported', $proof->currency . ' ' . $proof->amount . ' via ' . $proof->method, null, $proof->transaction_id);

        return back()->with('success', 'Payment details received. You will see the fee marked as paid once it has been verified.');
    }

    public function downloadPaymentProof(PaperPaymentProof $proof)
    {
        abort_unless($proof->user_id === Auth::id() || Gate::allows('camera_ready_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(!Storage::exists($proof->proof_path), Response::HTTP_NOT_FOUND, 'That file is not on record.');

        $name = $proof->paper
            ? $proof->paper->downloadName('payment-proof', $proof->proof_name)
            : $proof->proof_name;

        return Storage::download($proof->proof_path, $name);
    }

    /**
     * The author, the conference administrators, the chairs of the paper's track, and
     * whoever checks revised manuscripts for it.
     */
    private function authoriseReading(Paper $paper): void
    {
        $user = Auth::user();
        $paper->loadMissing('cameraReady');

        $allowed = $paper->user_id === $user->id
            || Gate::allows('camera_ready_access')
            || (Gate::allows('decision_access') && $paper->track_id
                && ChairScope::for($user)->canManage($paper->track_id, $paper->sub_track_id))
            || (Gate::allows('revision_review') && RevisionReviewers::canReview($user, $paper))
            || (Gate::allows('camera_ready_approve') && \App\Services\CameraReadyCheckers::canCheck($user, $paper));

        abort_unless($allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
