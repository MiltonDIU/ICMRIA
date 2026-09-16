<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paper;
use App\Models\PaperBid;
use App\Models\PaperReviewerAssignment;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\SubmissionRules;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paper bidding (requirement document, Phase 3): "System presents reviewers with paper
 * titles and abstracts. Reviewers mark their preference."
 *
 * A reviewer sees the papers in the tracks and sub-tracks they review for, and only
 * the title, abstract, keywords and track. Authors are never shown here, whatever the
 * blind review setting, since the document offers nothing more at this stage. The
 * reviewer's own papers are left out.
 */
class PaperBidController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('review_bid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();
        $filter = $request->string('filter')->toString();

        $papers = $this->biddablePapers($user)
            ->with(['track', 'subTrack'])
            ->orderBy('id')
            ->get();

        $bids = PaperBid::where('reviewer_id', $user->id)
            ->whereIn('paper_id', $papers->pluck('id'))
            ->pluck('preference', 'paper_id');

        $total = $papers->count();

        if ($filter === 'unmarked') {
            $papers = $papers->reject(fn ($paper) => $bids->has($paper->id))->values();
        }

        return view('admin.paper_bids.index', [
            'papers' => $papers,
            'bids' => $bids,
            'assignedIds' => PaperReviewerAssignment::where('reviewer_id', $user->id)->pluck('paper_id'),
            'preferences' => PaperBid::LABELS,
            'biddingOpen' => SubmissionRules::biddingIsOpen(),
            'inPool' => TrackAssignment::where('user_id', $user->id)->where('role', 'reviewer')->exists(),
            'filter' => $filter,
            'marked' => $bids->count(),
            'total' => $total,
        ]);
    }

    /** Saves every paper marked on the page in one go; unmarked papers are left as they were. */
    public function store(Request $request)
    {
        abort_if(Gate::denies('review_bid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (!SubmissionRules::biddingIsOpen()) {
            return back()->with('error', 'Bidding is closed, so preferences can no longer be changed.');
        }

        $data = $request->validate([
            'bids' => 'required|array',
            'bids.*' => ['required', Rule::in(array_keys(PaperBid::LABELS))],
        ], [
            'bids.required' => 'Mark at least one paper before saving.',
            'bids.*.in' => 'Choose Want to Review, Can Review, Neutral or Conflict for each paper.',
        ]);

        $user = auth()->user();
        $allowed = $this->biddablePapers($user)->pluck('id')->all();
        $changed = 0;

        DB::transaction(function () use ($data, $user, $allowed, &$changed) {
            foreach ($data['bids'] as $paperId => $preference) {
                // A paper id edited into the form must not become a bid outside the pool.
                abort_unless(in_array((int) $paperId, $allowed, true), Response::HTTP_FORBIDDEN,
                    '403 Forbidden - that paper is outside your review pool.');

                $bid = PaperBid::updateOrCreate(
                    ['paper_id' => (int) $paperId, 'reviewer_id' => $user->id],
                    ['preference' => $preference]
                );

                $changed += ($bid->wasRecentlyCreated || $bid->wasChanged()) ? 1 : 0;
            }
        });

        return back()->with('success', $changed
            ? $changed . ' preference' . ($changed === 1 ? '' : 's') . ' saved.'
            : 'Nothing had changed.');
    }

    /**
     * Papers in the reviewer's pool: a whole track they review for, or one of their
     * sub-tracks. Rejected abstracts and the reviewer's own papers are left out.
     */
    private function biddablePapers(User $user)
    {
        return \App\Services\BiddablePapers::query($user);
    }
}
