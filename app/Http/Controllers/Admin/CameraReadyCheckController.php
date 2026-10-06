<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CameraReadyUpdate;
use App\Models\Paper;
use App\Models\Track;
use App\Services\CameraReadyCheckers;
use App\Services\ChairScope;
use App\Services\PaperProgress;
use App\Services\ProceedingsConfirmation;
use App\Services\ProceedingsRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * The camera-ready check (organisers, 2026-10-06): once an accepted paper's camera-ready
 * manuscript and signed copyright form are in, the paper's Track Chair or Sub-Track Chair
 * approves the files or sends them back, before the author pays. Administrators and
 * Proceedings Editors see every track and may act too (CameraReadyCheckers).
 */
class CameraReadyCheckController extends Controller
{
    /** Tab => camera-ready statuses it shows. Approved includes approved-and-paid (confirmed). */
    private const FILTERS = [
        'pending' => ['submitted'],
        'changes_requested' => ['changes_requested'],
        'approved' => ['approved', 'confirmed'],
    ];

    /** When both files were in: the later of the two uploads. */
    private const FILES_IN_AT = 'GREATEST(COALESCE(cr.camera_ready_uploaded_at, 0), COALESCE(cr.copyright_uploaded_at, 0))';

    public const SORTS = [
        'waiting' => 'Waiting longest first',
        'recent' => 'Most recent upload first',
        'checked' => 'Checked most recently',
        'id' => 'Paper ID (ascending)',
        'id_desc' => 'Paper ID (descending)',
        'title' => 'Title (A–Z)',
        'track' => 'Track, then sub-track',
    ];

    private const PER_PAGE = [25, 50, 100];

    public function index(Request $request)
    {
        abort_if(Gate::denies('camera_ready_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        $scope = ChairScope::for($user);
        $allTracks = CameraReadyCheckers::seesAllTracks($user);
        [$filter, $trackFilter, $search, $sort] = $this->listOptions($request);
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE, true) ? $request->integer('per_page') : self::PER_PAGE[0];

        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])
            ->when(!$allTracks, fn ($q) => $q->whereIn('id', $scope->trackIds() ?: [0]))
            ->orderBy('id')
            ->get();

        // Tab counts follow the track filter and search, so they match what a tab shows.
        $counts = [];
        foreach (self::FILTERS as $key => $statuses) {
            $counts[$key] = $this->listQuery($user, $key, $trackFilter, $search)->count();
        }

