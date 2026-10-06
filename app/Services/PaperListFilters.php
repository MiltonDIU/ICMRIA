<?php

namespace App\Services;

use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The search, track filter, sort and page size shared by the paper lists that chairs
 * and the TPC Chair work through: Decisions, Revised Manuscripts, Camera-Ready Check and
 * Final Approval. The toolbar is resources/views/admin/partials/list-toolbar.blade.php.
 *
 * Track filter values: "t<id>" for a whole track, "s<id>" for one sub-track.
 */
class PaperListFilters
{
    public const PER_PAGE = [25, 50, 100];

    /** Registration fee filter, offered where the toolbar is given showPayment. */
    public const PAYMENT = ['' => 'All', 'paid' => 'Paid', 'unpaid' => 'Unpaid'];

    /** @param array<string, string> $sorts key => label; the first is the default */
    private function __construct(
        public readonly string $track,
        public readonly string $search,
        public readonly string $sort,
        public readonly int $perPage,
        public readonly array $sorts,
        public readonly string $payment,
    ) {
    }

    public static function from(Request $request, array $sorts): self
    {
        $sort = $request->string('sort')->toString();
        $perPage = $request->integer('per_page');
        $payment = $request->string('payment')->toString();

        return new self(
            preg_match('/^[ts]\d+$/', $request->string('track')->toString()) ? $request->string('track')->toString() : '',
            trim($request->string('q')->toString()),
            array_key_exists($sort, $sorts) ? $sort : array_key_first($sorts),
            in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0],
            $sorts,
            array_key_exists($payment, self::PAYMENT) ? $payment : '',
        );
    }

    public function isDefaultSort(): bool
    {
        return $this->sort === array_key_first($this->sorts);
    }

    public function isFiltered(): bool
    {
        return $this->track !== '' || $this->search !== '' || $this->payment !== '' || !$this->isDefaultSort();
    }

    /** The parameters to carry over when switching tabs. */
    public function query(): array
    {
        return array_filter([
            'track' => $this->track,
            'q' => $this->search,
            'payment' => $this->payment,
            'sort' => $this->isDefaultSort() ? null : $this->sort,
            'per_page' => $this->perPage === self::PER_PAGE[0] ? null : $this->perPage,
        ]);
    }

    /** Narrows a paper query (papers table, possibly joined) to the chosen track, fee status and search. */
    public function apply($query, string $papers = 'papers')
    {
        if ($this->payment === 'paid') {
            $query->where($papers . '.payment_status', '1');
        } elseif ($this->payment === 'unpaid') {
            $query->where(fn ($q) => $q->whereNull($papers . '.payment_status')->orWhere($papers . '.payment_status', '!=', '1'));
        }

        if (preg_match('/^t(\d+)$/', $this->track, $m)) {
            $query->where($papers . '.track_id', (int) $m[1]);
        } elseif (preg_match('/^s(\d+)$/', $this->track, $m)) {
            $query->where($papers . '.sub_track_id', (int) $m[1]);
        }

        if ($this->search !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $this->search) . '%';
            $query->where(fn ($q) => $q->where($papers . '.submission_id', 'like', $like)
                ->orWhere($papers . '.title', 'like', $like)
                ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', $like)->orWhere('email', 'like', $like)));
        }

        return $query;
    }

    /** The same, for a query on a model that belongs to a paper (e.g. decisions). */
    public function applyThroughPaper($query)
    {
        if ($this->track === '' && $this->search === '' && $this->payment === '') {
            return $query;
        }

        return $query->whereHas('paper', fn ($q) => $this->apply($q));
    }

    /**
     * Leaves out papers this person wrote or where a conflict with them was declared (and,
     * unless Settings allows it, papers they review), as ChairScope::conflictWith decides
     * for one paper, but in SQL so long lists stay fast.
     */
    public static function excludeConflicts($query, User $user)
    {
        $email = Str::lower((string) $user->email);

        $query->whereDoesntHave('authors', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))
            ->whereDoesntHave('user', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))
            ->whereDoesntHave('conflicts', fn ($q) => $q->where('conflicted_user_id', $user->id));

        if (!SubmissionRules::reviewerChairMayDecide()) {
            $query->whereDoesntHave('reviewerAssignments', fn ($q) => $q->where('reviewer_id', $user->id)->where('status', '!=', 'declined'));
        }

        return $query;
    }

    /** The tracks to offer in the filter: every track, or the ones this person chairs. */
    public static function tracksFor(User $user, bool $allTracks): Collection
    {
        $scope = ChairScope::for($user);

        return Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])
            ->when(!$allTracks && !$scope->seesEverything(), fn ($q) => $q->whereIn('id', $scope->trackIds() ?: [0]))
            ->orderBy('id')
            ->get();
    }

    /** Pages a list built in PHP (where a tab depends on computed values). */
    public function paginateCollection(Collection $items, Request $request): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page', 1));

        return (new LengthAwarePaginator(
            $items->forPage($page, $this->perPage)->values(),
            $items->count(),
            $this->perPage,
            $page,
            ['path' => $request->url()]
        ))->withQueryString();
    }
}
