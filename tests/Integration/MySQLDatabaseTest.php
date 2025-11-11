<?php

namespace Tests\Integration;

use App\Models\StatisticType;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use App\Models\VillageStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MySQLDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseRoles();
    }

    public function test_database_transactions_rollback_on_error(): void
    {
        $this->expectException(\Exception::class);

        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        try {
            DB::transaction(function () use ($village, $type, $officer) {
                VillageStatistic::create([
                    'village_id' => $village->id,
                    'statistic_type_id' => $type->id,
                    'indicator_name' => 'Test',
                    'value' => 100,
                    'year' => 2024,
                    'created_by' => $officer->id,
                ]);

                // Force an error
                throw new \Exception('Test rollback');
            });
        } catch (\Exception $e) {
            // Verify rollback worked
            $this->assertDatabaseCount('village_statistics', 0);
            throw $e;
        }
    }

    public function test_foreign_key_constraint_on_delete(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        VillageStatistic::create([
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
            'indicator_name' => 'Test',
            'value' => 100,
            'year' => 2024,
            'created_by' => $officer->id,
        ]);

        // Try to delete user with statistics (should fail if FK constraint exists)
        $this->expectException(\Illuminate\Database\QueryException::class);
        $officer->delete();
    }

    public function test_cascade_delete_on_village(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        VillageStatistic::factory()->count(3)->create([
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
            'created_by' => $officer->id,
        ]);

        $this->assertDatabaseCount('village_statistics', 3);

        // If cascade delete is configured, statistics should be deleted
        // Note: This depends on your migration configuration
        $village->delete();

        // Adjust assertion based on your actual FK configuration
        // If ON DELETE CASCADE: expect 0
        // If ON DELETE RESTRICT: expect exception above
    }

    public function test_unique_constraint_violation(): void
    {
        // This test depends on whether you have unique constraints
        // Add if you have unique constraints in your schema
        $this->markTestSkipped('Add if unique constraints exist');
    }

    public function test_concurrent_updates_with_locking(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        $statistic = VillageStatistic::create([
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
            'indicator_name' => 'Test',
            'value' => 100,
            'year' => 2024,
            'created_by' => $officer->id,
        ]);

        // Simulate concurrent update with row locking
        DB::transaction(function () use ($statistic) {
            $locked = VillageStatistic::lockForUpdate()->find($statistic->id);
            $locked->value = 200;
            $locked->save();
        });

        $statistic->refresh();
        $this->assertEquals(200, $statistic->value);
    }

    public function test_large_dataset_query_performance(): void
    {
        $village = Village::factory()->create();
        $type = StatisticType::factory()->create();
        $officer = $this->createVillageOfficer($village);

        // Create 100 records
        VillageStatistic::factory()->count(100)->create([
            'village_id' => $village->id,
            'statistic_type_id' => $type->id,
            'created_by' => $officer->id,
        ]);

        $startTime = microtime(true);

        $statistics = VillageStatistic::where('village_id', $village->id)
            ->with(['statisticType', 'creator'])
            ->orderBy('year', 'desc')
            ->paginate(15);

        $executionTime = microtime(true) - $startTime;

        // Query should complete in under 1 second
        $this->assertLessThan(1.0, $executionTime);
        $this->assertCount(15, $statistics->items());
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
