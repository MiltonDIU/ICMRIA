<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Track;
use App\Models\TrackAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates login accounts for the track and sub-track chairs named in the
 * requirement document (section 6.4, "Track Chairs & Track Co-Chairs") and puts
 * each one in charge of the right scope.
 *
 * Each assignment carries its own focus area — for a sub-track chair that is the
 * sub-track's subject matter, which is what the automatic reviewer assignment
 * matches paper keywords against later on. It sits on the assignment rather than on
 * the person because someone can chair two posts in unrelated fields.
 *
 * No profiles are created. A profile describes a delegate who registers and pays;
 * chairs are users with track assignments and nothing more.
 *
 * Addresses come from the DIU faculty directory where a chair could be matched to it
 * with confidence. Anyone who could not is given a conference address on icmria.com
 * derived from their name; those mailboxes have to exist, or be replaced under
 * Admin > Users, before that chair can sign in. The seeder lists them on every run.
 *
 * Chairs are matched to tracks by position, not by name, so a reworded track title
 * does not silently leave someone unassigned.
 */
class TrackChairSeeder extends Seeder
{
    /** Conference domain used when the faculty directory gave nothing usable. */
    private const PLACEHOLDER_DOMAIN = 'icmria.com';

    private const ROLE_TRACK_CHAIR = 5;
    private const ROLE_SUB_TRACK_CHAIR = 6;

    /**
     * Real addresses taken from the DIU faculty directory (project_fms) and checked
     * one by one against the department the requirement document gives for each
     * chair. Only matches whose department or address corroborates the document are
     * listed; anyone missing from this map keeps a placeholder address on purpose.
     *
     * Left out deliberately, and why:
     *   Prof. Dr. Md. Saidur Rahman   the only "Saidur Rahman" in the directory sits
     *                                 in no department, is inactive and archived,
     *                                 while the document places him in ESDM
     *   Dr. Nusrat Jahan              likewise unassigned, inactive and archived;
     *                                 the document places her at the head of ITM
     *   Dr. Md. Rashedul Islam        the directory match works in Journalism, the
     *                                 document places him in Agricultural Science
     *   Dr. Kazi A. S. M. Nurul Huda  no one of that name in the directory
     *   Ms. Bilkis Khanam             no one of that name in the directory
     *   Dr. Md. Ahad Ali              no one of that name in the directory
     *   Prof. Dr. Bellal Hossain      resolves to the same person as
     *                                 Prof. Dr. Md. Bellal Hossain; see the note below
     *
     * Addresses beginning "dean…" are role accounts. They reach whoever currently
     * holds the post, which is what is wanted here, but they move with the office.
     */
    private array $emails = [
        'Prof. Dr. Md. Fokhray Hossain'      => 'drfokhray@daffodilvarsity.edu.bd',
        'Prof. Dr. Sheak Rashed Haider Noori'=> 'drnoori@daffodilvarsity.edu.bd',
        'Dr. S. M. Aminul Haque'             => 'aheadcse2@daffodilvarsity.edu.bd',
        'Dr. Imran Mahmud'                   => 'imranmahmud@daffodilvarsity.edu.bd',
        'Mr. Md. Sarwar Hossain Mollah'      => 'dad@daffodil.ac',
        'Prof. Dr. Bimal Chandra Das'        => 'bcdas@daffodilvarsity.edu.bd',
        'Dr. A. B. M. Kamal Pasha'           => 'drpasha@daffodilvarsity.edu.bd',
        'Mr. Sheikh Muhammad Rezwan'         => 'rezwan.arch@daffodilvarsity.edu.bd',
        'Prof. Dr. Mohammad Rokibul Kabir'   => 'deanfbe@daffodilvarsity.edu.bd',
        'Dr. Md. Azizur Rahman'              => 'azizur.bba@diu.edu.bd',
        'Professor Dr. Md. Abdur Rouf'       => 'rouf.bba@diu.edu.bd',
        'Mr. Siddiqur Rahman'                => 'sr@daffodilvarsity.edu.bd',
        'Dr. Dewan Golam Yazdani Showrav'    => 'dewan.bba@diu.edu.bd',
        'Prof. Dr. M. Shamsul Alam'          => 'deanfe@daffodilvarsity.edu.bd',
        'Dr. Dara Abdus Satter'              => 'abdussatter@daffodilvarsity.edu.bd',
        'Prof. Dr. Md. Mahbubul Haque'       => 'drhaque@diu.edu.bd',
        'Prof. Dr. Liza Sharmin'             => 'liza.eng@diu.edu.bd',
        // Directory lists him under English, but the address itself carries both his
        // initials and his faculty, which matches the document.
        'Prof. Dr. Kudrat-E-Khuda Babu'      => 'kekbabu.law@diu.edu.bd',
        'Dr. Ehatasham Ul Hoque Eiten'       => 'eiten.eng@diu.edu.bd',
        'Dr. Md. Fouad Hossain Sarker'       => 'fouadsarker@daffodilvarsity.edu.bd',
        'Prof. Dr. Muniruddin Ahmed'         => 'drmuniruddin.ph@diu.edu.bd',
        'Prof. Dr. Md. Bellal Hossain'       => 'drbellal@daffodilvarsity.edu.bd',
        'Dr. A. B. M. Alauddin Chowdhury'    => 'dralauddin@daffodilvarsity.edu.bd',
        'Dr. Md. Shahjahan'                  => 'drshahjahan@daffodilvarsity.edu.bd',
        'Prof. Dr. Md. Mostafa Kamal'        => 'm.kamal@daffodilvarsity.edu.bd',
        'Prof. A. M. M. Hamidur Rahman'      => 'hamidurrahman@diu.edu.bd',
        'Mr. Aftab Hossain'                  => 'aftab.jmc@diu.edu.bd',
        'Prof. Dr. M. A. Rahim'              => 'deanfas@daffodilvarsity.edu.bd',
    ];


