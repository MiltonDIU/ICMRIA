<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SubTrack;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use App\Services\SubmissionRules;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Gives every sub-track a starting pool of reviewers drawn from the DIU faculty
 * directory, so reviewer assignment has candidates to work with.
 *
 * The requirement document names chairs but no reviewers, so they are picked by
 * subject: each track lists the departments that teach it, and the most senior
 * academics in those departments are taken. Their expertise is the sub-track's own
 * topics, which is what paper keywords are matched against.
 *
 * Two things to be aware of:
 *   - These people have not agreed to review anything. The pool is a starting point
 *     for the chairs to prune and add to, not a commitment on anyone's behalf.
 *   - Lecturers are skipped. Reviewing is normally done at Assistant Professor and
 *     above, and they are two thirds of the directory.
 *
 * Anyone already serving as a chair is left out: a chair decides on papers rather
 * than reviewing them.
 */
class ReviewerSeeder extends Seeder
{
    private const ROLE_REVIEWER = 4;

    /** How many reviewers each sub-track starts with. */
    private const PER_SUB_TRACK = 4;

    /** Seniority order; anyone below these is not considered. */
    private const DESIGNATION_RANK = [
        'Professor' => 1,
        'Associate Professor' => 2,
        'Assistant Professor' => 3,
        'Senior Lecturer' => 4,
    ];

    /** Track number => the departments that teach it, as named in the directory. */
    private array $departmentsByTrack = [
        1 => ['Computer Science and Engineering', 'Software Engineering', 'Computing and Information System',
              'Information Technology & Management', 'Multimedia & Creative Technology'],
        2 => ['Environmental Science and Disaster Management', 'Civil Engineering', 'Architecture'],
        3 => ['Business Administration', 'Accounting', 'Marketing', 'Management', 'Finance & Banking',
              'Real Estate', 'Innovation & Entrepreneurship', 'Tourism & Hospitality Management'],
        4 => ['Electrical and Electronic Engineering', 'Textile Engineering', 'Civil Engineering',
              'Architecture', 'Information and Communication Engineering', 'Robotics and Mechatronics Engineering'],
        5 => ['Law', 'English', 'Development Studies'],
        6 => ['Pharmacy', 'Public Health', 'Nutrition and Food Engineering', 'Genetic Engineering and Biotechnology'],
        7 => ['English', 'Journalism, Media and Communication', 'Physical Education & Sports Science'],
        8 => ['Agricultural Science', 'Fisheries', 'Nutrition and Food Engineering'],
    ];

