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
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,
            CountriesTableSeeder::class,
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,
            SettingsTableSeeder::class,
            TrackSubTrackSeeder::class,
            SpeakerTypeSeeder::class,
            KeynoteInvitedSpeakerSeeder::class,
            SpeakersTableSeeder::class,
            ScheduleCategorySeeder::class,
            SchedulesTableSeeder::class,
            VenuesTableSeeder::class,
            HotelsTableSeeder::class,
            GalleriesTableSeeder::class,
            SponsorsTableSeeder::class,
            FaqsTableSeeder::class,
            AmenitiesTableSeeder::class,
            PricesTableSeeder::class,
            AmenityPriceTableSeeder::class,
            CommitteeManagementSeeder::class,
            ConferenceMessageCategorySeeder::class,
            ConferenceMessageSeeder::class,
        ]);
    }
}