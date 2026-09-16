<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CameraReadyUpdate;
use App\Mail\RegistrationConfirmed;
use App\Models\Paper;
use App\Models\PaperPaymentProof;
use App\Models\Schedule;
use App\Services\PaymentSync;
use App\Services\ProceedingsExport;
use App\Services\ProceedingsRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * The administrator's side of Phase 6: verifying fees, confirming papers for the
 * proceedings, placing them in the programme, and exporting the result.
 */
class ProceedingsController extends Controller
{
    private const TABS = ['camera', 'payments', 'confirmed'];

    public function index(Request $request)
    {
        abort_if(Gate::denies('camera_ready_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $tab = in_array($request->string('tab')->toString(), self::TABS, true) ? $request->string('tab')->toString() : 'camera';

        $accepted = Paper::accepted()
            ->with(['track', 'subTrack', 'user', 'decision', 'cameraReady.schedule', 'paymentProofs'])
            ->orderBy('id')
            ->get();

        [$confirmed, $inProgress] = $accepted->partition(fn ($paper) => $paper->cameraReady?->isConfirmed());

        $pendingProofs = PaperPaymentProof::with(['paper', 'user'])
            ->whereHas('paper')
            ->where('status', 'submitted')
            ->orderBy('created_at')
            ->get();

        return view('admin.proceedings.index', [
            'tab' => $tab,
            'inProgress' => $inProgress->values(),
            'confirmed' => $confirmed
                ->sortBy(fn ($paper) => [
                    $paper->cameraReady->schedule->day_number ?? PHP_INT_MAX,
                    (string) ($paper->cameraReady->schedule->start_time ?? '99'),
                    $paper->cameraReady->presentation_order ?? PHP_INT_MAX,
                ])
                ->values(),
            'pendingProofs' => $pendingProofs,
            'reviewedProofs' => PaperPaymentProof::with(['paper', 'user', 'reviewedBy'])
                ->whereHas('paper')
                ->where('status', '!=', 'submitted')
                ->latest('reviewed_at')
                ->limit(20)
                ->get(),
            'schedules' => Schedule::where('is_active', '1')->orderBy('day_number')->orderBy('start_time')->get(),
            'counts' => [
                'camera' => $inProgress->count(),
                'payments' => $pendingProofs->count(),
                'confirmed' => $confirmed->count(),
            ],
        ]);
    }

    /**
     * Accepts a reported transfer: the paper is marked paid, as a gateway payment would
     * mark it, and the author receives the usual registration confirmation.
     */
    public function verifyPayment(PaperPaymentProof $proof)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($proof->status !== 'submitted') {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        $paper = $proof->paper;

        DB::transaction(function () use ($proof, $paper) {
            $proof->update(['status' => 'verified', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
            $paper->update(['payment_status' => '1', 'pay_amount' => $proof->amount, 'currency' => $proof->currency]);
            PaymentSync::refreshProfile($paper->user);
        });

        $profile = $paper->user->profile()->first();

        try {
            Mail::to($paper->user->email)->queue(new RegistrationConfirmed([
                'first_name' => $profile->first_name ?? '',
                'last_name' => $profile->last_name ?? '',
                'name' => $paper->user->name,
                'registration_id' => $profile->registration_id ?? '',
                'category' => 'Presenter',
                'mode' => $profile->participation_mode ?? 'Onsite',
                'amount' => $proof->amount,
                'currency' => $proof->currency,
                'transaction_id' => $proof->transaction_id,
            ]));
        } catch (\Exception $e) {
            Log::error('Registration confirmation after manual payment failed', ['proof' => $proof->id, 'error' => $e->getMessage()]);
        }

        // With every other item already in, the fee was the last thing outstanding, so
        // verifying it confirms the paper for the proceedings straight away. Files the
        // administrator has sent back for changes are left alone.
        $paper->load(['decision', 'cameraReady']);
        $autoConfirmed = $paper->cameraReady
            && $paper->cameraReady->status === 'submitted'
            && !ProceedingsRules::missing($paper);

        if ($autoConfirmed) {
            $paper->cameraReady->update(['status' => 'confirmed', 'confirmed_by' => auth()->id(), 'confirmed_at' => now()]);
            $this->tellAuthor($paper, 'confirmed');
        }

        return back()->with('success', 'Payment for ' . $paper->submission_id . ' verified.'
            . ($autoConfirmed ? ' Everything else was already in, so the paper is now Confirmed for Proceedings.' : ''));
    }

    public function rejectPayment(Request $request, PaperPaymentProof $proof)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($proof->status !== 'submitted') {
            return back()->with('error', 'This payment has already been reviewed.');
        }

        $data = $request->validate([
            'review_note' => 'required|string|max:1000',
        ], [
            'review_note.required' => 'Tell the author what is wrong with the payment, so they can correct it.',
        ]);

        $proof->update([
            'status' => 'rejected',
            'review_note' => $data['review_note'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Payment for ' . $proof->paper->submission_id . ' rejected. The author can report a corrected one.');
    }

    /** "Admin verifies payment and marks the paper as Confirmed for Proceedings." */
    public function confirm(Paper $paper)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['decision', 'cameraReady']);

        if ($paper->cameraReady?->isConfirmed()) {
            return back()->with('error', $paper->submission_id . ' is already confirmed for the proceedings.');
        }

        if ($missing = ProceedingsRules::missing($paper)) {
            return back()->with('error', $paper->submission_id . ' cannot be confirmed yet. Still outstanding: ' . implode('; ', $missing) . '.');
        }

        $paper->cameraReady->update(['status' => 'confirmed', 'confirmed_by' => auth()->id(), 'confirmed_at' => now()]);

        $this->tellAuthor($paper, 'confirmed');

        return back()->with('success', $paper->submission_id . ' is confirmed for the proceedings.');
    }

    /** Sends the files back to the author with a note; undoes a confirmation if there was one. */
    public function requestChanges(Request $request, Paper $paper)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load('cameraReady');

        if (!$paper->cameraReady) {
            return back()->with('error', 'The author has not uploaded anything yet.');
        }

        $data = $request->validate([
            'admin_note' => 'required|string|max:2000',
        ], [
            'admin_note.required' => 'Tell the author what to change.',
        ]);

        $paper->cameraReady->update([
            'status' => 'changes_requested',
            'admin_note' => $data['admin_note'],
            'confirmed_by' => null,
            'confirmed_at' => null,
            'schedule_id' => null,
            'presentation_order' => null,
        ]);

        $this->tellAuthor($paper, 'changes');

        return back()->with('success', 'Changes requested from the author of ' . $paper->submission_id . '.');
    }

    /** Places a confirmed paper in a programme session. */
    public function schedule(Request $request, Paper $paper)
    {
        abort_if(Gate::denies('camera_ready_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load('cameraReady');

        if (!$paper->cameraReady?->isConfirmed()) {
            return back()->with('error', 'Only papers confirmed for the proceedings can be scheduled.');
        }

        $data = $request->validate([
            'schedule_id' => 'nullable|integer|exists:schedules,id',
            'presentation_order' => 'nullable|integer|min:1|max:99',
        ]);

        $paper->cameraReady->update([
            'schedule_id' => $data['schedule_id'] ?? null,
            'presentation_order' => !empty($data['schedule_id']) ? ($data['presentation_order'] ?? null) : null,
        ]);

        return back()->with('success', $paper->submission_id . (empty($data['schedule_id']) ? ' removed from the programme.' : ' placed in the programme.'));
    }

    /**
     * json, xml, abstracts (PDF), program (PDF) or files (ZIP). Confirmed papers only,
     * unless scope=accepted asks for every accepted paper.
     */
    public function export(Request $request, string $format)
    {
        abort_if(Gate::denies('camera_ready_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $export = new ProceedingsExport($request->input('scope') !== 'accepted');
        $name = 'icmria-2027-' . ($export->confirmedOnly() ? 'proceedings' : 'accepted-papers') . '-' . now()->format('Ymd-His');

        switch ($format) {
            case 'json':
                return response(
                    json_encode($export->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    200,
                    ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="' . $name . '.json"']
                );

            case 'xml':
                return response($export->toXml(), 200, [
                    'Content-Type' => 'application/xml',
                    'Content-Disposition' => 'attachment; filename="' . $name . '.xml"',
                ]);

            case 'abstracts':
                return \PDF::loadView('admin.proceedings.pdf.abstracts', ['data' => $export->toArray()])
                    ->download('icmria-2027-book-of-abstracts-' . now()->format('Ymd') . '.pdf');

            case 'program':
                return \PDF::loadView('admin.proceedings.pdf.program', ['data' => $export->toArray()])
                    ->download('icmria-2027-programme-' . now()->format('Ymd') . '.pdf');

            case 'files':
                return $this->filesArchive($export, $name);
        }

        abort(Response::HTTP_NOT_FOUND);
    }

    /** Every camera-ready manuscript and copyright form, named by paper ID, with the JSON alongside. */
    private function filesArchive(ProceedingsExport $export, string $name)
    {
        $directory = storage_path('app/tmp');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $archivePath = $directory . '/' . $name . '-' . Str::random(6) . '.zip';
        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($export->papers() as $paper) {
            $final = $paper->cameraReady;

            foreach ([['camera_ready_path', 'camera_ready_name', 'camera-ready'], ['copyright_path', 'copyright_name', 'copyright']] as [$pathField, $nameField, $folder]) {
                if ($final?->$pathField && Storage::exists($final->$pathField)) {
                    $extension = pathinfo((string) $final->$nameField, PATHINFO_EXTENSION);
                    $zip->addFile(Storage::path($final->$pathField), $folder . '/' . $paper->submission_id . ($extension ? '.' . $extension : ''));
                }
            }
        }

        $zip->addFromString('proceedings.json', json_encode($export->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->close();

        return response()->download($archivePath, $name . '-files.zip')->deleteFileAfterSend(true);
    }

    private function tellAuthor(Paper $paper, string $kind): void
    {
        try {
            Mail::to($paper->user->email)->queue(new CameraReadyUpdate($paper, $kind));
        } catch (\Exception $e) {
            Log::error('Camera-ready update mail failed', ['paper' => $paper->id, 'kind' => $kind, 'error' => $e->getMessage()]);
        }
    }
}
