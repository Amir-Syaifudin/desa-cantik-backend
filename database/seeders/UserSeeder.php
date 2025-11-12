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

        // ===== CREATE ROLES =====
        if (UserRole::count() === 0) {
            $this->command->info('Creating default roles...');
            
            $adminRole = UserRole::create([
                'role_name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'System Administrator with full access',
            ]);

            $bpsRole = UserRole::create([
                'role_name' => 'bps_staff',
                'display_name' => 'Staff BPS',
                'description' => 'Staff BPS Toraja Utara',
            ]);

            $desaRole = UserRole::create([
                'role_name' => 'desa_admin',
                'display_name' => 'Admin Desa',
                'description' => 'Administrator Desa',
            ]);
        } else {
            $this->command->info('Roles already exist. Fetching...');
            
            $adminRole = UserRole::where('role_name', 'admin')->first();
            $bpsRole = UserRole::where('role_name', 'bps_staff')->first();
            $desaRole = UserRole::where('role_name', 'desa_admin')->first();
        }

        // ===== CREATE ADMIN USER =====
        if (!User::where('email', 'admin@bps.go.id')->exists()) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@bps.go.id',
                'password' => Hash::make('password'),
                'full_name' => 'Administrator Sistem',
                'phone_number' => '081234567890',
                'role_id' => $adminRole->id,
                'village_id' => null,
                'is_active' => true,
            ]);
            
            $this->command->info('✓ Admin user created: admin@bps.go.id (password: password)');
        } else {
            $this->command->warn('Admin user already exists. Skipping...');
        }

        // ===== CREATE BPS STAFF USER =====
        if (!User::where('email', 'staff@bps.go.id')->exists()) {
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
            $this->command->warn('BPS Staff user already exists. Skipping...');
        }

        // ===== CREATE DESA ADMIN USER (if village exists) =====
        $village = Village::first();
        if ($village && $desaRole) {
            if (!User::where('email', 'admin@desa.go.id')->exists()) {
                User::create([
                    'username' => 'desa_admin',
                    'email' => 'admin@desa.go.id',
                    'password' => Hash::make('password'),
                    'full_name' => 'Admin Desa ' . $village->name,  // ← GANTI
                    'phone_number' => '081234567892',
                    'role_id' => $desaRole->id,
                    'village_id' => $village->id,
                    'is_active' => true,
                ]);
                
                $this->command->info('✓ Desa Admin user created: admin@desa.go.id (password: password)');
            } else {
                $this->command->warn('Desa Admin user already exists. Skipping...');
            }
        } else {
            $this->command->warn('No village found in database. Skipping desa admin creation.');
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
