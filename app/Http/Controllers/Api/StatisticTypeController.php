<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticTypeResource;
use App\Models\StatisticType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class StatisticTypeController extends Controller
{
    #[OA\Get(
        path: '/api/v1/statistic-types',
        summary: 'List active statistic types',
        description: 'Get list of active statistic types with optional category filter',
        tags: ['Statistic Types'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', description: 'Filter by category', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
            ])),
        ],
    )]
    public function index(Request $request)
    {
        $category = $request->query('category');
        $cacheKey = 'statistic_types' . ($category ? "_{$category}" : '_all');
        $cacheTTL = 3600; // 1 hour

        $types = Cache::remember($cacheKey, $cacheTTL, function () use ($category) {
            return StatisticType::query()
                ->where('is_active', true)
                ->when($category, fn($query) => $query->where('category', $category))
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => StatisticTypeResource::collection($types),
        ]);
    }
}
