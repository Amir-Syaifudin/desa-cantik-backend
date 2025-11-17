<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class PasswordManagementTest extends TestCase
{
    use CreatesTestData;

    /**
     * Test admin can update their own password
     * 
     * Tests the endpoint that replaced TODO in UbahPasswordAdminBPS.jsx (line 73)
     */
    public function test_admin_can_update_own_password(): void
    {
        $admin = $this->actingAsAdmin();
        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password updated successfully. Please login again.',
            ]);
    }

    /**
     * Test admin can reset village officer password
     * 
     * Tests the endpoint that replaced TODO in UbahPasswordAdminBPS.jsx (line 77)
     * Tests the endpoint that replaced mock MOCK_DESA_LIST
     */
    public function test_admin_can_reset_village_officer_password(): void
    {
        $admin = $this->actingAsAdmin();
        $villageOfficers = $this->createVillageOfficers(2);

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/users/{$villageOfficers[0]->id}/reset-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset successfully. User must login again.',
            ]);
    }

    /**
     * Test admin cannot reset their own password via admin reset endpoint
     */
    public function test_admin_cannot_reset_own_password_via_admin_endpoint(): void
    {
        $admin = $this->actingAsAdmin();
        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/users/{$admin->id}/reset-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot reset your own password. Use the update password endpoint instead.',
            ]);
    }

    /**
     * Test village officer can update their own password
     * 
     * Tests the endpoint that replaced TODO in UbahPasswordPerangkatDesa.jsx (line 60)
     */
    public function test_village_officer_can_update_own_password(): void
    {
        $village = Village::factory()->create();
        $officer = $this->actingAsVillageOfficer($village);

        $this->actingAs($officer, 'sanctum');

        $response = $this->putJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test village officer cannot reset another officer's password
     */
    public function test_village_officer_cannot_reset_other_officer_password(): void
    {
        $villageOfficers = $this->createVillageOfficers(2);

        $this->actingAs($villageOfficers[0], 'sanctum');

        $response = $this->putJson("/api/v1/users/{$villageOfficers[1]->id}/reset-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can list village officers (for dropdown in UbahPasswordAdminBPS.jsx)
     */
    public function test_admin_can_list_village_officers(): void
    {
        $admin = $this->actingAsAdmin();
        $this->createVillageOfficers(3);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/users?role=village_officer');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'full_name',
                        'email',
                        'village',
                    ],
                ],
            ]);
    }
}
