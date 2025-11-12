<?php

namespace Tests\Feature\Dashboard;

use App\Models\ActivityLog;
use App\Models\Publication;
use App\Models\StatisticType;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use App\Models\VillageProfile;
use App\Models\VillageStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseRoles();
    }

    public function test_admin_dashboard_returns_summary(): void
    {
        $adminRole = UserRole::where('role_name', UserRole::BPS_ADMIN)->first();
        $admin = User::factory()
            ->for($adminRole, 'role')
            ->withoutVillage()
            ->create();

        $village = Village::factory()->create(['nama_desa' => 'Desa Makmur']);
        $profile = VillageProfile::factory()->for($village, 'village')->create(['is_featured' => true]);
        $statType = StatisticType::factory()->create(['code' => 'POPULATION_TOTAL', 'category' => 'kependudukan']);

        VillageStatistic::factory()->for($village, 'village')->for($statType)->create([
            'indicator_name' => 'Total Penduduk',
            'year' => now()->year,
            'created_by' => $admin->id,
        ]);

        Publication::factory()->for($village, 'village')->create([
            'published_at' => now(),
        ]);

        ActivityLog::factory()->for($admin)->for($village, 'village')->create([
            'action' => 'create',
            'model_type' => VillageStatistic::class,
            'description' => 'Menambahkan data statistik Total Penduduk',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/dashboard/admin');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_villages', 1)
            ->assertJsonPath('data.summary.total_statistics', 1)
            ->assertJsonPath('data.recent_activities.0.action', 'create');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $officerRole = UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first();
        $officer = User::factory()->for($officerRole, 'role')->create();

        $this->actingAs($officer, 'sanctum')
            ->getJson('/api/v1/dashboard/admin')
            ->assertStatus(403);
    }

    public function test_village_dashboard_returns_data_for_officer(): void
    {
        $officerRole = UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first();
        $officer = User::factory()->for($officerRole, 'role')->create();
        $village = $officer->village;

        VillageProfile::factory()->for($village, 'village')->create([
            'address' => 'Jl. Raya',
            'phone' => '0812',
            'email' => 'desa@example.com',
            'website' => 'https://desa.id',
            'logo_url' => 'https://example.com/logo.png',
        ]);

        $statType = StatisticType::factory()->create(['category' => 'ekonomi']);
        VillageStatistic::factory()->for($village, 'village')->for($statType)->create([
            'year' => now()->year,
            'indicator_name' => 'Jumlah UMKM',
        ]);

        ActivityLog::factory()->for($officer)->for($village, 'village')->create([
            'action' => 'create',
            'model_type' => VillageStatistic::class,
        ]);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard/village');

        $response->assertOk()
            ->assertJsonPath('data.village.id', $village->id)
            ->assertJsonPath('data.summary.total_statistics', 1)
            ->assertJsonPath('data.profile_completeness.percentage', 100);
    }

    public function test_public_dashboard_is_accessible(): void
    {
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->for($village, 'village')->create(['is_featured' => true]);

        Publication::factory()->for($village, 'village')->create([
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/dashboard/public');

        $response->assertOk()
            ->assertJsonPath('data.summary.total_villages', 1);
    }

    protected function seedBaseRoles(): void
    {
        UserRole::updateOrCreate(
            ['role_name' => UserRole::BPS_ADMIN],
            ['display_name' => 'BPS Admin']
        );

        UserRole::updateOrCreate(
            ['role_name' => UserRole::VILLAGE_OFFICER],
            ['display_name' => 'Perangkat Desa']
        );

        UserRole::updateOrCreate(
            ['role_name' => UserRole::GUEST],
            ['display_name' => 'Masyarakat Umum']
        );
    }
}
