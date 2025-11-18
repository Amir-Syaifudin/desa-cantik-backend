<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Village;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class VillageModuleController extends Controller
{
    /**
     * Get all modules for a village (BPS Admin only)
     */
    #[OA\Get(
        path: '/api/v1/villages/{village_id}/modules',
        summary: 'Get village modules',
        description: 'Returns all available modules for a village with their enabled/disabled status. Requires BPS Admin authentication.',
        security: [['sanctum' => []]],
        tags: ['Village Modules'],
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
        response: 200,
        description: 'Modules retrieved successfully',
        content: new OA\JsonContent(ref: '#/components/schemas/VillageModulesResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthenticated',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - BPS Admin required',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 404,
        description: 'Village not found',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function index($villageId): JsonResponse
    {
        $village = Village::findOrFail($villageId);

        $modules = Module::where('village_id', $village->id)
            ->get()
            ->map(function ($module) {
                return [
                    'id' => $module->id,
                    'village_id' => $module->village_id,
                    'module_name' => $module->name,
                    'is_enabled' => $module->status === 'active',
                    'created_at' => $module->created_at,
                    'updated_at' => $module->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * Toggle module status (BPS Admin only)
     */
    #[OA\Patch(
        path: '/api/v1/villages/{village_id}/modules/{module_name}',
        summary: 'Toggle village module',
        description: 'Enables or disables a specific module for a village. Creates the module if it doesn\'t exist. BPS Admin only.',
        security: [['sanctum' => []]],
        tags: ['Village Modules'],
        parameters: [
            new OA\Parameter(
                name: 'village_id',
                description: 'Village ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\Parameter(
                name: 'module_name',
                description: 'Module name (e.g., "Profil Desa", "Laporan Statistik", "Publikasi")',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'Profil Desa')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ToggleModuleRequest')
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Module status updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Module status updated successfully'),
                new OA\Property(property: 'data', ref: '#/components/schemas/VillageModule'),
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
        description: 'Forbidden - BPS Admin required',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 404,
        description: 'Village not found',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error',
        content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
    )]
    public function toggle(Request $request, $villageId, $moduleName): JsonResponse
    {
        $village = Village::findOrFail($villageId);

        $validator = Validator::make($request->all(), [
            'is_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find or create module
        $module = Module::firstOrNew([
            'village_id' => $village->id,
            'name' => $moduleName,
        ]);

        $oldStatus = $module->status;
        $module->status = $request->is_enabled ? 'active' : 'inactive';
        $module->save();

        ActivityLogger::log('update', $module, 'Village module toggled', [
            'old_data' => ['status' => $oldStatus],
            'new_data' => ['status' => $module->status],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Module status updated successfully',
            'data' => [
                'id' => $module->id,
                'village_id' => $module->village_id,
                'module_name' => $module->name,
                'is_enabled' => $module->status === 'active',
            ],
        ]);
    }
}
