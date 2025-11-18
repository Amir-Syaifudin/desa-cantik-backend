<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $bpsAdmin;

    protected User $villageOfficer;

    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test villages
        $this->village = Village::factory()->create(['name' => 'Test Village']);

        // Create BPS Admin
        $adminRole = UserRole::where('role_name', UserRole::BPS_ADMIN)->first();
        $this->bpsAdmin = User::factory()->create([
            'role_id' => $adminRole->id,
            'village_id' => null,
            'username' => 'admin_test',
            'email' => 'admin@test.com',
        ]);

        // Create Village Officer
        $officerRole = UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first();
        $this->villageOfficer = User::factory()->create([
            'role_id' => $officerRole->id,
            'village_id' => $this->village->id,
            'username' => 'officer_test',
            'email' => 'officer@test.com',
        ]);
    }

    /** @test */
    public function bps_admin_can_list_all_users(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson('/api/v1/users');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'username',
                        'email',
                        'full_name',
                        'role_id',
                        'village_id',
                        'is_active',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function village_officer_cannot_list_users(): void
    {
        $response = $this->actingAs($this->villageOfficer, 'sanctum')
            ->getJson('/api/v1/users');

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('message', fn($message) => str_contains($message, 'Access denied'));
    }

    /** @test */
    public function guest_cannot_list_users(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    /** @test */
    public function bps_admin_can_view_user_details(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson("/api/v1/users/{$this->villageOfficer->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->villageOfficer->id,
                    'username' => $this->villageOfficer->username,
                    'email' => $this->villageOfficer->email,
                ],
            ]);
    }

    /** @test */
    public function bps_admin_can_create_new_user(): void
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => UserRole::VILLAGE_OFFICER,
            'village_id' => $this->village->id,
            'full_name' => 'New User',
            'phone' => '08123456789',
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->postJson('/api/v1/users', $userData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'username',
                    'email',
                    'full_name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
        ]);
    }

    /** @test */
    public function creating_user_requires_unique_username(): void
    {
        $userData = [
            'username' => $this->villageOfficer->username, // Duplicate username
            'email' => 'different@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => UserRole::VILLAGE_OFFICER,
            'village_id' => $this->village->id,
            'full_name' => 'Test User',
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->postJson('/api/v1/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    public function creating_user_requires_unique_email(): void
    {
        $userData = [
            'username' => 'differentuser',
            'email' => $this->villageOfficer->email, // Duplicate email
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => UserRole::VILLAGE_OFFICER,
            'village_id' => $this->village->id,
            'full_name' => 'Test User',
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->postJson('/api/v1/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function creating_user_requires_valid_role(): void
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'invalid_role', // Invalid role
            'village_id' => $this->village->id,
            'full_name' => 'Test User',
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->postJson('/api/v1/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function creating_village_officer_requires_village_id(): void
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => UserRole::VILLAGE_OFFICER,
            'village_id' => null, // Missing village_id for village officer
            'full_name' => 'Test User',
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->postJson('/api/v1/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['village_id']);
    }

    /** @test */
    public function bps_admin_can_update_user(): void
    {
        $updateData = [
            'full_name' => 'Updated Full Name',
            'phone' => '08987654321',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->putJson("/api/v1/users/{$this->villageOfficer->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->villageOfficer->id,
            'full_name' => 'Updated Full Name',
            'phone_number' => '08987654321',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function village_officer_cannot_update_users(): void
    {
        $updateData = [
            'full_name' => 'Hacked Name',
        ];

        $response = $this->actingAs($this->villageOfficer, 'sanctum')
            ->putJson("/api/v1/users/{$this->bpsAdmin->id}", $updateData);

        $response->assertForbidden();
    }

    /** @test */
    public function bps_admin_can_reset_user_password(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->putJson("/api/v1/users/{$this->villageOfficer->id}/reset-password", [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('message', fn($message) => str_contains($message, 'Password reset successfully'));

        // Verify the user can login with new password
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'login' => $this->villageOfficer->email,
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertOk();
    }

    /** @test */
    public function password_reset_requires_confirmation(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->putJson("/api/v1/users/{$this->villageOfficer->id}/reset-password", [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_reset_requires_minimum_length(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->putJson("/api/v1/users/{$this->villageOfficer->id}/reset-password", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function bps_admin_can_delete_user(): void
    {
        $userToDelete = User::factory()->create([
            'role_id' => UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first()->id,
            'village_id' => $this->village->id,
        ]);

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->deleteJson("/api/v1/users/{$userToDelete->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
        ]);
    }

    /** @test */
    public function village_officer_cannot_delete_users(): void
    {
        $response = $this->actingAs($this->villageOfficer, 'sanctum')
            ->deleteJson("/api/v1/users/{$this->bpsAdmin->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function cannot_delete_non_existent_user(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->deleteJson('/api/v1/users/99999');

        $response->assertNotFound();
    }

    /** @test */
    public function user_list_can_be_filtered_by_role(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson('/api/v1/users?role=' . UserRole::BPS_ADMIN);

        $response->assertOk();

        $users = $response->json('data');
        $this->assertNotEmpty($users);
    }

    /** @test */
    public function user_list_can_be_filtered_by_village(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson("/api/v1/users?village_id={$this->village->id}");

        $response->assertOk();

        $users = $response->json('data');
        foreach ($users as $user) {
            if ($user['village_id']) {
                $this->assertEquals($this->village->id, $user['village_id']);
            }
        }
    }

    /** @test */
    public function user_list_can_be_searched_by_username(): void
    {
        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson('/api/v1/users?search=admin_test');

        $response->assertOk();

        $users = $response->json('data');
        $this->assertNotEmpty($users);
    }

    /** @test */
    public function user_list_is_paginated(): void
    {
        // Create additional users
        User::factory()->count(20)->create([
            'role_id' => UserRole::where('role_name', UserRole::VILLAGE_OFFICER)->first()->id,
            'village_id' => $this->village->id,
        ]);

        $response = $this->actingAs($this->bpsAdmin, 'sanctum')
            ->getJson('/api/v1/users?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);

        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertCount(10, $response->json('data'));
    }
}
