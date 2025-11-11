<?php

namespace Tests\Feature;

use App\Models\StatisticType;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VillageStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseRoles();
    }

    public function test_it_lists_statistic_types_publicly(): void
    {
        StatisticType::factory()->count(2)->create();
        StatisticType::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/statistic-types');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_it_lists_village_statistics_with_filters(): void
    {
        $village = Village::factory()->create();
        $typeA = StatisticType::factory()->create();
        $typeB = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        VillageStatistic::factory()->create([
            'village_id' => $village->id,
            'statistic_type_id' => $typeA->id,
            'indicator_name' => 'Total Penduduk',
            'year' => 2024,
            'created_by' => $officer->id,
        ]);

        VillageStatistic::factory()->create([
            'village_id' => $village->id,
            'statistic_type_id' => $typeB->id,
            'indicator_name' => 'UMKM',
            'year' => 2023,
            'created_by' => $officer->id,
        ]);

        $response = $this->getJson("/api/v1/villages/{$village->id}/statistics?year=2024&statistic_type_id={$typeA->id}");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.indicator_name', 'Total Penduduk');
    }

    public function test_village_officer_can_crud_own_statistics(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        $payload = [
            'statistic_type_id' => $type->id,
            'indicator_name' => 'Total Penduduk',
            'value' => 5000,
            'unit' => 'jiwa',
            'year' => 2024,
            'period' => 'Tahunan',
            'source' => 'Data Desa',
            'notes' => 'Catatan',
        ];

        $createResponse = $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/villages/{$village->id}/statistics", $payload);

        $createResponse->assertCreated()
            ->assertJsonPath('data.indicator_name', 'Total Penduduk');

        $statisticId = $createResponse->json('data.id');

        $updateResponse = $this->actingAs($officer, 'sanctum')
            ->putJson("/api/v1/villages/{$village->id}/statistics/{$statisticId}", [
                'indicator_name' => 'Total Penduduk Updated',
                'value' => 5100,
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.indicator_name', 'Total Penduduk Updated');

        $deleteResponse = $this->actingAs($officer, 'sanctum')
            ->deleteJson("/api/v1/villages/{$village->id}/statistics/{$statisticId}");

        $deleteResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('village_statistics', ['id' => $statisticId]);
    }

    public function test_village_officer_cannot_manage_other_village_statistics(): void
    {
        $villageA = Village::factory()->create();
        $villageB = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($villageA);

        $response = $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/villages/{$villageB->id}/statistics", [
                'statistic_type_id' => $type->id,
                'indicator_name' => 'Total Penduduk',
                'value' => 5000,
                'year' => 2024,
            ]);

        $response->assertForbidden();
    }

    public function test_import_statistics_returns_summary(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create(['code' => 'POPULATION_TOTAL']);
        $officer = $this->createVillageOfficer($village);

        $csvContent = implode("\n", [
            'statistic_type_code,indicator_name,value,unit,year,period,source,notes',
            'POPULATION_TOTAL,Total Penduduk,5000,jiwa,2024,Tahunan,Data Desa,Catatan',
        ]);

        $file = UploadedFile::fake()->createWithContent('statistics.csv', $csvContent);

        $response = $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/villages/{$village->id}/statistics/import", [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.failed', 0);

        $this->assertDatabaseHas('village_statistics', [
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
        ]);
    }

    public function test_export_statistics_returns_file(): void
    {
        $village = Village::factory()->create(['nama_desa' => 'Nonongan']);
        $type = StatisticType::factory()->create(['code' => 'POPULATION_TOTAL']);
        $officer = $this->createVillageOfficer($village);

        VillageStatistic::factory()->create([
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
            'indicator_name' => 'Total Penduduk',
            'year' => 2024,
            'value' => 5000,
            'unit' => 'jiwa',
            'created_by' => $officer->id,
        ]);

        $response = $this->get("/api/v1/villages/{$village->id}/statistics/export?format=csv&year=2024");

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }

    protected function createVillageOfficer(Village $village): User
    {
        $role = UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first();

        return User::factory()
            ->for($role, 'role')
            ->for($village, 'village')
            ->create();
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