        $papers = $this->sorted($this->listQuery($user, $filter, $trackFilter, $search), $sort)
            ->with(['track', 'subTrack', 'cameraReady.filesReviewedBy', 'authors', 'manuscriptVersions'])
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.camera_ready_checks.index', [
            'papers' => $papers,
            'counts' => $counts,
            'filter' => $filter,
            'tracks' => $tracks,
            'trackFilter' => $trackFilter,
            'search' => $search,
            'sort' => $sort,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'allTracks' => $allTracks,
            'hasNoScope' => !$allTracks && $scope->isEmpty(),
        ]);
    }

    /**
     * Every file of one paper in a single ZIP: the reviewed manuscript (current version),
     * the revised manuscript if any, the camera-ready manuscript and the copyright form,
     * each named by paper ID and kind.
     */
    public function downloadZip(Paper $paper)
    {
        abort_if(Gate::denies('camera_ready_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $paper->load(['cameraReady', 'manuscriptVersions', 'authors', 'conflicts', 'user']);
        abort_unless(CameraReadyCheckers::canCheck(auth()->user(), $paper), Response::HTTP_FORBIDDEN,
            '403 Forbidden - that paper is outside your tracks, or you have a conflict with it.');

        $final = $paper->cameraReady;
        $version = $paper->manuscriptVersions->max('version');

        $files = collect([
            [$paper->manuscript_path, $paper->downloadName('manuscript', $paper->manuscript_original_name, $version ? (int) $version : null)],
            [$final?->revised_path, $paper->downloadName('revised-manuscript', $final?->revised_name)],
            [$final?->camera_ready_path, $paper->downloadName('camera-ready', $final?->camera_ready_name)],
            [$final?->copyright_path, $paper->downloadName('copyright-form', $final?->copyright_name)],
        ])->filter(fn ($file) => $file[0] && Storage::exists($file[0]));

        // An empty archive is never written to disk, so say so instead of failing.
        if ($files->isEmpty()) {
            return back()->with('error', 'None of the files of ' . $paper->submission_id . ' were found on the server.');
        }

        $directory = storage_path('app/tmp');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $archivePath = $directory . '/' . Str::random(12) . '.zip';
        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($files as [$path, $name]) {
            $zip->addFile(Storage::path($path), $name);
        }
        $zip->close();

        return response()->download($archivePath, $paper->downloadName('files', 'x.zip'))->deleteFileAfterSend(true);
    }

    /** @return array{0: string, 1: string, 2: string, 3: string} tab, track, search, sort */
    private function listOptions(Request $request): array
    {
        $filter = $request->string('filter')->toString();
        $sort = $request->string('sort')->toString();

        return [
            array_key_exists($filter, self::FILTERS) ? $filter : 'pending',
            // "t<id>" for a whole track, "s<id>" for one sub-track.
            $request->string('track')->toString(),
            trim($request->string('q')->toString()),
            array_key_exists($sort, self::SORTS) ? $sort : 'waiting',
        ];
    }

    /**
     * The papers this person may check, in one tab, narrowed by track and search. Kept in
     * SQL so a list of hundreds of papers is paged by the database. Papers the person
     * wrote, or where a conflict with them was declared, are left out (as
     * ChairScope::conflictWith decides for one paper).
     */
    private function listQuery($user, string $filter, string $trackFilter, string $search)
    {
        $query = Paper::accepted()
            ->join('paper_camera_ready as cr', 'cr.paper_id', '=', 'papers.id')
            ->select('papers.*')
            ->whereNotNull('cr.camera_ready_path')
            ->whereNotNull('cr.copyright_path')
            ->whereIn('cr.status', self::FILTERS[$filter]);

        if (!CameraReadyCheckers::seesAllTracks($user)) {
            $query = ChairScope::for($user)->constrainPapers($query);
        }

        $email = Str::lower((string) $user->email);
        $query->whereDoesntHave('authors', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))
            ->whereDoesntHave('user', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))
            ->whereDoesntHave('conflicts', fn ($q) => $q->where('conflicted_user_id', $user->id));

        if (!\App\Services\SubmissionRules::reviewerChairMayDecide()) {
            $query->whereDoesntHave('reviewerAssignments', fn ($q) => $q->where('reviewer_id', $user->id)->where('status', '!=', 'declined'));
        }

        if (preg_match('/^t(\d+)$/', $trackFilter, $m)) {
            $query->where('papers.track_id', (int) $m[1]);
        } elseif (preg_match('/^s(\d+)$/', $trackFilter, $m)) {
            $query->where('papers.sub_track_id', (int) $m[1]);
        }

        if ($search !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search) . '%';
            $query->where(fn ($q) => $q->where('papers.submission_id', 'like', $like)
                ->orWhere('papers.title', 'like', $like)
                ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', $like)->orWhere('email', 'like', $like)));
        }

        return $query;
    }

    private function sorted($query, string $sort)
    {
        return (match ($sort) {
            'recent' => $query->orderByRaw(self::FILES_IN_AT . ' DESC'),
            'checked' => $query->orderByRaw('cr.files_reviewed_at IS NULL')->orderByDesc('cr.files_reviewed_at'),
            'id' => $query->orderBy('papers.id'),
            'id_desc' => $query->orderByDesc('papers.id'),
            'title' => $query->orderBy('papers.title'),
            'track' => $query->orderBy('papers.track_id')->orderBy('papers.sub_track_id'),
            default => $query->orderByRaw(self::FILES_IN_AT . ' ASC'),
        })->orderBy('papers.id');
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

        abort_unless(CameraReadyCheckers::canCheck(auth()->user(), $paper), Response::HTTP_FORBIDDEN,
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
