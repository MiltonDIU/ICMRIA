<?php

namespace App\Services;

use App\Models\Paper;
use App\Models\PaperBid;
use App\Models\TrackAssignment;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * The papers a reviewer may bid on, and whether they still owe any preferences.
 */
class BiddablePapers
{
    /**
     * Papers in the reviewer's pool: a whole track they review for, or one of their
     * sub-tracks. Rejected abstracts and the reviewer's own papers are left out.
     */
    public static function query(User $user)
    {
        $pool = TrackAssignment::where('user_id', $user->id)->where('role', 'reviewer')->get();
        $wholeTrackIds = $pool->whereNull('sub_track_id')->pluck('track_id')->unique()->values()->all();
        $subTrackIds = $pool->whereNotNull('sub_track_id')->pluck('sub_track_id')->unique()->values()->all();

        return Paper::query()
            ->underConsideration()
            ->where(function ($q) use ($wholeTrackIds, $subTrackIds) {
                $q->whereIn('track_id', $wholeTrackIds ?: [0])
                  ->orWhereIn('sub_track_id', $subTrackIds ?: [0]);
            })
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('authors', fn ($q) => $q->whereRaw('LOWER(email) = ?', [Str::lower($user->email)]));
    }

    /**
     * What the bidding banner says: how many papers still wait for this reviewer's
     * preference. Null when bidding is closed or nothing is waiting.
     *
     * @return array{unmarked: int, total: int}|null
     */
    public static function notice(User $user): ?array
    {
        if (!SubmissionRules::biddingIsOpen()) {
            return null;
        }

        $paperIds = self::query($user)->pluck('id');

        if ($paperIds->isEmpty()) {
            return null;
        }

        $marked = PaperBid::where('reviewer_id', $user->id)->whereIn('paper_id', $paperIds)->count();
        $unmarked = $paperIds->count() - $marked;

        return $unmarked > 0 ? ['unmarked' => $unmarked, 'total' => $paperIds->count()] : null;
    }
}
