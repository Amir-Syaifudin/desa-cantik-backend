<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Village;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class VillageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/villages",
     *     tags={"Villages"},
     *     summary="Get all villages (Public)",
     *     description="Retrieve paginated list of villages with optional filters",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or district",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = Village::query()
            ->with(['profile:id,village_id,description,population,area'])
            ->select(['id', 'code', 'name', 'district', 'subdistrict', 'is_active', 'display_order', 'created_at', 'updated_at']);

        // Filter by active status (default true for public)
        $isActive = $request->query('is_active', 'true');
        if ($isActive !== 'all') {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('district', 'LIKE', "%{$search}%");
            });
        }

        $villages = $query->orderBy('display_order')->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $villages->items(),
            'meta' => [
                'current_page' => $villages->currentPage(),
                'per_page' => $villages->perPage(),
                'total' => $villages->total(),
                'last_page' => $villages->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/villages/{id}",
     *     tags={"Villages"},
     *     summary="Get village detail (Public)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Village not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $village = Village::with([
            'profile:id,village_id,description,vision,mission,area,population,population_density,address,phone,email,website,logo_url'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $village->id,
                'code' => $village->code,
                'name' => $village->name,
                'district' => $village->district,
                'subdistrict' => $village->subdistrict,
                'is_active' => $village->is_active,
                'display_order' => $village->display_order,
                'profile' => $village->profile ? [
                    'description' => $village->profile->description,
                    'vision' => $village->profile->vision,
                    'mission' => $village->profile->mission,
                    'area' => $village->profile->area,
                    'population' => $village->profile->population,
                    'population_density' => $village->profile->population_density,
                    'address' => $village->profile->address,
                    'phone' => $village->profile->phone,
                    'email' => $village->profile->email,
                    'website' => $village->profile->website,
                    'logo_url' => $village->profile->logo_url,
                ] : null,
                'created_at' => $village->created_at,
                'updated_at' => $village->updated_at,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/villages",
     *     tags={"Villages"},
     *     summary="Create village (BPS Admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=201, description="Village created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:villages',
            'name' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'subdistrict' => 'sometimes|string|max:255',
            'display_order' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $village = Village::create([
            'code' => $request->code,
            'name' => $request->name,
            'district' => $request->district,
            'subdistrict' => $request->subdistrict ?? 'Toraja Utara',
            'is_active' => true,
            'display_order' => $request->display_order ?? 0,
        ]);

        ActivityLogger::log('create', $village, 'Village created');

        return response()->json([
            'success' => true,
            'message' => 'Village created successfully',
            'data' => [
                'id' => $village->id,
                'code' => $village->code,
                'name' => $village->name,
                'district' => $village->district,
                'subdistrict' => $village->subdistrict,
                'is_active' => $village->is_active,
                'display_order' => $village->display_order,
            ]
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/villages/{id}",
     *     tags={"Villages"},
     *     summary="Update village (BPS Admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Village updated"),
     *     @OA\Response(response=404, description="Village not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $village = Village::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|max:20|unique:villages,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'subdistrict' => 'sometimes|string|max:255',
            'display_order' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldData = $village->toArray();

        if ($request->has('code')) $village->code = $request->code;
        if ($request->has('name')) $village->name = $request->name;
        if ($request->has('district')) $village->district = $request->district;
        if ($request->has('subdistrict')) $village->subdistrict = $request->subdistrict;
        if ($request->has('display_order')) $village->display_order = $request->display_order;

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
                'code' => $village->code,
                'name' => $village->name,
                'district' => $village->district,
                'subdistrict' => $village->subdistrict,
                'is_active' => $village->is_active,
                'display_order' => $village->display_order,
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/villages/{id}",
     *     tags={"Villages"},
     *     summary="Delete village (BPS Admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Village deleted"),
     *     @OA\Response(response=404, description="Village not found")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $village = Village::findOrFail($id);

        // Check if village has associated users
        if ($village->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete village with associated users'
            ], 422);
        }

        ActivityLogger::log('delete', $village, 'Village deleted', [
            'old_data' => $village->toArray(),
        ]);

        $village->delete();

        return response()->json([
            'success' => true,
            'message' => 'Village deleted successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/villages/{id}/toggle-status",
     *     tags={"Villages"},
     *     summary="Toggle village active status (BPS Admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Status toggled")
     * )
     */
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
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $village->is_active;
        $village->is_active = $request->is_active;
        $village->save();

        ActivityLogger::log('update', $village, 'Village status toggled', [
            'old_data' => ['is_active' => $oldStatus],
            'new_data' => ['is_active' => $village->is_active],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Village status updated successfully',
            'data' => [
                'id' => $village->id,
                'name' => $village->name,
                'is_active' => $village->is_active,
            ]
        ]);
    }
}
