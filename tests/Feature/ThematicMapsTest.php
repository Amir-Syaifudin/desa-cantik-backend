<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ThematicMapsTest extends TestCase
{
    use CreatesTestData;

    /**
     * Test admin can list geospatial data for a village
     * 
     * Tests the endpoint that replaced MOCK_GEOSPATIAL in PetaTematikAdmin.jsx
     */
    public function test_can_list_geospatial_data_for_village(): void
    {
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];

        $response = $this->getJson("/api/v1/villages/{$village->id}/geospatial");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'village_id',
                        'name',
                        'type',
                        'geometry',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data'); // 3 types: Polygon, Point, LineString
    }

    /**
     * Test admin can list thematic maps for a village
     * 
     * Tests the endpoint that replaced MOCK_LAYERS in PetaTematikAdmin.jsx
     */
    public function test_can_list_thematic_maps_for_village(): void
    {
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];

        $response = $this->getJson("/api/v1/villages/{$village->id}/thematic-maps");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'village_id',
                        'theme_name',
                        'description',
                        'icon',
                        'points_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data'); // 2 maps: Population and Education
    }

    /**
     * Test admin can create geospatial data
     * 
     * Tests the endpoint used in PetaTematikAdmin.jsx handleFormSubmit (line 236)
     */
    public function test_admin_can_create_geospatial_data(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/v1/villages/{$village->id}/geospatial", [
            'name' => 'Area Pertanian',
            'type' => 'Polygon',
            'geometry' => [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'properties' => ['name' => 'Sawah'],
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [119.89, -2.98],
                                    [119.90, -2.98],
                                    [119.90, -2.97],
                                    [119.89, -2.97],
                                    [119.89, -2.98],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Geospatial data created successfully',
            ]);
    }

    /**
     * Test admin can update geospatial data
     */
    public function test_admin_can_update_geospatial_data(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];
        $geoData = $testData['geospatial']['boundary'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/geospatial/{$geoData->id}", [
            'name' => 'Batas Wilayah Updated',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Geospatial data updated successfully',
            ]);
    }

    /**
     * Test admin can delete geospatial data
     * 
     * Tests the endpoint used in PetaTematikAdmin.jsx handleDelete (line 221)
     */
    public function test_admin_can_delete_geospatial_data(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];
        $geoData = $testData['geospatial']['river'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson("/api/v1/villages/{$village->id}/geospatial/{$geoData->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Geospatial data deleted successfully',
            ]);

        $this->assertDatabaseMissing('geospatial_data', [
            'id' => $geoData->id,
        ]);
    }

    /**
     * Test admin can create thematic map
     */
    public function test_admin_can_create_thematic_map(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];
        $geoData = $testData['geospatial']['boundary'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/v1/villages/{$village->id}/thematic-maps", [
            'theme_name' => 'Peta Kesehatan',
            'description' => 'Peta fasilitas kesehatan',
            'geospatial_data_id' => $geoData->id,
            'color' => '#00FF00',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test admin can update thematic map
     */
    public function test_admin_can_update_thematic_map(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];
        $map = $testData['maps']['population'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->putJson("/api/v1/villages/{$village->id}/thematic-maps/{$map->id}", [
            'name' => 'Peta Kepadatan Updated',
            'color' => '#AA0000',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test admin can delete thematic map
     */
    public function test_admin_can_delete_thematic_map(): void
    {
        $admin = $this->actingAsAdmin();
        $testData = $this->createTestVillageWithMaps();
        $village = $testData['village'];
        $map = $testData['maps']['education'];

        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson("/api/v1/villages/{$village->id}/thematic-maps/{$map->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('thematic_maps', [
            'id' => $map->id,
        ]);
    }

    /**
     * Test village officer can only manage their own village's maps
     */
    public function test_village_officer_can_only_manage_own_village_maps(): void
    {
        $village1 = $this->createTestVillageWithMaps()['village'];
        $village2 = $this->createTestVillageWithMaps()['village'];

        $officer1 = $this->actingAsVillageOfficer($village1);

        $this->actingAs($officer1, 'sanctum');

        // Can access own village
        $response = $this->getJson("/api/v1/villages/{$village1->id}/geospatial");
        $response->assertStatus(200);

        // Cannot create for other village
        $response = $this->postJson("/api/v1/villages/{$village2->id}/geospatial", [
            'name' => 'Test',
            'type' => 'Point',
            'geometry' => ['type' => 'FeatureCollection', 'features' => []],
        ]);
        $response->assertStatus(403);
    }
}
