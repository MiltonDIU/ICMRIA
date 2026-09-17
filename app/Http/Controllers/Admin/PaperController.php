<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperAuthor;
use App\Models\Setting;
use App\Models\Country;
use App\Models\Track;
use App\Models\SubTrack;
use App\Models\Profile;
use App\Mail\AbstractSubmitted;
use App\Mail\AbstractAccepted;
use App\Mail\AbstractRejected;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PaperController extends Controller
{
    public function index(Request $request)
    {
        $settings = Setting::pluck('value', 'key');
        $eventStartDate = Carbon::parse($settings['registration_start_date'] ?? now());
        $abstractDeadline = Carbon::parse($settings['abstract_submission_deadline'] ?? $settings['registration_close_date'] ?? now());
        $paymentLastDate = isset($settings['payment_last_date']) ? Carbon::parse($settings['payment_last_date']) : null;
        $isSubmissionOpen = \App\Services\SubmissionRules::abstractWindowIsOpen();
        $isPaymentOpen = \App\Services\ProceedingsRules::paymentWindowIsOpen();

        if ($request->ajax()) {
            $user = Auth::user();

            abort_if(Gate::denies('paper_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            // Which papers are listed follows who is asking, not the permission alone.
            // SuperAdmin, Admin and the TPC Chair see every paper; a Track Chair the papers of
            // their whole track; a Sub-Track Chair those of their own sub-tracks. Everyone also
            // sees their own submissions, so a chair who is an author still finds theirs.
            $scope = \App\Services\ChairScope::for($user);
            $query = Paper::select('papers.*')
                ->with('user.papers', 'user.profile.country', 'track', 'subTrack', 'authors.country')
                ->when(!$scope->seesEverything(), function ($query) use ($scope, $user) {
                    $query->where(function ($visible) use ($scope, $user) {
                        $visible->where('papers.user_id', $user->id);

                        if (!$scope->isEmpty()) {
                            $visible->orWhere(fn ($chaired) => $scope->constrainPapers($chaired));
                        }
                    });
                })
                ->orderBy('id', 'desc');

            // Apply Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('track_id')) {
                $query->where('track_id', $request->track_id);
            }
            if ($request->filled('payment_status')) {
                $paymentStatus = $request->payment_status == 'paid' ? '1' : ($request->payment_status == 'unpaid' ? '0' : null);
                if ($paymentStatus !== null) {
                    $query->where('payment_status', $paymentStatus);
                }
            }
            if ($request->filled('department')) {
                $dept = $request->department;
                $query->where(function($q) use ($dept) {
                    $q->whereHas('authors', function($authQuery) use ($dept) {
                        $authQuery->where('department', 'like', "%{$dept}%");
                    })->orWhereHas('user.profile', function($profileQuery) use ($dept) {
                        $profileQuery->where('department', 'like', "%{$dept}%");
                    });
                });
            }
            if ($request->filled('institution')) {
                $inst = $request->institution;
                $query->where(function($q) use ($inst) {
                    $q->whereHas('authors', function($authQuery) use ($inst) {
                        $authQuery->where('institution', 'like', "%{$inst}%");
                    })->orWhereHas('user.profile', function($profileQuery) use ($inst) {
                        $profileQuery->where('institution', 'like', "%{$inst}%");
                    });
                });
            }
            if ($request->filled('country_id')) {
                $countryId = $request->country_id;
                $query->where(function($q) use ($countryId) {
                    $q->whereHas('authors', function($authQuery) use ($countryId) {
                        $authQuery->where('country_id', $countryId);
                    })->orWhereHas('user.profile', function($profileQuery) use ($countryId) {
                        $profileQuery->where('country_id', $countryId);
                    });
                });
            }

            return DataTables::of($query)
                ->filterColumn('designation', function($q, $keyword) {
                    $q->where(function($sub) use ($keyword) {
                        $sub->whereHas('authors', function($authQuery) use ($keyword) {
                            $authQuery->where('designation', 'like', "%{$keyword}%");
                        })->orWhereHas('user.profile', function($profileQuery) use ($keyword) {
                            $profileQuery->where('designation', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('submitted_by', function($q, $keyword) {
                    $q->whereHas('user', function($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('submitter_email', function($q, $keyword) {
                    $q->whereHas('user', function($userQuery) use ($keyword) {
                        $userQuery->where('email', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('pay_amount', function($q, $keyword) {
                    $q->where('papers.pay_amount', 'like', "%{$keyword}%");
                })
                ->filterColumn('currency', function($q, $keyword) {
                    $q->where('papers.currency', 'like', "%{$keyword}%");
                })
                ->filterColumn('department', function($q, $keyword) {
                    $q->where(function($sub) use ($keyword) {
                        $sub->whereHas('authors', function($authQuery) use ($keyword) {
                            $authQuery->where('department', 'like', "%{$keyword}%");
                        })->orWhereHas('user.profile', function($profileQuery) use ($keyword) {
                            $profileQuery->where('department', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('institution', function($q, $keyword) {
                    $q->where(function($sub) use ($keyword) {
                        $sub->whereHas('authors', function($authQuery) use ($keyword) {
                            $authQuery->where('institution', 'like', "%{$keyword}%");
                        })->orWhereHas('user.profile', function($profileQuery) use ($keyword) {
                            $profileQuery->where('institution', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('country', function($q, $keyword) {
                    $q->where(function($sub) use ($keyword) {
                        $sub->whereHas('authors.country', function($countryQuery) use ($keyword) {
                            $countryQuery->where('name', 'like', "%{$keyword}%");
                        })->orWhereHas('user.profile.country', function($countryQuery) use ($keyword) {
                            $countryQuery->where('name', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->addColumn('actions', function ($row) use ($isSubmissionOpen, $isPaymentOpen, $user) {
                    $viewRoute = route('papers.show', $row->id);
                    $editRoute = route('papers.edit', $row->id);

                    // Show Edit button for authors only if paper is pending and abstract submission is open
                    $editBtn = '';
                    if (Auth::user()->roles->contains('id', 3) && $row->user_id === Auth::id() && $row->status === 'pending' && $isSubmissionOpen) {
                        $editBtn = ' <a href="'.$editRoute.'" class="btn btn-sm btn-white border text-info" title="Edit Paper">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                    } elseif (Gate::allows('paper_edit')) {
                        // For Admin/Others with mass edit permission
                         $editBtn = ' <a href="'.$editRoute.'" class="btn btn-sm btn-white border text-info" title="Edit Paper">
                                        <i class="fas fa-edit"></i>
                                    </a>';
                    }

                    $payBtn = '';
                    if ($row->status === 'approved' && $row->payment_status != '1' && Auth::user()->roles->contains('id', 3) && $isPaymentOpen) {
                        if ($user->profile && !$user->profile->author_list_confirmed) {
                            $payBtn = ' <a href="'.route('show-profile').'" class="btn btn-sm btn-warning ml-1" title="Confirm Authors First">
                                            <i class="fas fa-id-card mr-1"></i> Confirm Authors
                                        </a>';
                        } else {
                            $payBtn = ' <button class="btn btn-sm btn-primary ml-1" onclick="openPaymentModal('.$row->id.')" title="Payment Review">
                                            <i class="fas fa-credit-card mr-1"></i> Pay
                                        </button>';
                        }
                    }

                    return '<div class="btn-group shadow-sm">
                                <a href="'.$viewRoute.'" class="btn btn-sm btn-white border text-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                '.$editBtn.'
                                '.$payBtn.'
                            </div>';
                })
                ->editColumn('submission_id', function ($row) {
                    return '<span class="font-weight-bold text-primary">'.$row->submission_id.'</span>';
                })
                ->addColumn('submitted_by', function ($row) {
                    $name = $row->user->name ?? 'N/A';

                    if ($row->user && $row->user->papers->count() >= 2) {
                        $otherPapers = $row->user->papers->reject(function ($p) use ($row) {
                            return $p->id === $row->id;
                        });

                        $otherLinks = [];
                        foreach ($otherPapers as $p) {
                            $viewUrl = route('papers.show', $p->id);
                            $otherLinks[] = '<a href="' . $viewUrl . '" class="badge badge-light border text-primary mr-1" title="' . e($p->title) . '">' . e($p->submission_id) . '</a>';
                        }

                        $otherLinksHtml = implode('', $otherLinks);

                        return '<div class="d-flex flex-column">' .
                                    '<span class="font-weight-bold text-dark">' . $name . '</span>' .
                                    '<div class="mt-1" style="font-size: 0.75rem; line-height: 1.4;">' .
                                        '<span class="text-muted mr-1" style="font-size: 0.7rem;">Others:</span>' . $otherLinksHtml .
                                    '</div>' .
                               '</div>';
                    }
                    
                    return $name;
                })
                ->addColumn('submitter_email', function ($row) {
                    return $row->user->email ?? 'N/A';
                })
                ->addColumn('authors', function ($row) {
                    $authors = $row->authors->pluck('name')->toArray();
                    if (empty($authors) && $row->user) {
                        $authors[] = $row->user->name;
                    }
                    $authorText = implode(', ', $authors);
                    return '<div class="text-muted small" title="'.$authorText.'">'.$authorText.'</div>';
                })
                ->addColumn('total_member', function ($row) {
                    $count = $row->authors->count();
                    if ($count === 0 && $row->user) {
                        $count = 1;
                    }
                    return '<span class="badge badge-light border font-weight-bold px-2 py-1 rounded-pill">' . $count . '</span>';
                })
                ->addColumn('designation', function ($row) {
                    $author = $row->authors->where('is_presenting_author', 1)->first() ?? $row->authors->first();
                    $designation = $author?->designation ?? null;
                    if (empty($designation) || trim($designation) === '' || strtolower(trim($designation)) === 'n/a' || strtolower(trim($designation)) === 'null') {
                        $designation = $row->user?->profile?->designation ?? null;
                    }
                    return $designation ?: 'N/A';
                })
                ->addColumn('department', function ($row) {
                    $author = $row->authors->where('is_presenting_author', 1)->first() ?? $row->authors->first();
                    $dept = $author?->department ?? null;
                    if (empty($dept) || trim($dept) === '' || strtolower(trim($dept)) === 'n/a' || strtolower(trim($dept)) === 'null') {
                        $dept = $row->user?->profile?->department ?? null;
                    }
                    return $dept ?: 'N/A';
                })
                ->addColumn('institution', function ($row) {
                    $author = $row->authors->where('is_presenting_author', 1)->first() ?? $row->authors->first();
                    $inst = $author?->institution ?? null;
                    if (empty($inst) || trim($inst) === '' || strtolower(trim($inst)) === 'n/a' || strtolower(trim($inst)) === 'null') {
                        $inst = $row->user?->profile?->institution ?? null;
                    }
                    return $inst ?: 'N/A';
                })
                ->addColumn('country', function ($row) {
                    $author = $row->authors->where('is_presenting_author', 1)->first() ?? $row->authors->first();
                    $country = $author?->country?->name ?? null;
                    if (empty($country) || trim($country) === '' || strtolower(trim($country)) === 'n/a' || strtolower(trim($country)) === 'null') {
                        $country = $row->user?->profile?->country?->name ?? null;
                    }
                    return $country ?: 'N/A';
                })
                ->editColumn('mode_of_participation', function ($row) {
                    $mode = $row->mode_of_participation ?: $row->user?->profile?->participation_mode ?? null;
                    return $mode ? ucfirst($mode) : 'N/A';
                })
                ->editColumn('pay_amount', function ($row) {
                    if ($row->payment_status == '1') {
                        $amount = $row->pay_amount;
                    } else {
                        if ($row->user?->profile) {
                            $pricing = \App\Services\PricingService::calculatePaperCost($row->user->profile, $row);
                            $amount = $pricing['final_price'];
                        } else {
                            $amount = $row->pay_amount;
                        }
                    }
                    return $amount !== null ? number_format($amount, 2) : 'N/A';
                })
                ->editColumn('currency', function ($row) {
                    if ($row->payment_status == '1') {
                        return $row->currency ?: 'N/A';
                    } else {
                        if ($row->user?->profile) {
                            $pricing = \App\Services\PricingService::calculatePaperCost($row->user->profile, $row);
                            return $pricing['currency'];
                        }
                        return $row->currency ?? 'N/A';
                    }
                })
                ->editColumn('title', function ($row) {
                    return '<div class="text-dark font-weight-600 text-truncate" style="max-width: 300px;" title="'.$row->title.'">'.$row->title.'</div>';
                })
                ->editColumn('track', function ($row) {
                    $trackName = $row->track->name ?? 'N/A';
                    $subTrackHtml = $row->subTrack ? '<small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1.2;"><i class="fas fa-caret-right mr-1"></i> '.$row->subTrack->name.'</small>' : '';
                    return '<span class="badge badge-light border text-dark px-2 py-1 rounded-pill d-block mb-1 text-truncate" style="max-width: 150px;" title="'.$trackName.'">'.$trackName.'</span>' . $subTrackHtml;
                })
                ->editColumn('status', function ($row) {
                    $statusClass = [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    ][$row->status] ?? 'secondary';

                    $badge = '<span class="badge badge-'.$statusClass.' px-3 py-2 text-uppercase shadow-none border-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">'.$row->status.'</span>';

                    if ($row->status === 'approved') {
                        $pStatusClass = $row->payment_status == '1' ? 'success' : 'warning';
                        $pStatusText = $row->payment_status == '1' ? 'PAID' : 'UNPAID';
                        $badge .= '<span class="badge badge-'.$pStatusClass.' d-block mt-1" style="font-size: 0.65rem;">'.$pStatusText.'</span>';
                    }

                    return $badge;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('M d, Y') : '';
                })
                ->rawColumns(['actions', 'submission_id', 'submitted_by', 'title', 'authors', 'total_member', 'track', 'status'])
                ->make(true);
        }

        $user = Auth::user();
        $myProfile = null;
        $unpaidPapers = collect();
        if ($user) {
            $myProfile = Profile::where('user_id', $user->id)->first();
            if ($user->roles->contains('id', 3)) {
                $unpaidPapers = Paper::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where(function($q) {
                        $q->whereNull('payment_status')
                          ->orWhere('payment_status', '!=', '1');
                    })->with('authors.country')->get();
            }
        }

        // A chair filters only among the tracks they can see.
        $scope = $user ? \App\Services\ChairScope::for($user) : null;
        $tracks = $scope && !$scope->seesEverything() && !$scope->isEmpty()
            ? Track::whereIn('id', $scope->trackIds())->get()
            : Track::all();
        $countries = Country::orderBy('name', 'asc')->get();
        return view('admin.papers.index', compact('tracks', 'countries', 'myProfile', 'unpaidPapers'));
    }

    public function getPaperPricing(Paper $paper)
    {
        $user = Auth::user();
        // The response names every author, so only the paper's own author or a chair whose
        // scope covers it may ask; anyone else, a reviewer included, could otherwise read
        // author names that double-blind review withholds.
        $isOwner = (int) $paper->user_id === (int) $user->id;
        if (!$isOwner && !(Gate::allows('paper_access') && \App\Services\ChairScope::for($user)->canSee($paper))) {
            return response()->json(['error' => 'Unauthorized access to this paper.'], 403);
        }

        if (!$user->profile) {
            Log::warning('Paper pricing failed: Profile not found for User ID ' . $user->id);
            return response()->json(['error' => 'Your profile details are missing. Please complete your profile first.'], 422);
        }

        if ($user->roles->contains('id', 3) && !$user->profile->author_list_confirmed) {
            return response()->json(['error' => 'Please confirm your author list and student status first.'], 422);
        }

        try {
            $pricing = \App\Services\PricingService::calculatePaperCost($user->profile, $paper);
            $authors = $paper->authors->map(function($author) use ($pricing) {
                return [
                    'name' => $author->name,
                    'designation' => $author->designation,
                    'fee' => $pricing['author_fees'][$author->id] ?? $pricing['individual_final_price']
                ];
            });

            return response()->json([
                'submission_id' => $paper->submission_id,
                'pricing' => $pricing,
                'authors' => $authors,
                'paper_id' => $paper->id,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Paper pricing calculation error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'paper_id' => $paper->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to calculate pricing. Details: ' . $e->getMessage()], 500);
        }
    }

    public function show(Paper $paper)
    {
        $user = Auth::user();

        // The paper's author, or someone with paper access whose chair scope covers it
        // (SuperAdmin, Admin and the TPC Chair cover every paper).
        $isOwner = (int) $paper->user_id === (int) $user->id;
        $chairsIt = Gate::allows('paper_access') && \App\Services\ChairScope::for($user)->canSee($paper);

        abort_unless($isOwner || $chairsIt, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $paper->load('authors', 'user', 'reviewHistory.reviewer', 'conflicts.conflictedUser',
            'manuscriptVersions.uploadedBy', 'decision', 'reviewerAssignments.evaluation',
            'cameraReady.schedule', 'paymentProofs');

        return view('admin.papers.show', [
            'paper' => $paper,
            'manuscriptWindowOpen' => \App\Services\SubmissionRules::manuscriptWindowIsOpen(),
            'manuscriptOpensAt' => \App\Services\SubmissionRules::manuscriptWindowOpensAt(),
            'manuscriptClosesAt' => \App\Services\SubmissionRules::manuscriptWindowClosesAt(),
            'conflictCandidates' => $this->conflictCandidates($paper),
        ]);
    }

    /**
     * The manuscript the author submits for review, uploaded or replaced while the
     * window in the settings is open.
     */
    public function uploadManuscript(Request $request, Paper $paper)
    {
        $user = Auth::user();
        $isOwner = (int) $paper->user_id === (int) $user->id;

        if ($isOwner) {
            if (!\App\Services\SubmissionRules::manuscriptWindowIsOpen()) {
                return back()->with('error', 'The manuscript submission window is closed.');
            }
        } else {
            abort_if(Gate::denies('paper_manuscript_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $request->validate([
            'manuscript' => [
                'required',
                'file',
                'mimes:' . implode(',', \App\Services\SubmissionRules::MANUSCRIPT_MIMES),
                'max:' . \App\Services\SubmissionRules::MANUSCRIPT_MAX_KB,
            ],
            // The system cannot strip names from inside a PDF, so under double-blind
            // review the author has to state that they have done it.
            'format_confirmed' => ['accepted'],
            'anonymity_confirmed' => \App\Services\SubmissionRules::isDoubleBlind() ? ['accepted'] : ['nullable'],
        ], [
            'manuscript.mimes' => 'The manuscript must be a PDF or Word document.',
            'manuscript.max' => 'The manuscript may not be larger than 20 MB.',
            'anonymity_confirmed.accepted' => 'Please confirm the file carries no author names or affiliations.',
            'format_confirmed.accepted' => 'Please confirm the manuscript follows the IEEE conference template and the page limit.',
        ]);

        $file = $request->file('manuscript');
        $replacing = $paper->manuscript_path;

        // Kept off the public disk: an anonymised manuscript under review must not be
        // reachable by guessing a URL.
        $path = $file->store('manuscripts/' . $paper->id);

        $paper->update([
            'manuscript_path' => $path,
            'manuscript_original_name' => $file->getClientOriginalName(),
            'manuscript_uploaded_at' => now(),
            'manuscript_status' => $replacing ? 'revised' : 'submitted',
        ]);

        // The superseded file is kept. Once review has begun it is the only record of
        // what a reviewer actually read, and discarding submitted work cannot be undone.
        \App\Models\PaperManuscriptVersion::create([
            'paper_id' => $paper->id,
            'uploaded_by' => $user->id,
            'version' => (int) $paper->manuscriptVersions()->max('version') + 1,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'anonymity_confirmed' => (bool) $request->boolean('anonymity_confirmed'),
        ]);

        try {
            Mail::to($paper->user->email)->queue(new \App\Mail\ManuscriptReceived($paper->fresh()));
        } catch (\Exception $e) {
            Log::error('Manuscript confirmation mail failed', ['paper' => $paper->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', $replacing
            ? 'Manuscript replaced. The earlier version is kept on record.'
            : 'Manuscript uploaded.');
    }

    /**
     * Streams a stored manuscript to anyone allowed to read the paper. Without a
     * version it serves the current file; with one it serves that earlier upload,
     * which is how a chair checks what a reviewer was given.
     */
    public function downloadManuscript(Paper $paper, ?int $version = null)
    {
        $user = Auth::user();

        // Decided by the person's relation to this paper, not by role. A permission alone
        // let every reviewer read every manuscript, and one person can be an author, a
        // chair and a reviewer at once, each reaching different papers.
        $isOwner = $paper->user_id === $user->id;
        $chairsIt = $paper->track_id
            && \App\Services\ChairScope::for($user)->canManage($paper->track_id, $paper->sub_track_id);
        $reviewsIt = $paper->reviewerAssignments()
            ->where('reviewer_id', $user->id)
            ->where('status', '!=', 'declined')
            ->exists();

        abort_unless($isOwner || $chairsIt || $reviewsIt, Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Under double-blind review a reviewer gets the file under the paper ID. The name
        // the author uploaded it with often carries their own name.
        $downloadName = function (?string $original) use ($paper, $isOwner, $chairsIt) {
            if ($isOwner || $chairsIt || !\App\Services\SubmissionRules::isDoubleBlind()) {
                return $original;
            }

            $extension = pathinfo((string) $original, PATHINFO_EXTENSION);

            return $paper->submission_id . ($extension ? '.' . $extension : '');
        };

        if ($version !== null) {
            $record = $paper->manuscriptVersions()->where('version', $version)->first();
            abort_if(!$record || !Storage::exists($record->path), Response::HTTP_NOT_FOUND, 'That version is not on file.');

            return Storage::download($record->path, $downloadName($record->original_name));
        }

        abort_if(!$paper->manuscript_path || !Storage::exists($paper->manuscript_path),
            Response::HTTP_NOT_FOUND, 'No manuscript on file.');

        return Storage::download($paper->manuscript_path, $downloadName($paper->manuscript_original_name));
    }

    /**
     * Conflicts of interest the author declares, so that reviewer assignment can
     * steer around them (document, Phase 2).
     */
    public function declareConflict(Request $request, Paper $paper)
    {
        $user = Auth::user();
        $isOwner = (int) $paper->user_id === (int) $user->id;

        if (!$isOwner) {
            abort_if(Gate::denies('paper_conflict_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $data = $request->validate([
            'conflicted_user_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:255',
        ], [
            'conflicted_user_id.required' => 'Choose the chair or reviewer you have a conflict with.',
        ]);

        \App\Models\PaperConflict::firstOrCreate([
            'paper_id' => $paper->id,
            'conflicted_user_id' => $data['conflicted_user_id'],
        ], [
            'declared_by_user_id' => $user->id,
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Conflict of interest recorded. Reviewer assignment will avoid it.');
    }

    public function removeConflict(Paper $paper, \App\Models\PaperConflict $conflict)
    {
        $user = Auth::user();
        $isOwner = (int) $paper->user_id === (int) $user->id;

        if (!$isOwner) {
            abort_if(Gate::denies('paper_conflict_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        abort_if($conflict->paper_id !== $paper->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $conflict->delete();

        return back()->with('success', 'Conflict removed.');
    }

    /**
     * People an author might reasonably declare a conflict with: the chairs and
     * reviewers who could end up handling this paper's track.
     */
    private function conflictCandidates(Paper $paper)
    {
        if (!$paper->track_id) {
            return collect();
        }

        return \App\Models\User::whereHas('trackAssignments', function ($query) use ($paper) {
                $query->where('track_id', $paper->track_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is an author
        $profile = Profile::where('user_id', $user->id)->first();


        if (!$profile || !$profile->is_author) {
            return redirect()->route('show-profile')->with('error', 'Only registered authors can submit papers.');
        }


        $settings = Setting::pluck('value', 'key');
        $eventStartDate = Carbon::parse($settings['registration_start_date'] ?? now());
        $abstractDeadline = Carbon::parse($settings['abstract_submission_deadline'] ?? $settings['registration_close_date'] ?? now());
        $currentDate = Carbon::now();

        if (!\App\Services\SubmissionRules::abstractWindowIsOpen()) {
            return redirect()->route('show-profile')->with('error', 'Abstract submission is currently closed.');
        }

        $maxSubmissions = (int) ($settings['maximum_abstract_submission'] ?? $settings['maximum_abastract_submission'] ?? 1);
        $userPaperCount = Paper::where('user_id', $user->id)->count();

        if ($userPaperCount >= $maxSubmissions) {
            return redirect()->route('papers.index')->with('error', 'You have reached the maximum allowed limit of ' . $maxSubmissions . ' abstract submissions.');
        }

        $countries = Country::all();
        $tracks = Track::with('subTracks')->get();
        $prices = \App\Models\Price::orderBy('id')->get();
        $countryCategories = $this->countryCategoryMap($countries);
        $priceTable = $this->priceTableForJs($prices);
        $currentStage = \App\Services\PricingService::currentStage();

        return view('admin.papers.create', compact('countries', 'tracks', 'prices', 'countryCategories', 'priceTable', 'currentStage'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = Profile::where('user_id', $user->id)->first();
        if (!$profile || !$profile->is_author) {
            return redirect()->route('show-profile')->with('error', 'Only registered authors can submit papers.');
        }

        $settings = Setting::pluck('value', 'key');
        $eventStartDate = Carbon::parse($settings['registration_start_date'] ?? now());
        $abstractDeadline = Carbon::parse($settings['abstract_submission_deadline'] ?? $settings['registration_close_date'] ?? now());
        $currentDate = Carbon::now();

        if (!\App\Services\SubmissionRules::abstractWindowIsOpen()) {
            return redirect()->route('show-profile')->with('error', 'Abstract submission is currently closed.');
        }

        $maxSubmissions = (int) ($settings['maximum_abstract_submission'] ?? $settings['maximum_abastract_submission'] ?? 1);
        $userPaperCount = Paper::where('user_id', $user->id)->count();

        if ($userPaperCount >= $maxSubmissions) {
            return redirect()->route('papers.index')->with('error', 'You have reached the maximum allowed limit of ' . $maxSubmissions . ' abstract submissions.');
        }

        // PHP Tag Check Regex
        $noPhpTags = 'regex:/^((?!(<\?php|<\?|\?>)).)*$/is';

        $rules = [
            'paper_title' => ['required', 'string', 'max:255', $noPhpTags],
            'abstract_text' => \App\Services\SubmissionRules::abstractRules([$noPhpTags]),
            'keywords' => \App\Services\SubmissionRules::keywordRules([$noPhpTags]),
            'track_id' => ['required', 'exists:tracks,id'],
            'sub_track_id' => ['required', 'exists:sub_tracks,id'],
            'is_corresponding_author' => ['required', 'boolean'],
            'corresponding_author_index' => ['nullable', 'integer'],
            'presenting_author_index' => ['nullable', 'integer'],
            'consent_original' => ['accepted'],
            'consent_review' => ['accepted'],
            'consent_acceptance' => ['accepted'],
            'consent_no_late_addition' => ['accepted'],
            'co_authors.*.name' => ['required', 'string', 'max:255', $noPhpTags],
            'co_authors.*.email' => ['required', 'email', 'max:255'],
            'co_authors.*.designation' => ['required', 'string', 'max:255', $noPhpTags],
            'co_authors.*.department' => ['required', 'string', 'max:255', $noPhpTags],
            'co_authors.*.institution' => ['required', 'string', 'max:255', $noPhpTags],
            'co_authors.*.country_id' => ['required', 'exists:countries,id'],
            'co_authors.*.is_student' => ['nullable', 'in:0,1'],
        ];

        // The category rule needs that row's country, so it cannot use a wildcard.
        foreach ((array) $request->input('co_authors', []) as $index => $author) {
            $rules["co_authors.$index.price_id"] = ['required', 'exists:prices,id',
                new \App\Rules\DelegateCategoryMatchesCountry($author['country_id'] ?? null)];
        }

        // Conflicts of interest declared with the abstract, all optional.
        $rules = array_merge($rules, \App\Services\ConflictCandidates::rules());

        $request->validate($rules, [
            'regex' => 'The :attribute contains forbidden characters (PHP tags are not allowed).',
        ]);

        try {
            DB::beginTransaction();

            // Generate Submission ID
            $submissionId = \App\Services\IdGeneratorService::generateSubmissionId();
            $hasCoAuthors = $request->has('co_authors') && count($request->co_authors) > 1;

            $isCorrespondingAuthor = $request->boolean('is_corresponding_author');
            $correspondingAuthorIndex = $request->input('corresponding_author_index');
            if ($correspondingAuthorIndex !== null) {
                $isCorrespondingAuthor = ((int)$correspondingAuthorIndex === 0);
            } else {
                $correspondingAuthorIndex = $isCorrespondingAuthor ? 0 : 0;
            }

            $paper = Paper::create([
                'user_id' => $user->id,
                'submission_id' => $submissionId,
                'title' => $request->paper_title,
                'abstract' => $request->abstract_text,
                'keywords' => \App\Services\SubmissionRules::splitKeywords($request->keywords),
                'track_id' => $request->track_id,
                'sub_track_id' => $request->sub_track_id,
                'mode_of_participation' => $profile->participation_mode ?? 'onsite',
                'is_corresponding_author' => $isCorrespondingAuthor ? 1 : 0,
                'has_multiple_authors' => $hasCoAuthors,
            ]);

            $presentingAuthorIndex = $request->presenting_author_index ?? 0;

            if ($request->has('co_authors') && count($request->co_authors) > 0) {
                foreach ($request->co_authors as $index => $authorData) {
                    PaperAuthor::create([
                        'paper_id' => $paper->id,
                        'name' => $authorData['name'],
                        'email' => $authorData['email'],
                        'designation' => $authorData['designation'],
                        'department' => $authorData['department'] ?? 'N/A',
                        'institution' => $authorData['institution'],
                        'country_id' => $authorData['country_id'],
                        'price_id' => $authorData['price_id'] ?? null,
                        'is_student' => ($authorData['is_student'] ?? '0') == '1',
                        'author_order' => $index + 1,
                        'is_presenting_author' => ($index == $presentingAuthorIndex) ? 1 : 0,
                        'is_corresponding_author' => ($correspondingAuthorIndex !== null && (int)$correspondingAuthorIndex === (int)$index) ? 1 : 0,
                    ]);
                }
            } else {
                // Fallback if no authors submitted from frontend
                PaperAuthor::create([
                    'paper_id' => $paper->id,
                    'name' => trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: $user->name,
                    'designation' => $profile->designation ?? null,
                    'department' => $profile->department ?? null,
                    'institution' => $profile->institution ?? null,
                    'country_id' => $profile->country_id ?? null,
                    'price_id' => $profile->price_id,
                    'email' => $user->email,
                    'author_order' => 1,
                    'is_presenting_author' => 1,
                    'is_corresponding_author' => 1,
                ]);
            }

            if ($paper->authors()->where('is_corresponding_author', 1)->count() === 0) {
                $paper->authors()->first()?->update(['is_corresponding_author' => 1]);
            }

            \App\Services\ConflictCandidates::record($paper, $user->id, $request->all());

            DB::commit();

            // Recalculate and sync the total due amount on the profile
            \App\Services\PricingService::updateProfileTotalDue($profile->fresh());

            // Send submission confirmation email
            try {
                Mail::to($user->email)->queue(new AbstractSubmitted($paper));
            } catch (\Exception $e) {
                Log::error('Abstract submission email failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'paper_id' => $paper->id,
                ]);
            }

            return redirect()->route('papers.index')->with('message', 'Abstract submitted successfully. Submission ID: ' . $submissionId);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Paper Submission Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error submitting abstract. Please try again or contact support. Details: ' . $e->getMessage());
        }
    }

    /**
     * Which fee tiers each country may pick, for the browser-side filter. Countries
     * that are plain "international" are left out and treated as the default, so the
     * map stays a handful of entries instead of 260.
     */
    private function countryCategoryMap($countries)
    {
        return $countries->mapWithKeys(function ($country) {
            return [$country->id => \App\Services\PricingService::allowedCategoriesFor($country->name)];
        })->reject(function ($categories) {
            return $categories === ['international'];
        });
    }

    /** Feeds the live "amount payable" panel above the submit button. */
    private function priceTableForJs($prices)
    {
        return $prices->keyBy('id')->map(function ($price) {
            return [
                'name'       => $price->name,
                'category'   => $price->category,
                'currency'   => $price->currency,
                'early_bird' => (float) $price->early_bird_price,
                'regular'    => (float) $price->regular_price,
            ];
        });
    }

    public function edit(Paper $paper)
    {
        $user = Auth::user();

        $isSubmissionOpen = \App\Services\SubmissionRules::abstractWindowIsOpen();

        // Authorization check: Authors can only edit their own pending paper while abstract submission is open
        if ($user->roles->contains('id', 3) && !$user->roles->contains('id', 1)) {
            if ($paper->user_id !== $user->id) {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - You can only edit your own paper.');
            }
            if ($paper->status === 'approved') {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - Approved paper cannot be edited.');
            }
            if ($paper->status !== 'pending' || !$isSubmissionOpen) {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - Paper is not editable.');
            }
        } else {
            abort_if(Gate::denies('paper_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $tracks = Track::with('subTracks')->get();
        $countries = Country::where('is_active', 1)->orderBy('name', 'asc')->get();
        $paper->load(['authors', 'conflicts']);
        $prices = \App\Models\Price::orderBy('id')->get();
        $countryCategories = $this->countryCategoryMap($countries);
        $priceTable = $this->priceTableForJs($prices);
        $currentStage = \App\Services\PricingService::currentStage();
        $conflictCandidates = \App\Services\ConflictCandidates::byTrack();

        $existingConflictUserIds = $paper->conflicts->whereNotNull('conflicted_user_id')->pluck('conflicted_user_id')->all();
        $existingConflictNote = $paper->conflicts->firstWhere('note', '!=', null)?->note;

        return view('admin.papers.edit', compact(
            'paper', 'tracks', 'countries', 'prices', 'countryCategories',
            'priceTable', 'currentStage', 'conflictCandidates',
            'existingConflictUserIds', 'existingConflictNote'
        ));
    }

    public function update(Request $request, Paper $paper)
    {
        $user = Auth::user();

        $isSubmissionOpen = \App\Services\SubmissionRules::abstractWindowIsOpen();

        // Authorization check: Authors can only edit their own pending paper while abstract submission is open
        if ($user->roles->contains('id', 3) && !$user->roles->contains('id', 1)) {
            if ($paper->user_id !== $user->id) {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - You can only edit your own paper.');
            }
            if ($paper->status === 'approved') {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - Approved paper cannot be edited.');
            }
            if ($paper->status !== 'pending' || !$isSubmissionOpen) {
                abort(Response::HTTP_FORBIDDEN, '403 Forbidden - Paper is not editable.');
            }
        } else {
            abort_if(Gate::denies('paper_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $noPhpTags = 'regex:/^((?!(<\?php|<\?|\?>)).)*$/is';

        $rules = [
            'paper_title' => ['required', 'string', 'max:255', $noPhpTags],
            'abstract_text' => \App\Services\SubmissionRules::abstractRules([$noPhpTags]),
            'keywords' => \App\Services\SubmissionRules::keywordRules([$noPhpTags]),
            'track_id' => 'required|exists:tracks,id',
            'sub_track_id' => 'required|exists:sub_tracks,id',
            'is_corresponding_author' => 'required|boolean',
            'corresponding_author_index' => 'nullable|integer',
            'presenting_author_index' => 'nullable|integer',
            'co_authors' => 'nullable|array',
            'co_authors.*.id' => 'nullable|integer|exists:paper_authors,id',
            'co_authors.*.name' => 'required|string|max:255',
            'co_authors.*.email' => 'required|email|max:255',
            'co_authors.*.designation' => 'required|string|max:255',
            'co_authors.*.department' => 'required|string|max:255',
            'co_authors.*.institution' => 'required|string|max:255',
            'co_authors.*.country_id' => 'required|exists:countries,id',
            'co_authors.*.is_student' => 'nullable|in:0,1',
        ];

        // The category rule needs that row's country, so it cannot use a wildcard.
        foreach ((array) $request->input('co_authors', []) as $index => $author) {
            $rules["co_authors.$index.price_id"] = ['required', 'exists:prices,id',
                new \App\Rules\DelegateCategoryMatchesCountry($author['country_id'] ?? null)];
        }

        // Conflicts of interest declared with the abstract, all optional.
        $rules = array_merge($rules, \App\Services\ConflictCandidates::rules());

        $request->validate($rules, [
            'regex' => 'The :attribute contains forbidden characters (PHP tags are not allowed).',
        ]);

        try {
            DB::beginTransaction();

            $hasCoAuthors = !empty($request->co_authors) && count($request->co_authors) > 1;

            $primaryEmail = $paper->user->email;
            $profile = $paper->user->profile;
            $incomingIds = [];
            $orderOffset = 1;

            $presentingAuthorIndex = $request->presenting_author_index ?? 0;
            $correspondingAuthorIndex = $request->input('corresponding_author_index');

            // Find primary author index in form
            $primaryAuthorIndexInForm = 0;
            $primaryAuthorFromForm = null;
            if (!empty($request->co_authors)) {
                foreach ($request->co_authors as $idx => $authorRow) {
                    if (($authorRow['email'] ?? '') === $primaryEmail) {
                        $primaryAuthorIndexInForm = $idx;
                        $primaryAuthorFromForm = $authorRow;
                        break;
                    }
                }
            }

            $isCorrespondingAuthor = $request->boolean('is_corresponding_author');
            if ($correspondingAuthorIndex !== null) {
                $isCorrespondingAuthor = ((int)$correspondingAuthorIndex === (int)$primaryAuthorIndexInForm);
            } else {
                $correspondingAuthorIndex = $isCorrespondingAuthor ? $primaryAuthorIndexInForm : null;
            }

            $paper->update([
                'title' => $request->paper_title,
                'abstract' => $request->abstract_text,
                'keywords' => \App\Services\SubmissionRules::splitKeywords($request->keywords),
                'track_id' => $request->track_id,
                'sub_track_id' => $request->sub_track_id,
                'is_corresponding_author' => $isCorrespondingAuthor ? 1 : 0,
                'has_multiple_authors' => $hasCoAuthors,
            ]);

            // 1. Maintain primary author robustly (update if available, create if missing somehow)
            $primaryAuthorModel = $paper->authors()->where('email', $primaryEmail)->first();

            $primaryData = [
                'name' => $primaryAuthorFromForm['name'] ?? trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: $paper->user->name,
                'designation' => $primaryAuthorFromForm['designation'] ?? ($profile?->designation ?? 'N/A'),
                'department' => $primaryAuthorFromForm['department'] ?? ($profile?->department ?? 'N/A'),
                'institution' => $primaryAuthorFromForm['institution'] ?? ($profile?->institution ?? 'N/A'),
                'country_id' => $primaryAuthorFromForm['country_id'] ?? ($profile?->country_id ?? 1),
                'price_id' => $primaryAuthorFromForm['price_id'] ?? ($primaryAuthorModel?->price_id ?? $profile?->price_id),
                'email' => $primaryEmail,
                'author_order' => $orderOffset++,
                'is_presenting_author' => ($presentingAuthorIndex == $primaryAuthorIndexInForm) ? 1 : 0,
                'is_corresponding_author' => ($correspondingAuthorIndex !== null && (int)$correspondingAuthorIndex === (int)$primaryAuthorIndexInForm) ? 1 : 0,
                'is_student' => isset($primaryAuthorFromForm['is_student']) && $primaryAuthorFromForm['is_student'] !== '' ? (bool)$primaryAuthorFromForm['is_student'] : ($primaryAuthorModel?->is_student ?? null),
            ];

            if ($primaryAuthorModel) {
                $primaryAuthorModel->update($primaryData);
                $incomingIds[] = $primaryAuthorModel->id;
            } else {
                $newPrimary = $paper->authors()->create($primaryData);
                $incomingIds[] = $newPrimary->id;
            }

            // 2. Sync Co-authors gracefully
            if (!empty($request->co_authors)) {
                foreach ($request->co_authors as $index => $authorData) {
                    if ($authorData['email'] === $primaryEmail) {
                        continue;
                    }

                    $existingAuthor = null;
                    $authorId = $authorData['id'] ?? null;
                    if ($authorId) {
                        $existingAuthor = $paper->authors()->find($authorId);
                    }

                    $coAuthorData = [
                        'name' => $authorData['name'],
                        'email' => $authorData['email'],
                        'designation' => $authorData['designation'],
                        'department' => $authorData['department'] ?? 'N/A',
                        'institution' => $authorData['institution'],
                        'country_id' => $authorData['country_id'],
                        'price_id' => $authorData['price_id'] ?? ($existingAuthor?->price_id),
                        'author_order' => $orderOffset++,
                        'is_presenting_author' => ($presentingAuthorIndex == $index) ? 1 : 0,
                        'is_corresponding_author' => ($correspondingAuthorIndex !== null && (int)$correspondingAuthorIndex === (int)$index) ? 1 : 0,
                        'is_student' => isset($authorData['is_student']) && $authorData['is_student'] !== '' ? (bool)$authorData['is_student'] : ($existingAuthor?->is_student ?? null),
                    ];

                    if ($existingAuthor) {
                        $existingAuthor->update($coAuthorData);
                        $incomingIds[] = $existingAuthor->id;
                        continue;
                    }

                    // Create new if no match or no ID provided
                    $newCoAuthor = $paper->authors()->create($coAuthorData);
                    $incomingIds[] = $newCoAuthor->id;
                }
            }

            // 3. Delete any strictly removed authors from the database (cleanup orphans)
            $paper->authors()->whereNotIn('id', $incomingIds)->delete();

            // Ensure at least one author is marked corresponding
            if ($paper->authors()->where('is_corresponding_author', 1)->count() === 0) {
                $paper->authors()->first()?->update(['is_corresponding_author' => 1]);
            }

            // 4. Sync conflicts of interest
            $paper->conflicts()->where('declared_by_user_id', $user->id)->delete();
            \App\Services\ConflictCandidates::record($paper, $user->id, $request->all());

            DB::commit();

            // Recalculate and sync the total due amount on the profile
            if ($profile) {
                \App\Services\PricingService::updateProfileTotalDue($profile->fresh());
            }

            return redirect()->route('papers.index')->with('success', 'Paper updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Paper Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating abstract. Please try again.');
        }
    }

    public function review(Request $request, Paper $paper)
    {
        $this->authoriseAbstractReview($paper);

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string'
        ]);

        $paper->update([
            'status' => $request->status,
            'review_note' => $request->review_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        \App\Models\PaperReview::create([
            'paper_id' => $paper->id,
            'reviewed_by' => Auth::id(),
            'status' => $request->status,
            'review_note' => $request->review_note,
        ]);

        // Reload the paper with the reviewer relations
        $paper->load('reviewer');

        // Send Email Notification (queued)
        try {
            if ($request->status == 'approved') {
                Mail::to($paper->user->email)->queue(new AbstractAccepted($paper));
                $message = 'Abstract approved and notification email queued for author.';
            } else {
                Mail::to($paper->user->email)->queue(new AbstractRejected($paper));
                $message = 'Abstract rejected and notification email queued for author.';
            }
        } catch (\Exception $e) {
            Log::error("Review email sending error ({$request->status}): " . $e->getMessage());
            $message = "Status updated but email queuing failed: " . $e->getMessage();
        }

        return back()->with('message', $message);
    }

    public function approve(Paper $paper)
    {
        // Old action endpoint, can be redirected/handled
        $this->authoriseAbstractReview($paper);
        return back()->with('error', 'Please use the review modal to approve papers.');
    }

    public function reject(Paper $paper)
    {
        // Old action endpoint, can be redirected/handled
        $this->authoriseAbstractReview($paper);
        return back()->with('error', 'Please use the review modal to reject papers.');
    }

    /**
     * Approving or rejecting an abstract needs abstract_review, the same permission that
     * shows the button on the paper page, and only for papers the person can see.
     * paper_access alone had let any chair decide abstracts in every track.
     */
    private function authoriseAbstractReview(Paper $paper): void
    {
        abort_if(Gate::denies('abstract_review'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_unless(\App\Services\ChairScope::for(Auth::user())->canSee($paper), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
