<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting user seeding...');

        // ===== CREATE OR FETCH ROLES =====
        if (UserRole::count() === 0) {
            $this->command->info('Creating default roles...');

            UserRole::create(['role_name' => 'admin', 'display_name' => 'Administrator', 'description' => 'System Administrator']);
            UserRole::create(['role_name' => 'bps_admin', 'display_name' => 'BPS Admin', 'description' => 'BPS Administrator']);
            UserRole::create(['role_name' => 'bps_staff', 'display_name' => 'BPS Staff', 'description' => 'BPS Staff']);
            UserRole::create(['role_name' => 'desa_admin', 'display_name' => 'Desa Admin', 'description' => 'Admin Desa']);
            UserRole::create(['role_name' => 'village_officer', 'display_name' => 'Village Officer', 'description' => 'Village Officer or Perangkat Desa']);
            UserRole::create(['role_name' => 'guest', 'display_name' => 'Guest', 'description' => 'Guest User']);
        }

        $bpsAdminRole = UserRole::where('role_name', 'bps_admin')->first();
        $villageOfficerRole = UserRole::where('role_name', 'village_officer')->first();
        $guestRole = UserRole::where('role_name', 'guest')->first();
        $bpsRole = UserRole::where('role_name', 'bps_staff')->first();
        $desaRole = UserRole::where('role_name', 'desa_admin')->first();

        // Verify required roles exist
        if (! $bpsAdminRole || ! $villageOfficerRole || ! $guestRole) {
            $this->command->error('Required roles not found!');

            return;
        }

        // ===== CREATE BPS ADMIN USER =====
        if (! User::where('email', 'admin@bps.go.id')->exists()) {
            User::create([
                'username' => 'bps_admin',
                'email' => 'admin@bps.go.id',
                'password' => Hash::make('password'),
                'full_name' => 'Administrator BPS',
                'phone_number' => '081234567890',
                'role_id' => $bpsAdminRole->id,
                'village_id' => null,
                'is_active' => true,
            ]);

            $this->command->info('✓ BPS Admin user created: admin@bps.go.id (password: password)');
        } else {
            $this->command->warn('BPS Admin user already exists. Skipping...');
        }

        // ===== CREATE BPS STAFF USER =====
        if ($bpsRole && ! User::where('email', 'staff@bps.go.id')->exists()) {
            User::create([
                'username' => 'bps_staff',
                'email' => 'staff@bps.go.id',
                'password' => Hash::make('password'),
                'full_name' => 'Staff BPS Toraja Utara',
                'phone_number' => '081234567891',
                'role_id' => $bpsRole->id,
                'village_id' => null,
                'is_active' => true,
            ]);

            $this->command->info('✓ BPS Staff user created: staff@bps.go.id (password: password)');
        } else {
            $this->command->warn('BPS Staff user already exists or role missing. Skipping...');
        }

        // ===== CREATE VILLAGE OFFICER USER =====
        $village = Village::first();
        if ($village && $villageOfficerRole) {
            if (! User::where('email', 'officer@desa.go.id')->exists()) {
                User::create([
                    'username' => 'village_officer',
                    'email' => 'officer@desa.go.id',
                    'password' => Hash::make('password'),
                    'full_name' => 'Perangkat Desa '.$village->name,
                    'phone_number' => '081234567891',
                    'role_id' => $villageOfficerRole->id,
                    'village_id' => $village->id,
                    'is_active' => true,
                ]);

                $this->command->info('✓ Village Officer user created: officer@desa.go.id (password: password)');
            } else {
                $this->command->warn('Village Officer user already exists. Skipping...');
            }
        } else {
            $this->command->warn('No village found in database. Skipping village officer/desa admin creation.');
        }

        // ===== SUMMARY =====
        $this->command->newLine();
        $this->command->info('==================================');
        $this->command->info('✓ User seeding completed!');
        $this->command->info('==================================');
        $this->command->info('Total Users: '.User::count());
        $this->command->info('Total Roles: '.UserRole::count());
    }
}
