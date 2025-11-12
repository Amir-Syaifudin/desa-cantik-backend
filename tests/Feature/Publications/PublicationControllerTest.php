<?php

namespace Tests\Feature\Publications;

use App\Models\Publication;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PublicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseRoles();
    }

    public function test_it_lists_publications_publicly(): void
    {
        $village = Village::factory()->create();
        $uploader = $this->createBpsAdmin();

        Publication::factory()->count(2)->create([
            'desa_id' => $village->id,
            'uploaded_by' => $uploader->id,
            'published_at' => '2024-01-10',
        ]);

        $response = $this->getJson("/api/v1/villages/{$village->id}/publications?year=2024");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_it_displays_publication_detail(): void
    {
        $village = Village::factory()->create();
        $uploader = $this->createBpsAdmin();
        $publication = Publication::factory()->create([
            'desa_id' => $village->id,
            'uploaded_by' => $uploader->id,
            'published_at' => '2024-02-01',
        ]);

        $response = $this->getJson("/api/v1/publications/{$publication->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $publication->id)
            ->assertJsonPath('data.village.id', $village->id);
    }

    public function test_bps_admin_can_upload_publication(): void
    {
        Storage::fake('public');
        $village = Village::factory()->create();
        $admin = $this->createBpsAdmin();

        Sanctum::actingAs($admin);

        $file = UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf');

        $response = $this->post(
            "/api/v1/villages/{$village->id}/publications",
            [
                'title' => 'Laporan Tahunan Desa',
                'description' => 'Laporan lengkap',
                'published_at' => '2024-12-31',
                'file' => $file,
            ],
            ['Accept' => 'application/json']
        );

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('publications', [
            'title' => 'Laporan Tahunan Desa',
            'desa_id' => $village->id,
        ]);
    }

    public function test_village_officer_can_replace_publication_file(): void
    {
        Storage::fake('public');
        $village = Village::factory()->create();
        $officer = $this->createVillageOfficer($village);
        Sanctum::actingAs($officer);

        $publication = Publication::factory()->create([
            'desa_id' => $village->id,
            'uploaded_by' => $officer->id,
            'file_path' => 'publications/old.pdf',
            'file_name' => 'old.pdf',
        ]);

        Storage::disk('public')->put($publication->file_path, 'old file content');

        $response = $this->post(
            "/api/v1/villages/{$village->id}/publications/{$publication->id}/replace-file",
            [
                'file' => UploadedFile::fake()->create('new.doc', 120, 'application/msword'),
            ],
            ['Accept' => 'application/json']
        );

        $response->assertOk()->assertJsonPath('message', 'File publikasi berhasil diganti');
        Storage::disk('public')->assertMissing('publications/old.pdf');

        $publication->refresh();
        $this->assertSame('doc', $publication->file_type);
    }

    public function test_download_endpoint_returns_file(): void
    {
        Storage::fake('public');
        $village = Village::factory()->create();
        $publication = Publication::factory()->create([
            'desa_id' => $village->id,
            'file_path' => 'publications/download.pdf',
            'file_name' => 'download.pdf',
        ]);

        Storage::disk('public')->put($publication->file_path, 'dummy');

        $response = $this->get("/api/v1/publications/{$publication->id}/download");

        $response->assertOk();
        $this->assertStringContainsString('download.pdf', $response->headers->get('content-disposition'));
    }

    protected function createBpsAdmin(): User
    {
        $role = UserRole::where('role_name', UserRole::BPS_ADMIN)->first();

        return User::factory()->for($role, 'role')->create();
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
            ['display_name' => 'Masyarakat']
        );
    }
}
