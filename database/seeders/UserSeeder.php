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

            UserRole::create(['role_name' => 'bps_admin', 'display_name' => 'Admin BPS', 'description' => 'BPS Administrator with full access to all villages']);
            UserRole::create(['role_name' => 'village_officer', 'display_name' => 'Perangkat Desa', 'description' => 'Village Officer with access only to their assigned village']);
        }

        $bpsAdminRole = UserRole::where('role_name', 'bps_admin')->first();
        $villageOfficerRole = UserRole::where('role_name', 'village_officer')->first();

        // Verify required roles exist
        if (! $bpsAdminRole || ! $villageOfficerRole) {
            $this->command->error('Required roles not found!');

            return;
        }

        // ===== CREATE BPS ADMIN USER =====
        if (! User::where('email', 'admin@bps.go.id')->exists()) {
            User::create([
                'username' => 'bps_admin',
                'email' => 'admin@bps.go.id',
                'password' => Hash::make('password123'),
                'full_name' => 'Administrator BPS Toraja Utara',
                'phone_number' => '081234567890',
                'role_id' => $bpsAdminRole->id,
                'village_id' => null,
                'is_active' => true,
            ]);

            $this->command->info('✓ BPS Admin user created: admin@bps.go.id (password: password123)');
        } else {
            $this->command->warn('BPS Admin user already exists. Skipping...');
        }

        // ===== CREATE VILLAGE OFFICER USERS =====
        $villages = Village::all();
        if ($villages->count() > 0 && $villageOfficerRole) {
            // Create village officers for Nonongan Selatan and Rindingbatu if they exist
            $nonongan = $villages->firstWhere('name', 'Nonongan Selatan');
            if ($nonongan && ! User::where('email', 'nonongan@desacantik.id')->exists()) {
                User::create([
                    'username' => 'perangkat_nonongan',
                    'email' => 'nonongan@desacantik.id',
                    'password' => Hash::make('password123'),
                    'full_name' => 'Perangkat Desa Nonongan Selatan',
                    'phone_number' => '081234567891',
                    'role_id' => $villageOfficerRole->id,
                    'village_id' => $nonongan->id,
                    'is_active' => true,
                ]);
                $this->command->info('✓ Village Officer created: nonongan@desacantik.id (password: password123)');
            }

            $rindingbatu = $villages->firstWhere('name', 'Rindingbatu');
            if ($rindingbatu && ! User::where('email', 'rindingbatu@desacantik.id')->exists()) {
                User::create([
                    'username' => 'perangkat_rindingbatu',
                    'email' => 'rindingbatu@desacantik.id',
                    'password' => Hash::make('password123'),
                    'full_name' => 'Perangkat Desa Rindingbatu',
                    'phone_number' => '081234567892',
                    'role_id' => $villageOfficerRole->id,
                    'village_id' => $rindingbatu->id,
                    'is_active' => true,
                ]);
                $this->command->info('✓ Village Officer created: rindingbatu@desacantik.id (password: password123)');
            }
        } else {
            $this->command->warn('No villages found in database. Skipping village officer creation.');
        }

        // ===== SUMMARY =====
        $this->command->newLine();
        $this->command->info('==================================');
        $this->command->info('✓ User seeding completed!');
        $this->command->info('==================================');
        $this->command->info('Total Users: ' . User::count());
        $this->command->info('Total Roles: ' . UserRole::count());
    }
}
