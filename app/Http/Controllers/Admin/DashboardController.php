<?php

namespace App\Http\Controllers\Admin;

use App\Models\Amenity;
use App\Models\Paper;
use App\Models\Profile;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Gate;
use DB;

class DashboardController extends Controller
{
    public function index()
    {

        abort_if(Gate::denies('admin_dashboard'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = Auth::user();

        // An author or participant gets their own dashboard. Everything below this line
        // is organiser analytics &mdash; country breakdowns, daily trends, currency totals
        // across the whole conference. None of it is theirs to see, and running those
        // aggregates to render a page that hides them is waste on every login.
        if ($user->roles->contains('id', 3)) {
            return $this->authorDashboard($user);
        }

        // General Registration Stats
        $total = Profile::count();
        $totalParticipants = Profile::where('is_author', false)->count();
        $totalAuthors = Profile::where('is_author', true)->count();
        $totalSubmitters = $totalAuthors;
        $totalActualAuthors = \App\Models\PaperAuthor::whereHas('paper')->count();
        $paidParticipants = Profile::where('is_author', false)->where('payment_status', '1')->count();
        $paid = Profile::where('payment_status', '1')->count();
        $unpaid = $total - $paid;

        $profiles = [
            ['country' => 'Paid', 'litres' => $paid],
            ['country' => 'Unpaid', 'litres' => $unpaid],
        ];

        // Payment Statistics Grouped by Currency and User Type
        $currencyStats = DB::table('profiles')
            ->selectRaw('currency,
                         COUNT(*) as total_users,
                         SUM(CASE WHEN payment_status = "1" THEN pay_amount ELSE 0 END) as paid_amount,
                         SUM(CASE WHEN payment_status = "0" THEN pay_amount ELSE 0 END) as unpaid_amount,
                         SUM(CASE WHEN is_author = 1 AND payment_status = "1" THEN pay_amount ELSE 0 END) as author_paid_amt,
                         SUM(CASE WHEN is_author = 1 AND payment_status = "0" THEN pay_amount ELSE 0 END) as author_unpaid_amt,
                         SUM(CASE WHEN is_author = 0 AND payment_status = "1" THEN pay_amount ELSE 0 END) as participant_paid_amt,
                         SUM(CASE WHEN is_author = 0 AND payment_status = "0" THEN pay_amount ELSE 0 END) as participant_unpaid_amt')
            ->whereNotNull('currency')
            ->groupBy('currency')
            ->get();

        // Top Submission Tracks with Status Breakdown
        $topTracks = DB::table('tracks')
            ->leftJoin('papers', 'tracks.id', '=', 'papers.track_id')
            ->selectRaw('tracks.name,
                         COUNT(papers.id) as submission_count,
                         SUM(CASE WHEN papers.status = "pending" THEN 1 ELSE 0 END) as pending_count,
                         SUM(CASE WHEN papers.status = "approved" THEN 1 ELSE 0 END) as approved_count,
                         SUM(CASE WHEN papers.status = "rejected" THEN 1 ELSE 0 END) as rejected_count')
            ->groupBy('tracks.id', 'tracks.name')
            ->orderBy('submission_count', 'DESC')
            ->limit(10)
            ->get();

        // Abstract Statistics
        $totalPapers = Paper::count();
        $pendingPapers = Paper::where('status', 'pending')->count();
        $approvedPapers = Paper::where('status', 'approved')->count();
        $rejectedPapers = Paper::where('status', 'rejected')->count();

        $paperStats = [
            ['category' => 'Pending', 'litres' => $pendingPapers],
            ['category' => 'Approved', 'litres' => $approvedPapers],
            ['category' => 'Rejected', 'litres' => $rejectedPapers],
        ];

        // These two were computed twice over, running both queries a second time on every
        // organiser page load.
        $paidPapers = Paper::where('payment_status', '1')->count();
        $unpaidPaperCount = Paper::where('status', 'approved')->where('payment_status', '0')->count();

        $paperPaymentStats = [
            ['category' => 'Paid', 'litres' => $paidPapers],
            ['category' => 'Unpaid', 'litres' => $unpaidPaperCount],
        ];

        // The delegates-only branch that stood here (their unpaid papers, and issuing a
        // registration ID once a payment cleared) moved to authorDashboard(), which now
        // returns before this point for role 3.

        // 1. Country-wise Registration & Submission Analytics
        $countryStats = DB::table('countries')
            ->join('profiles', 'profiles.country_id', '=', 'countries.id')
            ->selectRaw('countries.id as country_id,
                         countries.name as country_name,
                         COUNT(profiles.id) as total_registrations,
                         SUM(CASE WHEN profiles.is_author = 1 THEN 1 ELSE 0 END) as total_authors,
                         SUM(CASE WHEN profiles.is_author = 1 AND profiles.payment_status = "1" THEN 1 ELSE 0 END) as paid_authors,
                         SUM(CASE WHEN profiles.is_author = 0 THEN 1 ELSE 0 END) as total_participants,
                         SUM(CASE WHEN profiles.is_author = 0 AND profiles.payment_status = "1" THEN 1 ELSE 0 END) as paid_participants,
                         SUM(CASE WHEN profiles.payment_status = "1" THEN 1 ELSE 0 END) as total_paid')
            ->groupBy('countries.id', 'countries.name')
            ->orderBy('total_registrations', 'desc')
            ->get();

        $paperCountryStats = DB::table('papers')
            ->join('profiles', 'profiles.user_id', '=', 'papers.user_id')
            ->selectRaw('profiles.country_id, COUNT(papers.id) as total_papers')
            ->groupBy('profiles.country_id')
            ->pluck('total_papers', 'profiles.country_id');

        foreach ($countryStats as $stat) {
            $stat->total_papers = $paperCountryStats[$stat->country_id] ?? 0;
            $stat->payment_percentage = $stat->total_registrations > 0 
                ? round(($stat->total_paid / $stat->total_registrations) * 100, 1) 
                : 0;
        }

        // 2. Daily Trends (Last 30 Days)
        $dailyRegistrations = DB::table('profiles')
            ->selectRaw('DATE(created_at) as reg_date,
                         SUM(CASE WHEN is_author = 1 THEN 1 ELSE 0 END) as author_count,
                         SUM(CASE WHEN is_author = 0 THEN 1 ELSE 0 END) as participant_count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('reg_date')
            ->orderBy('reg_date', 'asc')
            ->get();

        $dailyPapers = DB::table('papers')
            ->selectRaw('DATE(created_at) as submit_date, COUNT(*) as paper_count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('submit_date')
            ->orderBy('submit_date', 'asc')
            ->pluck('paper_count', 'submit_date');

        $dailyTrends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $reg = $dailyRegistrations->firstWhere('reg_date', $date);
            
            $dailyTrends[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'authors' => $reg ? (int)$reg->author_count : 0,
                'participants' => $reg ? (int)$reg->participant_count : 0,
                'papers' => (int)($dailyPapers[$date] ?? 0),
            ];
        }

        // Only what admin/home.blade.php actually reads. It used to be handed eleven more
        // variables, every one of them consumed solely by the delegates-only block that
        // has moved out, or by markup that has been commented out for years.
        return view('admin.home', compact(
            'profiles', 'total', 'totalParticipants', 'totalSubmitters', 'totalActualAuthors',
            'paidParticipants', 'topTracks', 'currencyStats',
            'totalPapers', 'pendingPapers',
            'paperStats', 'paidPapers', 'paperPaymentStats',
            'countryStats', 'dailyTrends'
        ));
    }

    /**
     * What an author or participant needs the moment they log in.
     *
     * The requirement document puts the same three things in front of authors on the
     * public site &mdash; important dates, the fee table, and the author guidelines. A
     * delegate who has logged in should not have to go back out to the marketing pages to
     * find them, and once they have logged in we can say which of it applies to *them*:
     * which deadline is next, which fee tier they are on, what is still outstanding.
     *
     * Every figure here is read from settings and the prices table through the same
     * services the forms and validators use, so the dashboard cannot advertise a rule the
     * portal does not enforce.
     */
    private function authorDashboard($user)
    {
        $settings = Setting::pluck('value', 'key');
        $profile = $user->profile;

        // A cleared payment earns a registration ID. Kept from the old dashboard: this is
        // the screen delegates land on after paying, so it is where the ID appears.
        if ($profile && $profile->payment_status == 1 && $profile->registration_id == null) {
            $profile->registration_id = \App\Services\IdGeneratorService::generateRegistrationId();
            $profile->save();
            $user = $user->fresh();
            $profile = $user->profile;
        }

        $papers = Paper::where('user_id', $user->id)
            ->with(['track', 'subTrack', 'authors'])
            ->orderByDesc('id')
            ->get();

        $unpaidPapers = $papers
            ->filter(fn (Paper $paper) => $paper->status === 'approved' && $paper->payment_status != 1)
            ->values();

        $paymentLastDate = $this->asDate($settings['payment_last_date'] ?? null);
        $isPaymentOpen = !$paymentLastDate || Carbon::now()->lte($paymentLastDate);

        return view('admin.author_dashboard', [
            'user' => $user,
            'profile' => $profile,
            'settings' => $settings,
            'papers' => $papers,
            'unpaidPapers' => $unpaidPapers,
            'isPaymentOpen' => $isPaymentOpen,
            'todo' => \App\Services\DelegateChecklist::for($profile, $unpaidPapers, $isPaymentOpen),
            'milestones' => $this->milestones($settings),
            'prices' => \App\Models\Price::orderBy('id')->get(),
            'currentStage' => \App\Services\PricingService::currentStage(),
            'earlyBirdEndsAt' => $this->asDate($settings['early_registration_last_date'] ?? null),
            'paymentBlockReason' => \App\Services\ProceedingsRules::paymentBlockReason(),
            'abstractWindowOpen' => \App\Services\SubmissionRules::abstractWindowIsOpen(),
            'submissionsLeft' => max(
                // The misspelled key is the legacy one; papers/index.blade.php falls back the same way.
                (int) ($settings['maximum_abstract_submission'] ?? $settings['maximum_abastract_submission'] ?? 1) - $papers->count(),
                0
            ),
            // Carried over from the old shared dashboard, where both cards sat inside the
            // delegates-only block. They are conference information a delegate wants, so
            // they move here rather than being dropped with the rest of that block.
            'amenities' => Amenity::orderBy('id', 'desc')->get(),
            'allSchedules' => Schedule::with('speaker')
                ->where('is_active', '1')
                ->orderBy('day_number', 'asc')
                ->orderBy('start_time', 'asc')
                ->get()
                ->groupBy('day_number'),
        ]);
    }

    /**
     * The conference timeline as a delegate experiences it: what each date is, whether it
     * has passed, and how long is left. Dates missing from settings are dropped rather
     * than shown as blanks &mdash; a milestone nobody has scheduled yet is not news.
     *
     * @return array<int, array<string, mixed>>
     */
    private function milestones($settings): array
    {
        $now = Carbon::now();

        $rows = [
            ['label' => 'Abstract submission deadline', 'icon' => 'fa-file-alt',
             'to' => $this->asDate($settings['abstract_submission_deadline'] ?? null)],
            ['label' => 'Full manuscript window', 'icon' => 'fa-file-upload',
             'from' => $this->asDate($settings['manuscript_submission_start'] ?? null),
             'to' => $this->asDate($settings['manuscript_submission_end'] ?? null)],
            ['label' => 'Early bird registration ends', 'icon' => 'fa-tags',
             'to' => $this->asDate($settings['early_registration_last_date'] ?? null)],
            ['label' => 'Camera-ready & copyright form', 'icon' => 'fa-stamp',
             'to' => $this->asDate($settings['camera_ready_deadline'] ?? null)],
            ['label' => 'Registration deadline', 'icon' => 'fa-credit-card',
             'to' => $this->asDate($settings['registration_close_date'] ?? ($settings['payment_last_date'] ?? null))],
            ['label' => 'Conference', 'icon' => 'fa-calendar-check',
             'from' => $this->asDate($settings['event_date'] ?? null),
             'to' => $this->asDate($settings['event_end_date'] ?? null)],
        ];

        return collect($rows)
            ->filter(fn ($row) => ($row['to'] ?? null) || ($row['from'] ?? null))
            ->map(function ($row) use ($now) {
                $from = $row['from'] ?? null;
                $to = $row['to'] ?? $from;

                if ($to->lt($now)) {
                    $state = 'passed';
                    $note = 'Closed';
                } elseif ($from && $from->gt($now)) {
                    $state = 'upcoming';
                    $note = 'Opens in ' . $now->diffInDays($from) . ' days';
                } else {
                    $state = 'open';
                    $days = $now->diffInDays($to);
                    $note = $days === 0 ? 'Today' : $days . ' days left';
                }

                return $row + ['from' => $from, 'to' => $to, 'state' => $state, 'note' => $note];
            })
            ->sortBy(fn ($row) => $row['to']->timestamp)
            ->values()
            ->all();
    }

    private function asDate($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function tracksReport()
    {
        abort_if(Gate::denies('track_report'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Fetch tracks and sub-tracks submission statistics
        $reportData = DB::table('tracks')
            ->join('sub_tracks', 'sub_tracks.track_id', '=', 'tracks.id')
            ->leftJoin('papers', function($join) {
                $join->on('papers.track_id', '=', 'tracks.id')
                     ->on('papers.sub_track_id', '=', 'sub_tracks.id')
                     ->whereNull('papers.deleted_at'); // Filter out soft-deleted papers
            })
            ->selectRaw('
                tracks.id as track_id,
                tracks.name as track_name,
                sub_tracks.id as sub_track_id,
                sub_tracks.name as sub_track_name,
                COUNT(papers.id) as total_submissions,
                SUM(CASE WHEN papers.status = "approved" AND papers.payment_status = "1" THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN papers.status = "approved" AND (papers.payment_status = "0" OR papers.payment_status IS NULL) THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN papers.status = "pending" THEN 1 ELSE 0 END) as pending_count,
                (SELECT COUNT(*) 
                 FROM paper_authors 
                 JOIN papers ON papers.id = paper_authors.paper_id 
                 WHERE papers.track_id = tracks.id 
                   AND papers.sub_track_id = sub_tracks.id 
                   AND papers.deleted_at IS NULL) as total_authors,
                (SELECT COUNT(DISTINCT papers.user_id) 
                 FROM papers 
                 WHERE papers.track_id = tracks.id 
                   AND papers.sub_track_id = sub_tracks.id 
                   AND papers.deleted_at IS NULL) as unique_submitters
            ')
            ->groupBy('tracks.id', 'tracks.name', 'sub_tracks.id', 'sub_tracks.name')
            ->orderBy('tracks.name', 'asc')
            ->orderBy('sub_tracks.name', 'asc')
            ->get();

        // Get all unique currencies dynamically from papers table
        $currencies = DB::table('papers')
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('currency')
            ->toArray();

        // Fallback default list of currencies if empty
        if (empty($currencies)) {
            $currencies = ['BDT', 'USD', 'EUR', 'INR'];
        }
        sort($currencies);

        // Query payment amounts grouped by track, sub-track, and currency
        $paymentSums = DB::table('papers')
            ->selectRaw('track_id, sub_track_id, currency, SUM(pay_amount) as total_amount')
            ->where('payment_status', '1')
            ->whereNull('deleted_at')
            ->groupBy('track_id', 'sub_track_id', 'currency')
            ->get();

        foreach ($reportData as $row) {
            $amounts = $paymentSums->where('track_id', $row->track_id)
                                   ->where('sub_track_id', $row->sub_track_id);
            
            $formattedAmounts = [];
            foreach ($amounts as $amt) {
                if ($amt->total_amount > 0 && !empty($amt->currency)) {
                    $formattedAmounts[] = number_format($amt->total_amount, 0) . ' ' . $amt->currency;
                }
            }
            $row->paid_amount = !empty($formattedAmounts) ? implode(', ', $formattedAmounts) : '0';

            $rowCurrencies = [];
            foreach ($currencies as $currency) {
                $amt = $amounts->firstWhere('currency', $currency);
                $rowCurrencies[$currency] = $amt ? $amt->total_amount : 0;
            }
            $row->currency_amounts = $rowCurrencies;
        }

        // Get track list for filters with paper counts
        $tracks = \App\Models\Track::withCount('papers')->orderBy('name', 'asc')->get();

        return view('admin.reports.tracks', compact('reportData', 'tracks', 'currencies'));
    }
}
