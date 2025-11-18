<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ActivityLogControllerTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    /**
     * Test BPS admin can list all activity logs
     *
     * @test
     */
    public function bps_admin_can_list_all_activity_logs(): void
    {
        $admin = $this->actingAsAdmin();
        $village1 = Village::factory()->create(['name' => 'Village 1']);
        $village2 = Village::factory()->create(['name' => 'Village 2']);

        // Create activity logs for different villages
        ActivityLog::factory()->count(3)->create(['village_id' => $village1->id, 'action' => 'create']);
        ActivityLog::factory()->count(2)->create(['village_id' => $village2->id, 'action' => 'update']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'village_id',
                        'action',
                        'model_type',
                        'description',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJsonPath('success', true);

        $this->assertGreaterThanOrEqual(5, $response->json('meta.total'));
    }

    /**
     * Test activity logs can be filtered by user ID
     *
     * @test
     */
    public function activity_logs_can_be_filtered_by_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ActivityLog::factory()->count(3)->create(['user_id' => $user1->id]);
        ActivityLog::factory()->count(2)->create(['user_id' => $user2->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/v1/activity-logs?user_id={$user1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);

        foreach ($response->json('data') as $log) {
            $this->assertEquals($user1->id, $log['user_id']);
        }
    }

    /**
     * Test activity logs can be filtered by village ID
     *
     * @test
     */
    public function activity_logs_can_be_filtered_by_village(): void
    {
        $admin = $this->actingAsAdmin();
        $village1 = Village::factory()->create();
        $village2 = Village::factory()->create();

        ActivityLog::factory()->count(4)->create(['village_id' => $village1->id]);
        ActivityLog::factory()->count(2)->create(['village_id' => $village2->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/v1/activity-logs?village_id={$village1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 4);

        foreach ($response->json('data') as $log) {
            $this->assertEquals($village1->id, $log['village_id']);
        }
    }

    /**
     * Test activity logs can be filtered by action type
     *
     * @test
     */
    public function activity_logs_can_be_filtered_by_action(): void
    {
        $admin = $this->actingAsAdmin();

        ActivityLog::factory()->count(3)->create(['action' => 'create']);
        ActivityLog::factory()->count(2)->create(['action' => 'update']);
        ActivityLog::factory()->count(1)->create(['action' => 'delete']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs?action=create');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);

        foreach ($response->json('data') as $log) {
            $this->assertEquals('create', $log['action']);
        }
    }

    /**
     * Test activity logs can be filtered by model type
     *
     * @test
     */
    public function activity_logs_can_be_filtered_by_model_type(): void
    {
        $admin = $this->actingAsAdmin();

        ActivityLog::factory()->count(3)->create(['model_type' => 'App\Models\VillageStatistic']);
        ActivityLog::factory()->count(2)->create(['model_type' => 'App\Models\Publication']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs?model_type=VillageStatistic');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);
    }

    /**
     * Test activity logs can be filtered by date range
     *
     * @test
     */
    public function activity_logs_can_be_filtered_by_date_range(): void
    {
        $admin = $this->actingAsAdmin();

        ActivityLog::factory()->create(['created_at' => now()->subDays(10)]);
        ActivityLog::factory()->count(2)->create(['created_at' => now()->subDays(3)]);
        ActivityLog::factory()->create(['created_at' => now()]);

        $this->actingAs($admin, 'sanctum');

        $fromDate = now()->subDays(5)->format('Y-m-d H:i:s');
        $toDate = now()->format('Y-m-d H:i:s');

        $response = $this->getJson("/api/v1/activity-logs?from_date={$fromDate}&to_date={$toDate}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3);
    }

    /**
     * Test activity logs are paginated
     *
     * @test
     */
    public function activity_logs_are_paginated(): void
    {
        $admin = $this->actingAsAdmin();

        ActivityLog::factory()->count(25)->create();

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(10, 'data');

        $this->assertGreaterThanOrEqual(25, $response->json('meta.total'));
    }

    /**
     * Test BPS admin can view activity log details
     *
     * @test
     */
    public function bps_admin_can_view_activity_log_details(): void
    {
        $admin = $this->actingAsAdmin();
        $log = ActivityLog::factory()->create([
            'action' => 'update',
            'old_data' => ['value' => 100],
            'new_data' => ['value' => 200],
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/v1/activity-logs/{$log->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $log->id)
            ->assertJsonPath('data.action', 'update')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'village',
                    'action',
                    'model_type',
                    'model_id',
                    'description',
                    'old_data',
                    'new_data',
                    'changes',
                    'ip_address',
                    'user_agent',
                    'created_at',
                ],
            ]);
    }

    /**
     * Test village officer cannot access activity logs (BPS admin only)
     *
     * @test
     */
    public function village_officer_cannot_access_activity_logs(): void
    {
        $village = Village::factory()->create();
        $villageRole = UserRole::where('role_name', 'village_officer')->first();
        $officer = User::factory()->create([
            'role_id' => $villageRole->id,
            'village_id' => $village->id,
        ]);

        ActivityLog::factory()->count(5)->create(['village_id' => $village->id]);

        $this->actingAs($officer, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access activity logs
     *
     * @test
     */
    public function guest_cannot_access_activity_logs(): void
    {
        ActivityLog::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/activity-logs');

        $response->assertStatus(401);
    }

    /**
     * Test pagination maximum limit is enforced
     *
     * @test
     */
    public function pagination_maximum_limit_is_enforced(): void
    {
        $admin = $this->actingAsAdmin();

        ActivityLog::factory()->count(150)->create();

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/activity-logs?per_page=200');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 100); // Should be capped at 100
    }

    /**
     * Test multiple filters can be combined
     *
     * @test
     */
    public function multiple_filters_can_be_combined(): void
    {
        $admin = $this->actingAsAdmin();
        $village = Village::factory()->create();
        $user = User::factory()->create();

        ActivityLog::factory()->count(2)->create([
            'village_id' => $village->id,
            'user_id' => $user->id,
            'action' => 'create',
        ]);

        ActivityLog::factory()->create([
            'village_id' => $village->id,
            'user_id' => $user->id,
            'action' => 'update',
        ]);

        ActivityLog::factory()->create([
            'village_id' => $village->id,
            'action' => 'create',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/v1/activity-logs?village_id={$village->id}&user_id={$user->id}&action=create");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2);
    }
}
