<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use App\Models\VillageProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class VillageProfileControllerTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    /**
     * Test anyone can view village profile (public endpoint)
     *
     * @test
     */
    public function anyone_can_view_village_profile(): void
    {
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create([
            'village_id' => $village->id,
            'deskripsi' => 'Test description',
            'visi' => 'Test vision',
            'misi' => json_encode(['Mission 1', 'Mission 2']),
            'area' => 15.5,
            'population' => 5000,
            'address' => '123 Test St',
            'phone' => '081234567890',
            'email' => 'village@test.com',
        ]);

        $response = $this->getJson("/api/v1/villages/{$village->id}/profile");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'village_id',
                    'description',
                    'vision',
                    'mission',
                    'area',
                    'population',
                    'population_density',
                    'address',
                    'phone',
                    'email',
                    'website',
                    'logo_url',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.village_id', $village->id)
            ->assertJsonPath('data.description', 'Test description')
            ->assertJsonPath('data.vision', 'Test vision')
            ->assertJsonPath('data.mission', ['Mission 1', 'Mission 2'])
            ->assertJsonPath('data.area', 15.5)
            ->assertJsonPath('data.population', 5000);
    }

    /**
     * Test 404 when viewing non-existent village profile
     *
     * @test
     */
    public function returns_404_for_non_existent_village_profile(): void
    {
        $village = Village::factory()->create();
        // No profile created

        $response = $this->getJson("/api/v1/villages/{$village->id}/profile");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /**
     * Test BPS admin can update village profile
     *
     * @test
     */
    public function bps_admin_can_update_village_profile(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create([
            'village_id' => $village->id,
            'deskripsi' => 'Old description',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'description' => 'Updated description',
            'vision' => 'New vision',
            'mission' => ['Mission A', 'Mission B', 'Mission C'],
            'area' => 25.5,
            'population' => 7500,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description', 'Updated description')
            ->assertJsonPath('data.vision', 'New vision')
            ->assertJsonPath('data.mission', ['Mission A', 'Mission B', 'Mission C'])
            ->assertJsonPath('data.area', 25.5)
            ->assertJsonPath('data.population', 7500);

        $this->assertDatabaseHas('village_profiles', [
            'village_id' => $village->id,
            'deskripsi' => 'Updated description',
            'visi' => 'New vision',
        ]);
    }

    /**
     * Test village officer can update own village profile
     *
     * @test
     */
    public function village_officer_can_update_own_village_profile(): void
    {
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create(['village_id' => $village->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'description' => 'Updated by officer',
            'phone' => '089999999999',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description', 'Updated by officer');
    }

    /**
     * Test village officer cannot update other village profile
     *
     * @test
     */
    public function village_officer_cannot_update_other_village_profile(): void
    {
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();
        $profile = VillageProfile::factory()->create(['village_id' => $village2->id]);

        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village1->id,
        ]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village2->id}/profile", [
            'description' => 'Unauthorized update',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test profile update validation
     *
     * @test
     */
    public function profile_update_validates_input(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create(['village_id' => $village->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'email' => 'invalid-email',
            'website' => 'not-a-url',
            'area' => -10,
            'population' => -100,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email', 'website', 'area', 'population']);
    }

    /**
     * Test BPS admin can upload village logo
     *
     * @test
     */
    public function bps_admin_can_upload_village_logo(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create(['village_id' => $village->id]);

        $this->actingAs($admin, 'sanctum');

        // For now, we're using URL field. In future, this could be file upload
        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'logo_url' => 'https://example.com/logo.png',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.logo_url', 'https://example.com/logo.png');
    }

    /**
     * Test partial update works (only updates provided fields)
     *
     * @test
     */
    public function partial_update_works(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create([
            'village_id' => $village->id,
            'deskripsi' => 'Original description',
            'visi' => 'Original vision',
            'area' => 10.0,
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'description' => 'Updated description only',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.description', 'Updated description only');

        // Other fields should remain unchanged
        $profile->refresh();
        $this->assertEquals('Original vision', $profile->visi);
        $this->assertEquals(10.0, $profile->area);
    }

    /**
     * Test guest cannot update village profile
     *
     * @test
     */
    public function guest_cannot_update_village_profile(): void
    {
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create(['village_id' => $village->id]);

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'description' => 'Unauthorized',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test profile update creates activity log
     *
     * @test
     */
    public function profile_update_creates_activity_log(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $profile = VillageProfile::factory()->create([
            'village_id' => $village->id,
            'deskripsi' => 'Original',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/profile", [
            'description' => 'Updated',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => 'App\Models\VillageProfile',
            'model_id' => $profile->id,
            'action' => 'update',
        ]);
    }
}
