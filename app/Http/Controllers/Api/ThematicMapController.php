<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThematicMap;
use App\Models\Village;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThematicMapController extends Controller
{
    /**
     * Get all thematic maps for a village (Public)
     */
    public function index($villageId): JsonResponse
    {
        $village = Village::findOrFail($villageId);

        $maps = ThematicMap::where('desa_id', $village->id)
            ->get()
            ->map(function ($map) {
                $config = $map->layer_config ?? [];
                return [
                    'id' => $map->id,
                    'village_id' => $map->desa_id,
                    'name' => $map->map_name,
                    'geoId' => $config['geospatial_data_id'] ?? null,
                    'color' => $config['color'] ?? '#000000',
                    'isVisible' => (bool) $map->is_active,
                    'theme_name' => $map->map_name,
                    'description' => $map->description,
                    'icon' => $map->map_type,
                    'points_count' => $map->mapPoints()->count(),
                    'created_at' => $map->created_at,
                    'updated_at' => $map->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $maps,
        ]);
    }

    /**
     * Get thematic map detail with points (Public)
     */
    public function show($mapId): JsonResponse
    {
        $map = ThematicMap::with(['mapPoints', 'village:id,name,code'])->findOrFail($mapId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $map->id,
                'village' => [
                    'id' => $map->village->id,
                    'name' => $map->village->name,
                    'code' => $map->village->code,
                ],
                'theme_name' => $map->map_name,
                'description' => $map->description,
                'icon' => $map->map_type,
                'points' => $map->mapPoints->map(function ($point) {
                    return [
                        'id' => $point->id,
                        'name' => $point->name,
                        'description' => $point->description,
                        'category' => $point->category,
                        'latitude' => $point->latitude,
                        'longitude' => $point->longitude,
                        'image_url' => $point->icon_url,
                        'additional_info' => $point->metadata,
                    ];
                }),
                'created_at' => $map->created_at,
                'updated_at' => $map->updated_at,
            ],
        ]);
    }

    /**
     * Create thematic map (Auth required)
     */
    public function store(Request $request, $villageId): JsonResponse
    {
        $village = Village::findOrFail($villageId);

        // Check authorization
        $user = $request->user();
        $userRole = $user->role?->role_name;

        if ($userRole === 'village_officer' && $user->village_id !== $village->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create thematic map for this village',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'geoId' => 'required|integer',
            'color' => 'required|string',
            'isVisible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $map = ThematicMap::create([
            'desa_id' => $village->id,
            'map_name' => $request->name,
            'description' => $request->description,
            'map_type' => 'layer',
            'is_active' => $request->input('isVisible', true),
            'layer_config' => [
                'geospatial_data_id' => $request->geoId,
                'color' => $request->color,
            ],
            'created_by' => $user->id,
        ]);

        ActivityLogger::log('create', $map, 'Thematic map created');

        return response()->json([
            'success' => true,
            'message' => 'Thematic map created successfully',
            'data' => [
                'id' => $map->id,
                'village_id' => $map->desa_id,
                'name' => $map->map_name,
                'geoId' => $request->geoId,
                'color' => $request->color,
                'isVisible' => $map->is_active,
            ],
        ], 201);
    }

    /**
     * Update thematic map (Auth required)
     */
    public function update(Request $request, $villageId, $mapId): JsonResponse
    {
        $village = Village::findOrFail($villageId);
        $map = ThematicMap::where('desa_id', $village->id)->findOrFail($mapId);

        // Check authorization
        $user = $request->user();
        $userRole = $user->role?->role_name;

        if ($userRole === 'village_officer' && $user->village_id !== $village->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this thematic map',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'geoId' => 'sometimes|integer',
            'color' => 'sometimes|string',
            'isVisible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldData = $map->toArray();

        if ($request->has('name')) {
            $map->map_name = $request->name;
        }
        if ($request->has('isVisible')) {
            $map->is_active = $request->isVisible;
        }

        $config = $map->layer_config ?? [];
        if ($request->has('geoId')) {
            $config['geospatial_data_id'] = $request->geoId;
        }
        if ($request->has('color')) {
            $config['color'] = $request->color;
        }
        $map->layer_config = $config;

        $map->save();

        ActivityLogger::log('update', $map, 'Thematic map updated', [
            'old_data' => $oldData,
            'new_data' => $map->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thematic map updated successfully',
            'data' => [
                'id' => $map->id,
                'village_id' => $map->desa_id,
                'name' => $map->map_name,
                'geoId' => $config['geospatial_data_id'] ?? null,
                'color' => $config['color'] ?? null,
                'isVisible' => $map->is_active,
            ],
        ]);
    }

    /**
     * Delete thematic map (Auth required)
     */
    public function destroy(Request $request, $villageId, $mapId): JsonResponse
    {
        $village = Village::findOrFail($villageId);
        $map = ThematicMap::where('desa_id', $village->id)->findOrFail($mapId);

        // Check authorization
        $user = $request->user();
        $userRole = $user->role?->role_name;

        if ($userRole === 'village_officer' && $user->village_id !== $village->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this thematic map',
            ], 403);
        }

        ActivityLogger::log('delete', $map, 'Thematic map deleted', [
            'old_data' => $map->toArray(),
        ]);

        // Cascade delete all map points
        $map->mapPoints()->delete();
        $map->delete();

        return response()->json([
            'success' => true,
            'message' => 'Thematic map deleted successfully',
        ]);
    }
}
