<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class VillageModuleControllerTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    /**
     * Test BPS admin can view village modules
     *
     * @test
     */
    public function anyone_can_view_village_modules(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'statistics',
            'status' => 'active',
        ]);
        Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'publications',
            'status' => 'active',
        ]);
        Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'maps',
            'status' => 'active',
        ]);
        Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'profile',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin, 'sanctum');
        $response = $this->getJson("/api/v1/villages/{$village->id}/modules");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'village_id',
                        'module_name',
                        'is_enabled',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount(4, 'data');
    }

    /**
     * Test BPS admin can toggle module status
     *
     * @test
     */
    public function bps_admin_can_toggle_module_status(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $module = Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'statistics',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/statistics/toggle", [
            'is_enabled' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.module_name', 'statistics')
            ->assertJsonPath('data.is_enabled', false);

        $this->assertDatabaseHas('desa_modules', [
            'village_id' => $village->id,
            'name' => 'statistics',
            'status' => 'inactive',
        ]);
    }

    /**
     * Test toggling module from inactive to active
     *
     * @test
     */
    public function can_enable_disabled_module(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $module = Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'publications',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/publications/toggle", [
            'is_enabled' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_enabled', true);

        $this->assertDatabaseHas('desa_modules', [
            'village_id' => $village->id,
            'name' => 'publications',
            'status' => 'active',
        ]);
    }

    /**
     * Test toggle creates module if not exists
     *
     * @test
     */
    public function toggle_creates_module_if_not_exists(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/new_module/toggle", [
            'is_enabled' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.module_name', 'new_module')
            ->assertJsonPath('data.is_enabled', true);

        $this->assertDatabaseHas('desa_modules', [
            'village_id' => $village->id,
            'name' => 'new_module',
            'status' => 'active',
        ]);
    }

    /**
     * Test village officer cannot toggle modules (BPS admin only)
     *
     * @test
     */
    public function village_officer_cannot_toggle_modules(): void
    {
        $village = Village::factory()->create();
        $module = Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'statistics',
            'status' => 'active',
        ]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/statistics/toggle", [
            'is_enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot toggle modules
     *
     * @test
     */
    public function guest_cannot_toggle_modules(): void
    {
        $village = Village::factory()->create();
        $module = Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'statistics',
        ]);

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/statistics/toggle", [
            'is_enabled' => false,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test toggle validation requires is_enabled boolean
     *
     * @test
     */
    public function toggle_requires_is_enabled_boolean(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/statistics/toggle", [
            'is_enabled' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['is_enabled']);
    }

    /**
     * Test toggle creates activity log
     *
     * @test
     */
    public function toggle_creates_activity_log(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $module = Module::factory()->create([
            'village_id' => $village->id,
            'name' => 'statistics',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/modules/statistics/toggle", [
            'is_enabled' => false,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => 'App\Models\Module',
            'model_id' => $module->id,
            'action' => 'update',
        ]);
    }

    /**
     * Test 404 for non-existent village
     *
     * @test
     */
    public function returns_404_for_non_existent_village(): void
    {
        $admin = $this->actingAsAdmin();

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson('/api/v1/villages/99999/modules/statistics/toggle', [
            'is_enabled' => false,
        ]);

        $response->assertStatus(404);
    }
}
