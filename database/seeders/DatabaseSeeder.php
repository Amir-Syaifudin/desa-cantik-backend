<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DesaSeeder::class,
            UserSeeder::class,
        ]);
        
        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed!');
        $this->command->newLine();
        $this->command->info('Test Credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Admin BPS: admin@bps.go.id / password123');
        $this->command->info('Perangkat Desa 1: nonongan@desacantik.id / password123');
        $this->command->info('Perangkat Desa 2: rindingbatu@desacantik.id / password123');
        $this->command->newLine();
    }
}
