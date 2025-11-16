<?php

namespace Tests\Feature\Village;

use App\Models\Village;
use App\Models\VillageProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VillageEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_villages_index_returns_frontend_ready_payload(): void
    {
        $village = Village::factory()->create([
            'name' => 'Test Village',
            'kecamatan' => 'Test District',
            'kabupaten' => 'Test Regency',
            'provinsi' => 'Test Province',
        ]);

        VillageProfile::factory()
            ->for($village)
            ->state([
                'population' => 1500,
                'households' => 300,
                'male_population' => 750,
                'female_population' => 750,
                'area' => 12.5,
                'thumbnail_url' => 'https://example.com/thumb.jpg',
            ])
            ->create();

        $response = $this->getJson('/api/villages');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJson([
            [
                'id' => (string) $village->id,
                'name' => 'Test Village',
                'district' => 'Test District',
                'regency' => 'Test Regency',
                'province' => 'Test Province',
                'population' => 1500,
                'status' => 'Aktif',
                'image' => 'https://example.com/thumb.jpg',
                'area' => 12.5,
                'households' => 300,
                'malePopulation' => 750,
                'femalePopulation' => 750,
            ],
        ]);
    }

    public function test_village_detail_returns_formatted_payload(): void
    {
        $village = Village::factory()->create([
            'is_visible' => false,
            'logo_url' => null,
        ]);

        VillageProfile::factory()
            ->for($village)
            ->state([
                'population' => 2000,
                'households' => null,
                'male_population' => null,
                'female_population' => null,
                'area' => null,
                'thumbnail_url' => null,
                'foto_url' => null,
            ])
            ->create();

        $response = $this->getJson("/api/villages/{$village->id}");

        $response->assertOk();
        $response->assertJson([
            'id' => (string) $village->id,
            'name' => $village->name,
            'district' => $village->kecamatan,
            'regency' => $village->kabupaten,
            'province' => $village->provinsi,
            'population' => 2000,
            'status' => 'Tidak Aktif',
            'image' => 'https://placehold.co/800x600/1C6EA4/FFFFFF?text=Desa+Cantik',
            'area' => 1.0,
            'households' => 0,
            'malePopulation' => 0,
            'femalePopulation' => 0,
        ]);
    }

    public function test_village_detail_returns_404_for_missing_record(): void
    {
        $this->getJson('/api/villages/999')->assertNotFound();
    }
}
