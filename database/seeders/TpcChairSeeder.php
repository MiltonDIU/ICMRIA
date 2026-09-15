<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * An account holding the TPC Chair role, so final approval (requirement document,
 * Phase 5) has someone to perform it.
 *
 * The committee lists Prof. Dr. Sheak Rashed Haider Noori as TPC Chair, but he also
 * chairs a sub-track. Giving one account both roles would let him approve decisions he
 * entered himself, and would widen what he sees as a track chair to every track. A
 * separate account keeps the two duties apart until the organisers decide otherwise.
 *
 * Outside production the account signs in with "password". In production it gets an
 * unknowable password and has to be claimed through the password reset link.
 */
class TpcChairSeeder extends Seeder
{
    private const ROLE_TPC_CHAIR = 7;
    private const EMAIL = 'tpc.chair@icmria.com';

    public function run(): void
    {
        if (!Role::find(self::ROLE_TPC_CHAIR)) {
            $this->command?->warn('TPC Chair role missing. Run RolesTableSeeder first.');
            return;
        }

        $user = User::firstOrCreate(['email' => self::EMAIL], [
            'name' => 'TPC Chair',
            'password' => Hash::make(app()->environment('production') ? Str::random(40) : 'password'),
            'email_verified_at' => now(),
        ]);

        $user->roles()->syncWithoutDetaching([self::ROLE_TPC_CHAIR]);

        $this->command?->info('TPC Chair account: ' . self::EMAIL
            . (app()->environment('production') ? ' (set the password through the reset link)' : ' / password'));
    }
}
