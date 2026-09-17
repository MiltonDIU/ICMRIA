<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperCameraReady;
use App\Models\PaperPaymentProof;
use App\Services\ChairScope;
use App\Services\ProceedingsRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $final = $paper->cameraReady;

        if ($final?->isConfirmed()) {
            return back()->with('error', 'Your paper is already confirmed for the proceedings, so its files can no longer be replaced.');
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

        // A re-upload answers a request for changes.
        $final->status = 'submitted';
        $final->save();

        return back()->with('success', 'Uploaded. The conference team will confirm your paper once every item on the checklist is complete.');
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
        ]);

        // A new revision answers a request for changes.
        if ($final->status === 'changes_requested') {
            $final->status = 'submitted';
        }

        $final->save();

        return back()->with('success', 'Revised manuscript received. Next, upload the camera-ready version and the copyright form.');
    }

    /** $file is "camera-ready", "copyright" or "revised". */
    public function download(Paper $paper, string $file)
    {
        $this->authoriseReading($paper);

        $final = $paper->cameraReady;
        [$path, $name] = match ($file) {
            'copyright' => [$final?->copyright_path, $final?->copyright_name],
            'revised' => [$final?->revised_path, $final?->revised_name],
            default => [$final?->camera_ready_path, $final?->camera_ready_name],
        };

        abort_if(!$path || !Storage::exists($path), Response::HTTP_NOT_FOUND, 'That file is not on record.');

        $extension = pathinfo((string) $name, PATHINFO_EXTENSION);

        return Storage::download($path, $paper->submission_id . '-' . $file . ($extension ? '.' . $extension : ''));
    }

    /** A registration fee paid by transfer, reported for an administrator to verify. */
    public function storePaymentProof(Request $request, Paper $paper)
    {
        abort_unless($paper->user_id === Auth::id(), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'paymentProofs']);

        if (!ProceedingsRules::needsPayment($paper)) {
            return back()->with('error', ProceedingsRules::isPaid($paper)
                ? 'The registration fee for this paper has already been paid.'
                : 'No registration fee is due for this paper yet.');
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

        PaperPaymentProof::create([
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

        return back()->with('success', 'Payment details received. You will see the fee marked as paid once it has been verified.');
    }

    public function downloadPaymentProof(PaperPaymentProof $proof)
    {
        abort_unless($proof->user_id === Auth::id() || Gate::allows('camera_ready_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(!Storage::exists($proof->proof_path), Response::HTTP_NOT_FOUND, 'That file is not on record.');

        return Storage::download($proof->proof_path, $proof->proof_name);
    }

    /** The author, the conference administrators, and the chairs of the paper's track. */
    private function authoriseReading(Paper $paper): void
    {
        $user = Auth::user();
        $paper->loadMissing('cameraReady');

        $allowed = $paper->user_id === $user->id
            || Gate::allows('camera_ready_access')
            || (Gate::allows('decision_access') && $paper->track_id
                && ChairScope::for($user)->canManage($paper->track_id, $paper->sub_track_id));

        abort_unless($allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
