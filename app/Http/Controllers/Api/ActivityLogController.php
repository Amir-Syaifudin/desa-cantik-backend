<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ActivityLogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs",
     *     tags={"Activity Logs"},
     *     summary="Get activity logs",
     *     description="Retrieve paginated activity logs (BPS Admin only)",
     *     security={{"sanctum": {}}},
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
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="village_id",
     *         in="query",
     *         description="Filter by village ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action type (create, update, delete)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model_type",
     *         in="query",
     *         description="Filter by model type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter from date (Y-m-d H:i:s)",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter to date (Y-m-d H:i:s)",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = ActivityLog::query()
            ->with(['user:id,username,full_name', 'village:id,name,code'])
            ->select([
                'id',
                'user_id',
                'village_id',
                'action',
                'model_type',
                'model_id',
                'description',
                'old_data',
                'new_data',
                'ip_address',
                'user_agent',
                'created_at'
            ]);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('village_id')) {
            $query->where('village_id', $request->query('village_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'LIKE', '%' . $request->query('model_type') . '%');
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->query('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->query('to_date'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity-logs/{id}",
     *     tags={"Activity Logs"},
     *     summary="Get activity log detail",
     *     description="Get detailed information about a specific activity log (BPS Admin only)",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Activity Log ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Log not found")
     * )
     */
    public function show($id): JsonResponse
    {
        $log = ActivityLog::with(['user:id,username,full_name,email', 'village:id,name,code,district'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'username' => $log->user->username,
                    'full_name' => $log->user->full_name,
                    'email' => $log->user->email,
                ] : null,
                'village' => $log->village ? [
                    'id' => $log->village->id,
                    'name' => $log->village->name,
                    'code' => $log->village->code,
                    'district' => $log->village->district,
                ] : null,
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'description' => $log->description,
                'old_data' => $log->old_data,
                'new_data' => $log->new_data,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at,
            ]
        ]);
    }
}