    public function run(): void
    {
        if (!Role::find(self::ROLE_REVIEWER)) {
            $this->command?->warn('Reviewer role missing. Run RolesTableSeeder first.');
            return;
        }

        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get();
        if ($tracks->isEmpty()) {
            $this->command?->warn('No tracks found. Run TrackSubTrackSeeder first.');
            return;
        }

        // Chairs decide rather than review, so keep them out of the pool.
        $chairEmails = User::whereIn('id', TrackAssignment::where('role', 'chair')->pluck('user_id'))
            ->pluck('email')
            ->map(fn ($e) => Str::lower($e))
            ->all();

        $createdUsers = 0;
        $assignments = 0;
        $short = [];

        foreach ($this->departmentsByTrack as $trackNumber => $departments) {
            $track = $tracks->first(fn ($t) => Str::startsWith($t->name, "Track {$trackNumber}:"));
            if (!$track) {
                continue;
            }

            $candidates = $this->candidatesFor($departments, $chairEmails);
            $offset = 0;

            foreach ($track->subTracks as $subTrack) {
                $slice = $candidates->slice($offset, self::PER_SUB_TRACK);

                // Wrap around when a track has more sub-tracks than senior staff.
                if ($slice->count() < self::PER_SUB_TRACK) {
                    $slice = $slice->concat($candidates->take(self::PER_SUB_TRACK - $slice->count()));
                    $offset = 0;
                } else {
                    $offset += self::PER_SUB_TRACK;
                }

                if ($slice->isEmpty()) {
                    $short[] = $subTrack->name;
                    continue;
                }

                DB::transaction(function () use ($slice, $track, $subTrack, &$createdUsers, &$assignments) {
                    foreach ($slice as $person) {
                        [$user, $wasCreated] = $this->upsertReviewer($person);
                        $createdUsers += $wasCreated ? 1 : 0;

                        $row = TrackAssignment::firstOrNew([
                            'user_id' => $user->id,
                            'track_id' => $track->id,
                            'sub_track_id' => $subTrack->id,
                            'role' => 'reviewer',
                        ]);

                        if (!$row->exists) {
                            $assignments++;
                        }

                        // Leave an expertise a chair has since refined by hand.
                        if (!$row->exists || blank($row->expertise)) {
                            $row->expertise = $this->topicsFrom($subTrack->name);
                        }

                        $row->save();
                    }
                });
            }
        }

        $this->command?->info("Reviewers seeded: {$assignments} new assignment(s), {$createdUsers} new account(s).");
        $this->command?->info('Pool now holds ' . TrackAssignment::where('role', 'reviewer')->count() . ' reviewer assignments across '
            . TrackAssignment::where('role', 'reviewer')->distinct('user_id')->count('user_id') . ' people.');

        if ($short) {
            $this->command?->warn('No senior staff found for: ' . implode('; ', array_unique($short)));
        }
    }

    /**
     * Senior academics in the given departments, most senior first, each appearing
     * once. Ordered deterministically so re-running picks the same people.
     */
    private function candidatesFor(array $departments, array $excludeEmails)
    {
        return DB::table('project_fms.teachers as t')
            ->join('project_fms.users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('project_fms.departments as d', 'd.id', '=', 't.department_id')
            ->leftJoin('project_fms.designations as g', 'g.id', '=', 't.designation_id')
            ->whereNull('u.deleted_at')
            ->where('u.is_active', 1)
            ->where(fn ($q) => $q->where('t.is_archived', 0)->orWhereNull('t.is_archived'))
            ->whereIn('d.name', $departments)
            ->whereIn('g.name', array_keys(self::DESIGNATION_RANK))
            ->whereNotNull('u.email')
            ->whereNotIn(DB::raw('LOWER(u.email)'), $excludeEmails ?: [''])
            ->select('u.name', 'u.email', 'd.name as department', 'g.name as designation')
            ->orderByRaw('FIELD(g.name, ' . implode(',', array_map(fn ($d) => "'" . $d . "'", array_keys(self::DESIGNATION_RANK))) . ')')
            ->orderBy('u.name')
            ->get()
            ->unique('email')
            ->values();
    }

    /** @return array{0: User, 1: bool} the reviewer and whether the account was new */
    private function upsertReviewer(object $person): array
    {
        $user = User::where('email', $person->email)->first();

        if ($user) {
            $user->roles()->syncWithoutDetaching([self::ROLE_REVIEWER]);

            return [$user, false];
        }

        $user = User::create([
            'name' => $person->name,
            'email' => $person->email,
            // Unknown by design: reviewers set their own through the reset link.
            'password' => Hash::make(Str::random(32)),
        ]);
        $user->roles()->syncWithoutDetaching([self::ROLE_REVIEWER]);

        return [$user, true];
    }

    /**
     * Sub-track titles are headings, not keyword lists. Split them on the separators
     * they actually use so the terms are worth matching paper keywords against.
     *
     * @return array<int, string>
     */
    private function topicsFrom(string $title): array
    {
        $title = preg_replace('/^\s*Track\s*\d+\s*:\s*/i', '', $title);

        $parts = preg_split('/[,:&]+/', $title);
        $parts = array_map('trim', $parts ?: []);
        $parts = array_filter($parts, fn ($p) => mb_strlen($p) > 2);

        return SubmissionRules::splitKeywords(array_values($parts));
    }
}
