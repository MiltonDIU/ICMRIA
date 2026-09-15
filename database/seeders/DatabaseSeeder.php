<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for ICMRIA 2027.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // 1. Access control. Role permissions need both lists in place.
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,

            // 2. Reference data read by everything below.
            CountriesTableSeeder::class,
            SettingsTableSeeder::class,

            // 3. The administrator account and its role.
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,

            // 4. Registration fees. Prices must exist before any profile or paper
            //    author points at one, and the amenity links need both sides.
            AmenitiesTableSeeder::class,
            PricesTableSeeder::class,
            AmenityPriceTableSeeder::class,

            // 5. Tracks and the people who work in them. Chairs come before
            //    reviewers, because ReviewerSeeder leaves chairs out of the pool.
            TrackSubTrackSeeder::class,
            TrackChairSeeder::class,
            // A separate TPC Chair account for final approval.
            TpcChairSeeder::class,
            ReviewerSeeder::class,
            // Placeholder reviewers on @icmria.com for when the faculty directory is empty.
            // Refuses to run in production.
            ReviewerPoolSeeder::class,

            // 6. Public site content. Speakers need their types and the tracks
            //    (SpeakersTableSeeder calls KeynoteInvitedSpeakerSeeder); schedule
            //    categories must exist before the schedules that are filed under them.
            SpeakerTypeSeeder::class,
            SpeakersTableSeeder::class,
            ScheduleCategorySeeder::class,
            SchedulesTableSeeder::class,
            VenuesTableSeeder::class,
            HotelsTableSeeder::class,
            GalleriesTableSeeder::class,
            SponsorsTableSeeder::class,
            FaqsTableSeeder::class,
            CommitteeManagementSeeder::class,
            ConferenceMessageCategorySeeder::class,
            ConferenceMessageSeeder::class,

            // 7. Demonstration data, last, since it needs tracks, prices, chairs and
            //    the reviewer pool. The demo authors take ICMRIA2027-001 to 005 and
            //    the review workflow papers follow. Both refuse to run in production.
            DemoAuthorSeeder::class,
            DemoReviewWorkflowSeeder::class,
        ]);
    }
}