    /**
     * Track number => overall chair, then one chair per sub-track in the order the
     * sub-tracks were seeded.
     */
    private array $chairsByTrack = [
        1 => [
            'overall' => 'Prof. Dr. Md. Fokhray Hossain',
            'sub' => [
                'Prof. Dr. Sheak Rashed Haider Noori',
                'Dr. S. M. Aminul Haque',
                'Dr. Imran Mahmud',
                'Mr. Md. Sarwar Hossain Mollah',
            ],
        ],
        2 => [
            'overall' => 'Prof. Dr. Bimal Chandra Das',
            'sub' => [
                'Dr. A. B. M. Kamal Pasha',
                'Prof. Dr. Md. Saidur Rahman',
                'Dr. Kazi A. S. M. Nurul Huda',
                'Mr. Sheikh Muhammad Rezwan',
            ],
        ],
        3 => [
            'overall' => 'Prof. Dr. Mohammad Rokibul Kabir',
            'sub' => [
                'Dr. Md. Azizur Rahman',
                'Professor Dr. Md. Abdur Rouf',
                'Mr. Siddiqur Rahman',
                'Dr. Dewan Golam Yazdani Showrav',
            ],
        ],
        4 => [
            'overall' => 'Prof. Dr. M. Shamsul Alam',
            'sub' => [
                'Dr. Dara Abdus Satter',
                'Prof. Dr. Md. Mahbubul Haque',
                'Dr. Kazi A. S. M. Nurul Huda',
                'Mr. Sheikh Muhammad Rezwan',
                'Dr. Nusrat Jahan',
            ],
        ],
        5 => [
            'overall' => 'Prof. Dr. Liza Sharmin',
            'sub' => [
                'Prof. Dr. Kudrat-E-Khuda Babu',
                'Dr. Ehatasham Ul Hoque Eiten',
                'Dr. Md. Fouad Hossain Sarker',
                'Ms. Bilkis Khanam',
            ],
        ],
        6 => [
            'overall' => 'Prof. Dr. Bellal Hossain',
            'sub' => [
                'Prof. Dr. Muniruddin Ahmed',
                'Prof. Dr. Md. Bellal Hossain',
                'Dr. A. B. M. Alauddin Chowdhury',
                'Dr. Md. Shahjahan',
            ],
        ],
        7 => [
            'overall' => 'Prof. Dr. Liza Sharmin',
            'sub' => [
                'Prof. Dr. Md. Mostafa Kamal',
                'Dr. Ehatasham Ul Hoque Eiten',
                'Prof. A. M. M. Hamidur Rahman',
                'Mr. Aftab Hossain',
            ],
        ],
        8 => [
            'overall' => 'Prof. Dr. Bellal Hossain',
            'sub' => [
                'Prof. Dr. M. A. Rahim',
                'Prof. Dr. Md. Bellal Hossain',
                'Dr. Md. Ahad Ali',
                'Dr. Md. Rashedul Islam',
            ],
        ],
    ];

