<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticTypeResource;
use App\Models\StatisticType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatisticTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/statistic-types",
     *     summary="List active statistic types",
     *     description="Get list of active statistic types with optional category filter",
     *     tags={"Statistic Types"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $cacheKey = 'statistic_types'.($category ? "_{$category}" : '_all');
        $cacheTTL = 3600; // 1 hour

        $types = Cache::remember($cacheKey, $cacheTTL, function () use ($category) {
            return StatisticType::query()
                ->where('is_active', true)
                ->when($category, fn ($query) => $query->where('category', $category))
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
