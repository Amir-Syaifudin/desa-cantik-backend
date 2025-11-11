<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticTypeResource;
use App\Models\StatisticType;
use Illuminate\Http\Request;

class StatisticTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = StatisticType::query()
            ->where('is_active', true)
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->query('category')))
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => StatisticTypeResource::collection($types),
        ]);
    }
}
