<?php

namespace Tests\Feature;

use App\Models\MapPoint;
use App\Models\ThematicMap;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class MapPointControllerTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    /**
     * Test BPS admin can create map point
     *
     * @test
     */
    public function bps_admin_can_create_map_point(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => 'Health Center',
            'description' => 'Main village health center',
            'category' => 'health',
            'latitude' => -2.9800,
            'longitude' => 119.8900,
            'image_url' => 'https://example.com/health-center.jpg',
            'additional_info' => ['beds' => 20, 'doctors' => 3],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'thematic_map_id',
                    'name',
                    'description',
                    'category',
                    'latitude',
                    'longitude',
                    'image_url',
                    'additional_info',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Health Center')
            ->assertJsonPath('data.latitude', -2.9800)
            ->assertJsonPath('data.longitude', 119.8900);

        $this->assertDatabaseHas('map_points', [
            'thematic_map_id' => $map->id,
            'name' => 'Health Center',
            'category' => 'health',
        ]);
    }

    /**
     * Test village officer can create point for own village map
     *
     * @test
     */
    public function village_officer_can_create_point_for_own_village(): void
    {
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => 'School',
            'latitude' => -2.9800,
            'longitude' => 119.8900,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    /**
     * Test village officer cannot create point for other village
     *
     * @test
     */
    public function village_officer_cannot_create_point_for_other_village(): void
    {
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village2->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village1->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => 'School',
            'latitude' => -2.9800,
            'longitude' => 119.8900,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test map point creation validation
     *
     * @test
     */
    public function map_point_creation_validates_input(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => '', // required
            'latitude' => 100, // out of range
            'longitude' => 200, // out of range
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'latitude', 'longitude']);
    }

    /**
     * Test BPS admin can update map point
     *
     * @test
     */
    public function bps_admin_can_update_map_point(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);
        $point = MapPoint::factory()->create([
            'thematic_map_id' => $map->id,
            'name' => 'Original Name',
            'latitude' => -2.98,
            'longitude' => 119.89,
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('map_points', [
            'id' => $point->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test village officer can update point for own village
     *
     * @test
     */
    public function village_officer_can_update_point_for_own_village(): void
    {
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);
        $point = MapPoint::factory()->create([
            'thematic_map_id' => $map->id,
            'name' => 'Original',
        ]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}", [
            'name' => 'Updated by Officer',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated by Officer');
    }

    /**
     * Test village officer cannot update point for other village
     *
     * @test
     */
    public function village_officer_cannot_update_point_for_other_village(): void
    {
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village2->id]);
        $point = MapPoint::factory()->create(['thematic_map_id' => $map->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village1->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}", [
            'name' => 'Unauthorized Update',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test BPS admin can delete map point
     *
     * @test
     */
    public function bps_admin_can_delete_map_point(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);
        $point = MapPoint::factory()->create(['thematic_map_id' => $map->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('map_points', [
            'id' => $point->id,
        ]);
    }

    /**
     * Test village officer can delete point for own village
     *
     * @test
     */
    public function village_officer_can_delete_point_for_own_village(): void
    {
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);
        $point = MapPoint::factory()->create(['thematic_map_id' => $map->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->deleteJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}");

        $response->assertStatus(200);
    }

    /**
     * Test village officer cannot delete point for other village
     *
     * @test
     */
    public function village_officer_cannot_delete_point_for_other_village(): void
    {
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village2->id]);
        $point = MapPoint::factory()->create(['thematic_map_id' => $map->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village1->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->deleteJson("/api/v1/thematic-maps/{$map->id}/points/{$point->id}");

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot create map point
     *
     * @test
     */
    public function guest_cannot_create_map_point(): void
    {
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);

        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => 'Point',
            'latitude' => -2.98,
            'longitude' => 119.89,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test map point operations create activity logs
     *
     * @test
     */
    public function map_point_operations_create_activity_logs(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $map = ThematicMap::factory()->create(['desa_id' => $village->id]);

        $this->actingAs($admin, 'sanctum');

        // Create
        $response = $this->postJson("/api/v1/thematic-maps/{$map->id}/points", [
            'name' => 'Test Point',
            'latitude' => -2.98,
            'longitude' => 119.89,
        ]);

        $pointId = $response->json('data.id');

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => 'App\Models\MapPoint',
            'model_id' => $pointId,
            'action' => 'create',
        ]);

        // Update
        $this->putJson("/api/v1/thematic-maps/{$map->id}/points/{$pointId}", [
            'name' => 'Updated',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => 'App\Models\MapPoint',
            'model_id' => $pointId,
            'action' => 'update',
        ]);

        // Delete
        $this->deleteJson("/api/v1/thematic-maps/{$map->id}/points/{$pointId}");

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => 'App\Models\MapPoint',
            'model_id' => $pointId,
            'action' => 'delete',
        ]);
    }
}
