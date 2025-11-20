<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplacePublicationFileRequest;
use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Models\User;
use App\Models\Village;
use App\Services\ActivityLogger;
use App\Services\PublicationService;
use App\Traits\AuthorizesVillageAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicationController extends Controller
{
    use AuthorizesVillageAccess;

    public function __construct(private PublicationService $publicationService) {}

    #[OA\Get(
        path: '/api/v1/villages/{village_id}/publications',
        summary: 'Get village publications',
        description: 'Returns paginated list of publications for a specific village with optional year filter. Includes file metadata and uploader information.',
        tags: ['Publications'],
        parameters: [
            new OA\Parameter(
                name: 'village_id',
                description: 'Village ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Items per page (max 100)',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100)
            ),
            new OA\Parameter(
                name: 'year',
                description: 'Filter by publication year',
                in: 'query',
                schema: new OA\Schema(type: 'integer', example: 2024)
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Publications retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Publication')),
                new OA\Property(property: 'meta', type: 'object', properties: [
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'per_page', type: 'integer', example: 15),
                    new OA\Property(property: 'total', type: 'integer', example: 78),
                    new OA\Property(property: 'last_page', type: 'integer', example: 6),
                ]),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Village not found',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function index(Request $request, Village $village): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;
        $year = $request->query('year');

        $publications = Publication::query()
            ->with(['uploader:id,full_name', 'village:id,name,village_code'])
            ->where('desa_id', $village->id)
            ->when($year, fn($query) => $query->whereYear('published_at', $year))
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => PublicationResource::collection($publications->getCollection()),
            'meta' => [
                'current_page' => $publications->currentPage(),
                'per_page' => $publications->perPage(),
                'total' => $publications->total(),
                'last_page' => $publications->lastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/publications/{id}',
        summary: 'Get publication detail',
        description: 'Returns detailed information about a specific publication including file metadata.',
        tags: ['Publications'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Publication ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 23)
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Publication retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', ref: '#/components/schemas/Publication'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Publication not found',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function show(Publication $publication): JsonResponse
    {
        $publication->loadMissing(['village:id,name,village_code', 'uploader:id,full_name']);

        return response()->json([
            'success' => true,
            'data' => PublicationResource::make($publication),
        ]);
    }

    #[OA\Post(
        path: '/api/v1/villages/{village_id}/publications',
        summary: 'Upload publication',
        description: 'Uploads a new publication file (PDF) for a village. Max 200MB. BPS Admins can upload to any village, Village Officers only to their own.',
        security: [['sanctum' => []]],
        tags: ['Publications'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'file', 'published_at'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Laporan Statistik Desa 2024'),
                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Laporan lengkap statistik desa'),
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'PDF file (max 200MB)'),
                        new OA\Property(property: 'published_at', type: 'string', format: 'date', example: '2024-12-15'),
                    ]
                )
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'village_id',
                description: 'Village ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
        ]
    )]
    #[OA\Response(
        response: 201,
        description: 'Publication uploaded successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Publikasi berhasil diunggah'),
                new OA\Property(property: 'data', ref: '#/components/schemas/Publication'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - Cannot upload to other villages',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error',
        content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
    )]
    public function store(StorePublicationRequest $request, Village $village): JsonResponse
    {
        $user = $this->user();
        $this->authorizeVillageAccess($village, $user);

        $fileMeta = $this->publicationService->storeFile($request->file('file'), $village);

        $status = $request->input('status');
        if ($status === 'Rilis') $status = 'Terverifikasi';
        if ($status === 'Diarsipkan') $status = 'Draft';

        $publication = Publication::create([
            'desa_id' => $village->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'published_at' => $request->input('published_at'),
            'category' => $request->input('category'),
            'status' => $status ?? 'Draft',
            'uploaded_by' => $user->id,
            'file_path' => $fileMeta['file_path'],
            'file_name' => $fileMeta['file_name'],
            'file_type' => $fileMeta['file_type'],
            'file_size_bytes' => $fileMeta['file_size_bytes'],
            'file_url' => Storage::disk('public')->url($fileMeta['file_path']),
        ]);

        $publication->load(['uploader:id,full_name']);

        ActivityLogger::log(
            'create',
            $publication,
            sprintf('Mengunggah publikasi %s', $publication->title),
            ['new_data' => $publication->toArray()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Publikasi berhasil diunggah',
            'data' => PublicationResource::make($publication),
        ], 201);
    }

    public function update(UpdatePublicationRequest $request, Village $village, Publication $publication): JsonResponse
    {
        $user = $this->user();
        $this->authorizeVillageAccess($village, $user);
        $this->ensurePublicationBelongsToVillage($publication, $village);

        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === 'Rilis') $data['status'] = 'Terverifikasi';
            if ($data['status'] === 'Diarsipkan') $data['status'] = 'Draft';
        }

        $original = $publication->toArray();
        $publication->fill($data);
        $publication->save();

        ActivityLogger::log(
            'update',
            $publication,
            sprintf('Memperbarui publikasi %s', $publication->title),
            [
                'old_data' => $original,
                'new_data' => $publication->toArray(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Publikasi berhasil diperbarui',
            'data' => PublicationResource::make($publication),
        ]);
    }

    public function replaceFile(ReplacePublicationFileRequest $request, Village $village, Publication $publication): JsonResponse
    {
        $user = $this->user();
        $this->authorizeVillageAccess($village, $user);
        $this->ensurePublicationBelongsToVillage($publication, $village);

        $original = $publication->only(['file_path', 'file_name']);
        $this->publicationService->deleteFile($publication->file_path);
        $fileMeta = $this->publicationService->storeFile($request->file('file'), $village);

        $publication->update([
            'file_path' => $fileMeta['file_path'],
            'file_name' => $fileMeta['file_name'],
            'file_type' => $fileMeta['file_type'],
            'file_size_bytes' => $fileMeta['file_size_bytes'],
            'file_url' => Storage::disk('public')->url($fileMeta['file_path']),
        ]);

        ActivityLogger::log(
            'update',
            $publication,
            sprintf('Mengganti file publikasi %s', $publication->title),
            [
                'old_data' => $original,
                'new_data' => $publication->only(['file_path', 'file_name']),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'File publikasi berhasil diganti',
            'data' => PublicationResource::make($publication),
        ]);
    }

    public function destroy(Village $village, Publication $publication): JsonResponse
    {
        $user = $this->user();
        $this->authorizeVillageAccess($village, $user);
        $this->ensurePublicationBelongsToVillage($publication, $village);

        $snapshot = $publication->toArray();

        ActivityLogger::log(
            'delete',
            $publication,
            sprintf('Menghapus publikasi %s', $snapshot['title'] ?? 'Publikasi'),
            ['old_data' => $snapshot]
        );

        $this->publicationService->deleteFile($publication->file_path);
        $publication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Publikasi berhasil dihapus',
        ]);
    }

    public function download(Publication $publication): StreamedResponse
    {
        if (! $publication->file_path || ! Storage::disk('public')->exists($publication->file_path)) {
            abort(404, 'File publikasi tidak ditemukan');
        }

        $mimeType = Storage::disk('public')->mimeType($publication->file_path) ?: 'application/octet-stream';

        return Storage::disk('public')->download(
            $publication->file_path,
            $publication->file_name ?? 'publication_' . $publication->id,
            [
                'Content-Type' => $mimeType,
            ]
        );
    }

    /**
     * Get publication metadata (categories and statuses)
     */
    #[OA\Get(
        path: '/api/v1/publications/metadata',
        summary: 'Get publication metadata',
        description: 'Returns available categories and statuses for publications',
        tags: ['Publications']
    )]
    #[OA\Response(
        response: 200,
        description: 'Metadata retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'statuses', type: 'array', items: new OA\Items(type: 'string')),
                ])
            ]
        )
    )]
    public function metadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => [
                    'Statistik Desa',
                    'Sosial',
                    'Ekonomi Lokal',
                    'Pemerintahan',
                    'Infrastruktur',
                    'Pendidikan',
                    'Kesehatan',
                ],
                'statuses' => [
                    'Draft',
                    'Perlu Validasi',
                    'Terverifikasi',
                    'Batal Terbit',
                ],
            ],
        ]);
    }

    protected function ensurePublicationBelongsToVillage(Publication $publication, Village $village): void
    {
        if ((int) $publication->desa_id !== (int) $village->id) {
            abort(404);
        }
    }

    protected function user(): User
    {
        $user = auth()->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        return $user;
    }
}
