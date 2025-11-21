<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publication; // <--- PENTING: Import Model Publication
use App\Models\Village;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class VillageController extends Controller
{
    #[OA\Get(
        path: '/api/v1/villages',
        summary: 'Get all villages',
        description: 'Returns a paginated list of villages with optional search and active status filters. Includes village profiles with population and area data. Publicly accessible.',
        tags: ['Villages'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number for pagination',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page (max 100)',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100)
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Search by village name, district (kecamatan), or regency (kabupaten)',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Nonongan')
            ),
            new OA\Parameter(
                name: 'is_active',
                description: 'Filter by active status (use "all" to show both active and inactive)',
                in: 'query',
                schema: new OA\Schema(type: 'string', default: 'true', enum: ['true', 'false', 'all'])
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Villages retrieved successfully',
        content: new OA\JsonContent(ref: '#/components/schemas/VillagesResponse')
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = Village::query()
            ->with(['profile:id,village_id,deskripsi,area,population,households,male_population,female_population,population_density,thumbnail_url,logo_url'])
            ->select(['id', 'village_code', 'name', 'kecamatan', 'kabupaten', 'provinsi', 'logo_url', 'is_visible', 'created_at', 'updated_at']);

        // Filter by active status (default true for public)
        $isVisible = $request->query('is_active', 'true');
        if ($isVisible !== 'all') {
            $query->where('is_visible', filter_var($isVisible, FILTER_VALIDATE_BOOLEAN));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('kecamatan', 'LIKE', "%{$search}%")
                    ->orWhere('kabupaten', 'LIKE', "%{$search}%");
            });
        }

        /** @var LengthAwarePaginator $villages */
        $villages = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($villages->items())->map(fn(Village $village) => $this->mapVillageToFrontendPayload($village))->values(),
            'meta' => [
                'current_page' => $villages->currentPage(),
                'per_page' => $villages->perPage(),
                'total' => $villages->total(),
                'last_page' => $villages->lastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/villages/{id}',
        summary: 'Get village detail',
        description: 'Returns detailed information about a specific village including full profile data (description, vision, mission, demographics, contact). Publicly accessible.',
        tags: ['Villages'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Village ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Village detail retrieved successfully',
        content: new OA\JsonContent(ref: '#/components/schemas/VillageDetailResponse')
    )]
    #[OA\Response(
        response: 404,
        description: 'Village not found',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function show($id): JsonResponse
    {
        $village = Village::with([
            'profile:id,village_id,deskripsi,visi,misi,area,population,households,male_population,female_population,population_density,address,phone,email,website,logo_url,thumbnail_url',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->mapVillageToFrontendPayload($village, true),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/villages',
        tags: ['Villages'],
        summary: 'Create village (BPS Admin only)',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Village created'), new OA\Response(response: 422, description: 'Validation error')]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:villages,village_code',
            'name' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'subdistrict' => 'sometimes|string|max:255',
            'display_order' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $village = Village::create([
            'village_code' => $request->code,
            'name' => $request->name,
            'kecamatan' => $request->district,
            'kabupaten' => $request->subdistrict ?? 'Toraja Utara',
            'provinsi' => $request->province ?? 'Sulawesi Selatan',
            'is_visible' => true,
        ]);

        ActivityLogger::log('create', $village, 'Village created');

        return response()->json([
            'success' => true,
            'message' => 'Village created successfully',
            'data' => [
                'id' => $village->id,
                'code' => $village->village_code,
                'name' => $village->name,
                'district' => $village->district,
                'subdistrict' => $village->subdistrict,
                'is_active' => $village->is_visible,
                'display_order' => $village->display_order,
            ],
        ], 201);
    }

    #[OA\Put(
        path: '/api/v1/villages/{id}',
        tags: ['Villages'],
        summary: 'Update village (BPS Admin only)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Village updated'), new OA\Response(response: 404, description: 'Village not found')]
    )]
    public function update(Request $request, $id): JsonResponse
    {
        $village = Village::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|max:20|unique:villages,village_code,' . $id,
            'name' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'subdistrict' => 'sometimes|string|max:255',
            'province' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldData = $village->toArray();

        if ($request->has('code')) {
            $village->village_code = $request->code;
        }
        if ($request->has('name')) {
            $village->name = $request->name;
        }
        if ($request->has('district')) {
            $village->kecamatan = $request->district;
        }
        if ($request->has('subdistrict')) {
            $village->kabupaten = $request->subdistrict;
        }
        if ($request->has('province')) {
            $village->provinsi = $request->province;
        }

        $village->save();

        ActivityLogger::log('update', $village, 'Village updated', [
            'old_data' => $oldData,
            'new_data' => $village->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Village updated successfully',
            'data' => [
                'id' => $village->id,
                'code' => $village->village_code,
                'name' => $village->name,
                'district' => $village->district,
                'subdistrict' => $village->subdistrict,
                'is_active' => $village->is_visible,
                'display_order' => $village->display_order,
            ],
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/villages/{id}',
        tags: ['Villages'],
        summary: 'Delete village (BPS Admin only)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Village deleted'), new OA\Response(response: 404, description: 'Village not found')]
    )]
    public function destroy($id): JsonResponse
    {
        $village = Village::findOrFail($id);

        // Check if village has associated users
        if ($village->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete village with associated users',
            ], 422);
        }

        ActivityLogger::log('delete', $village, 'Village deleted', [
            'old_data' => $village->toArray(),
        ]);

        $village->delete();

        return response()->json([
            'success' => true,
            'message' => 'Village deleted successfully',
        ]);
    }

    #[OA\Put(
        path: '/api/v1/villages/{id}/toggle-status',
        tags: ['Villages'],
        summary: 'Toggle village active status (BPS Admin only)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Status toggled')]
    )]
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        $village = Village::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $village->is_visible;
        $village->is_visible = $request->is_active;
        $village->save();

        ActivityLogger::log('update', $village, 'Village status toggled', [
            'old_data' => ['is_active' => $oldStatus],
            'new_data' => ['is_active' => $village->is_visible],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Village status updated successfully',
            'data' => [
                'id' => $village->id,
                'name' => $village->name,
                'is_active' => $village->is_visible,
            ],
        ]);
    }

    // --- START: METHOD BARU DOKUMENTASI (DIPERBAIKI) ---
    #[OA\Get(
        path: '/api/v1/villages/{id}/documentation',
        summary: 'Get village documentation',
        description: 'Returns a list of images including village profile image and publication images.',
        tags: ['Villages'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Village ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Documentation images retrieved successfully'
    )]
    public function documentation($id): JsonResponse
    {
        $village = Village::with(['profile'])->find($id);

        if (!$village) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan',
            ], 404);
        }

        $images = [];

        // 1. Ambil gambar utama desa (Profil/Logo)
        $profile = $village->profile;
        $mainImage = $profile?->thumbnail_url
            ?? $profile?->logo_url
            ?? $village->logo_url;

        if ($mainImage) {
            $images[] = [
                'id' => 'profile-' . $village->id,
                'type' => 'profile',
                'title' => 'Profil Desa ' . $village->name,
                'image_url' => $mainImage,
                'created_at' => $village->created_at,
            ];
        }

        // 2. Ambil Publikasi (Hanya yang berupa GAMBAR)
        // Menggunakan kolom 'desa_id' (bukan village_id) dan memfilter file_type
        $publications = Publication::where('desa_id', $id)
            ->where(function($q) {
                // Filter hanya file gambar
                $q->where('file_type', 'LIKE', 'image/%')
                  ->orWhere('file_name', 'LIKE', '%.jpg')
                  ->orWhere('file_name', 'LIKE', '%.jpeg')
                  ->orWhere('file_name', 'LIKE', '%.png');
            })
            ->latest()
            ->get();

        foreach ($publications as $pub) {
            // Gunakan accessor download_url atau file_url legacy
            $imageUrl = $pub->download_url ?? $pub->file_url;

            if ($imageUrl) {
                $images[] = [
                    'id' => 'pub-' . $pub->id,
                    'type' => 'publication',
                    'title' => $pub->title,
                    'image_url' => $imageUrl,
                    'created_at' => $pub->created_at,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }
    // --- END: METHOD BARU DOKUMENTASI ---

    protected function mapVillageToFrontendPayload(Village $village, bool $detailed = false): array
    {
        $profile = $village->profile;

        $payload = [
            'id' => (string) $village->id,
            'name' => $village->name,
            'district' => $village->kecamatan,
            'regency' => $village->kabupaten,
            'province' => $village->provinsi,
            'population' => $profile?->population ?? 0,
            'status' => $village->is_visible ? 'Aktif' : 'Tidak Aktif',
            'image' => $profile?->thumbnail_url
                ?? $profile?->logo_url
                ?? $village->logo_url
                ?? 'https://placehold.co/800x600/1C6EA4/FFFFFF?text=Desa+Cantik',
            'area' => $profile?->area ?? 0.0,
            'households' => $profile?->households ?? 0,
            'malePopulation' => $profile?->male_population ?? 0,
            'femalePopulation' => $profile?->female_population ?? 0,
        ];

        if ($detailed) {
            $payload['description'] = $profile?->deskripsi;
            $payload['vision'] = $profile?->visi;
            $payload['mission'] = $profile?->misi;
            $payload['address'] = $profile?->address;
            $payload['phone'] = $profile?->phone;
            $payload['email'] = $profile?->email;
            $payload['website'] = $profile?->website;
            $payload['updated_at'] = $village->updated_at;
        }

        return $payload;
    }
}