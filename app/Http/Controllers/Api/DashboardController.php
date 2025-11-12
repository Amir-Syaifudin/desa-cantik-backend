<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Services\DashboardStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatisticsService $service)
    {
    }

    public function admin(Request $request): JsonResponse
    {
        $this->authorizeRole(UserRole::BPS_ADMIN);

        return response()->json([
            'success' => true,
            'data' => $this->service->getAdminDashboard(),
        ]);
    }

    public function village(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $villageId = $request->query('village_id');

        if ($user->role?->role_name !== UserRole::BPS_ADMIN) {
            $villageId = $user->desa_id;
        }

        if (! $villageId) {
            abort(422, 'village_id is required');
        }

        $data = $this->service->getVillageDashboard($user, (int) $villageId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function public(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getPublicDashboard(),
        ]);
    }

    protected function authorizeRole(string ...$roles): void
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role?->role_name, $roles, true)) {
            abort(403, 'Forbidden');
        }
    }
}