    public function run(): void
    {
        $tracks = Track::with(['subTracks' => fn ($q) => $q->orderBy('id')])->orderBy('id')->get();

        if ($tracks->isEmpty()) {
            $this->command?->warn('No tracks found. Run TrackSubTrackSeeder first.');
            return;
        }

        // Each person's focus areas, gathered so one user gets a single combined entry
        // even when they chair several sub-tracks.
        $focusAreas = [];
        $assignments = [];

        foreach ($this->chairsByTrack as $trackNumber => $config) {
            $track = $tracks->first(fn ($t) => Str::startsWith($t->name, "Track {$trackNumber}:"));
            if (!$track) {
                $this->command?->warn("Track {$trackNumber} not found; skipping its chairs.");
                continue;
            }

            // An overall chair's subject is the track itself; a sub-track chair's is
            // that sub-track. Each assignment carries its own.
            $assignments[] = [
                'name' => $config['overall'],
                'track' => $track->id,
                'sub' => null,
                'expertise' => $track->name,
                'overall' => true,
            ];

            foreach ($config['sub'] as $position => $chairName) {
                $subTrack = $track->subTracks[$position] ?? null;
                if (!$subTrack) {
                    $this->command?->warn("Track {$trackNumber} has no sub-track #" . ($position + 1) . "; skipping {$chairName}.");
                    continue;
                }

                $assignments[] = [
                    'name' => $chairName,
                    'track' => $track->id,
                    'sub' => $subTrack->id,
                    'expertise' => $subTrack->name,
                    'overall' => false,
                ];
            }
        }

        $placeholders = [];

        DB::transaction(function () use ($assignments, &$placeholders) {
            $names = collect($assignments)->pluck('name')->unique();
            $users = [];

            foreach ($names as $name) {
                $isOverallChair = collect($assignments)
                    ->contains(fn ($a) => $a['name'] === $name && $a['overall']);

                $users[$name] = $this->upsertUser($name, $isOverallChair, $placeholders)->id;
            }

            foreach ($assignments as $assignment) {
                $row = TrackAssignment::firstOrNew([
                    'user_id' => $users[$assignment['name']],
                    'track_id' => $assignment['track'],
                    'sub_track_id' => $assignment['sub'],
                    'role' => 'chair',
                ]);

                // Leave an expertise a chair has since refined for themselves.
                if (!$row->exists || blank($row->expertise)) {
                    $row->expertise = \App\Services\SubmissionRules::topicsFrom($assignment['expertise']);
                }

                $row->save();
            }
        });

        $this->report($placeholders);
    }

    /** @param array<int, string> $placeholders */
    private function upsertUser(string $name, bool $isOverallChair, array &$placeholders): User
    {
        $user = User::where('name', $name)->first();
        $email = $this->emails[$name] ?? $this->placeholderEmail($name);

        // Two chairs occasionally resolve to one directory entry. Never move a real
        // address onto a second account: the unique index would reject it, and the
        // two people would be impossible to tell apart afterwards.
        if (User::where('email', $email)->where('id', '!=', $user?->id ?? 0)->exists()) {
            $email = $this->placeholderEmail($name);
        }

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                // Unknown by design: chairs set their own through the password reset
                // link once a real address is in place.
                'password' => Hash::make(Str::random(32)),
            ]);
        } elseif ($user->email !== $email && Str::endsWith($user->email, '@' . self::PLACEHOLDER_DOMAIN)) {
            // Only ever upgrade a placeholder. An address an admin typed by hand stays.
            $user->update(['email' => $email]);
        }

        if (Str::endsWith($user->email, '@' . self::PLACEHOLDER_DOMAIN)) {
            $placeholders[] = "{$name}  <{$user->email}>";
        }

        // A person chairing a whole track keeps the Track Chair role even if they also
        // chair a sub-track elsewhere; the wider role is the one that matters.
        $roleId = $isOverallChair ? self::ROLE_TRACK_CHAIR : self::ROLE_SUB_TRACK_CHAIR;
        if (!$user->roles->contains('id', $roleId)) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }

        // No profile: that record is for delegates who register and pay. A chair is a
        // user with track assignments; their designation lives in the committee data
        // that already drives the public Committee page.

        return $user->fresh('roles');
    }

    private function placeholderEmail(string $name): string
    {
        // Names carry stacked honorifics ("Prof. Dr. Md. ..."), so strip them all
        // rather than just the first, otherwise the address reads "dr-md-...".
        $bare = preg_replace('/\b(prof(essor)?|dr|mr|mrs|ms|engr)\b\.?\s*/i', '', $name);

        return Str::slug(trim($bare)) . '@' . self::PLACEHOLDER_DOMAIN;
    }

    /** @param array<int, string> $placeholders */
    private function report(array $placeholders): void
    {
        $chairs = TrackAssignment::where('role', 'chair')->count();
        $overall = TrackAssignment::where('role', 'chair')->whereNull('sub_track_id')->count();

        $this->command?->info("Track chairs seeded: {$chairs} assignments ({$overall} whole-track, " . ($chairs - $overall) . ' sub-track).');

        if ($placeholders) {
            $this->command?->warn(count(array_unique($placeholders)) . ' chair(s) fell back to a generated ' . self::PLACEHOLDER_DOMAIN . ' address:');
            foreach (array_unique($placeholders) as $line) {
                $this->command?->warn('  ' . $line);
            }
            $this->command?->warn('Create these mailboxes, or replace the addresses under Admin > Users, before inviting them.');
        }
    }
}
