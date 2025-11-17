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
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicationController extends Controller
{
    use AuthorizesVillageAccess;

    public function __construct(private PublicationService $publicationService) {}

    public function index(Request $request, Village $village): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;
        $year = $request->query('year');

        $publications = Publication::query()
            ->with(['uploader:id,full_name', 'village:id,name,village_code'])
            ->where('desa_id', $village->id)
            ->when($year, fn ($query) => $query->whereYear('published_at', $year))
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

    public function show(Publication $publication): JsonResponse
    {
        $publication->loadMissing(['village:id,name,village_code', 'uploader:id,full_name']);

        return response()->json([
            'success' => true,
            'data' => PublicationResource::make($publication),
        ]);
    }

    public function store(StorePublicationRequest $request, Village $village): JsonResponse
    {
        $user = $this->user();
        $this->authorizeVillageAccess($village, $user);

        $fileMeta = $this->publicationService->storeFile($request->file('file'), $village);

        $publication = Publication::create([
            'desa_id' => $village->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'published_at' => $request->input('published_at'),
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
            $publication->file_name ?? 'publication_'.$publication->id,
            [
                'Content-Type' => $mimeType,
            ]
        );
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
