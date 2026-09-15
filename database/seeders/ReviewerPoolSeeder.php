<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\SubmissionRules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A placeholder reviewer pool on icmria.com, so reviewer assignment (requirement
 * document, Phase 3) can be exercised without the DIU faculty directory.
 *
 * ReviewerSeeder draws real people from project_fms.teachers; where that table is
 * empty it adds nobody, and every paper then shows an empty pool. This fills the gap:
 *
 *   - four reviewers on every sub-track, "Reviewer 1.2 A" to "Reviewer 1.2 D";
 *   - one reviewer per track who covers the whole track, "Reviewer 1 All", so the
 *     track-wide half of the pool is tested too.
 *
 * Expertise is the sub-track's own topics. Reviewer A carries all of them and B to D
 * each drop one, so candidates score differently and the ranking on the assignment
 * screen means something.
 *
 * Every account signs in with the password "password", which is why this refuses to
 * run in production. All of them sit on reviewer.*@icmria.com and can be removed with
 * one query.
 */
class ReviewerPoolSeeder extends Seeder
{
    private const DOMAIN = 'icmria.com';
    private const ROLE_REVIEWER = 4;
    private const LETTERS = ['A', 'B', 'C', 'D'];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('ReviewerPoolSeeder skipped: never seed placeholder reviewers in production.');
            return;
        }

        if (!Role::find(self::ROLE_REVIEWER)) {
            $this->command?->warn('Reviewer role missing. Run RolesTableSeeder first.');
            return;
        }

        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get();
        if ($tracks->isEmpty()) {
            $this->command?->warn('No tracks found. Run TrackSubTrackSeeder first.');
            return;
        }

        // Hashed once: bcrypt on every one of 140 accounts would take several seconds.
        $password = Hash::make('password');
        $createdUsers = 0;
        $assignments = 0;

        foreach ($tracks as $trackIndex => $track) {
            $trackNumber = $trackIndex + 1;

            DB::transaction(function () use ($track, $trackNumber, $password, &$createdUsers, &$assignments) {
                foreach ($track->subTracks as $subIndex => $subTrack) {
                    $position = $trackNumber . '.' . ($subIndex + 1);
                    $topics = SubmissionRules::topicsFrom($subTrack->name);

                    foreach (self::LETTERS as $i => $letter) {
                        [$user, $wasCreated] = $this->upsertReviewer(
                            "Reviewer {$position} {$letter}",
                            'reviewer.' . $position . '.' . Str::lower($letter),
                            $password
                        );
                        $createdUsers += $wasCreated ? 1 : 0;
                        $assignments += $this->place($user, $track->id, $subTrack->id, $this->expertiseFor($topics, $i)) ? 1 : 0;
                    }
                }

                [$user, $wasCreated] = $this->upsertReviewer(
                    "Reviewer {$trackNumber} All",
                    "reviewer.{$trackNumber}.all",
                    $password
                );
                $createdUsers += $wasCreated ? 1 : 0;
                $assignments += $this->place($user, $track->id, null, SubmissionRules::topicsFrom($track->name)) ? 1 : 0;
            });
        }

        $pool = TrackAssignment::where('role', 'reviewer')
            ->whereHas('user', fn ($q) => $q->where('email', 'like', 'reviewer.%@' . self::DOMAIN));

        $this->command?->info("Placeholder reviewers: {$createdUsers} new account(s), {$assignments} new assignment(s).");
        $this->command?->info('They hold ' . (clone $pool)->count() . ' assignments across '
            . $pool->distinct('user_id')->count('user_id') . ' people, all signing in with "password".');
    }

    /**
     * Reviewer A covers every topic; the others each leave one out, in turn. A
     * sub-track with two topics or fewer gives everyone all of them, since dropping
     * one would leave a reviewer matching almost nothing.
     *
     * @param array<int, string> $topics
     * @return array<int, string>
     */
    private function expertiseFor(array $topics, int $letterIndex): array
    {
        if ($letterIndex === 0 || count($topics) <= 2) {
            return $topics;
        }

        $drop = ($letterIndex - 1) % count($topics);

        return array_values(array_filter($topics, fn ($t, $k) => $k !== $drop, ARRAY_FILTER_USE_BOTH));
    }

    /** @return array{0: User, 1: bool} the reviewer and whether the account was new */
    private function upsertReviewer(string $name, string $mailbox, string $password): array
    {
        $email = $mailbox . '@' . self::DOMAIN;
        $user = User::where('email', $email)->first();
        $wasCreated = false;

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
            ]);
            $wasCreated = true;
        }

        $user->roles()->syncWithoutDetaching([self::ROLE_REVIEWER]);

        return [$user, $wasCreated];
    }

    /** @return bool whether the assignment is new */
    private function place(User $user, int $trackId, ?int $subTrackId, array $expertise): bool
    {
        $row = TrackAssignment::firstOrNew([
            'user_id' => $user->id,
            'track_id' => $trackId,
            'sub_track_id' => $subTrackId,
            'role' => 'reviewer',
        ]);
        $isNew = !$row->exists;

        // Leave an expertise a chair has since refined by hand.
        if ($isNew || blank($row->expertise)) {
            $row->expertise = $expertise;
        }

        $row->save();

        return $isNew;
    }
}
