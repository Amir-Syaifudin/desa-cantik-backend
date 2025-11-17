<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            VillageSeeder::class,
            StatisticTypeSeeder::class,
            UserSeeder::class,
            GeospatialDataSeeder::class,
            ThematicMapSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('Database seeding completed!');
        $this->command->newLine();
        $this->command->info('Test Credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Admin BPS: admin@bps.go.id / password');
        $this->command->info('Perangkat Desa 1: nonongan@desacantik.id / password');
        $this->command->info('Perangkat Desa 2: rindingbatu@desacantik.id / password');
        $this->command->newLine();
    }
}
