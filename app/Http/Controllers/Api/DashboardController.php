<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DashboardAccessDeniedException;
use App\Exceptions\InvalidDashboardRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\VillageDashboardRequest;
use App\Models\UserRole;
use App\Services\DashboardStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardStatisticsService $service) {}

    /**
     * Get BPS Admin Dashboard Statistics
     * 
     * @throws DashboardAccessDeniedException
     */
    public function admin(Request $request): JsonResponse
    {
        $this->authorizeRole(UserRole::BPS_ADMIN);

        return response()->json([
            'success' => true,
            'data' => $this->service->getAdminDashboard(),
        ]);
    }

    /**
     * Get Village Officer Dashboard Statistics
     * 
     * @throws DashboardAccessDeniedException
     * @throws InvalidDashboardRequestException
     */
    public function village(VillageDashboardRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            throw new DashboardAccessDeniedException('You must be authenticated to access the village dashboard');
        }

        $villageId = $request->getVillageId();

        if (! $villageId) {
            throw new InvalidDashboardRequestException(
                'Village ID is required. Village officers can only view their assigned village, ' .
                    'while BPS administrators must specify a village_id parameter.'
            );
        }

        $data = $this->service->getVillageDashboard($user, $villageId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get Public Dashboard Statistics (Landing Page)
     */
    public function public(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getPublicDashboard(),
        ]);
    }

    /**
     * Authorize user has one of the specified roles
     * 
     * @throws DashboardAccessDeniedException
     */
    protected function authorizeRole(string ...$roles): void
    {
        $user = auth()->user();

        if (! $user) {
            throw new DashboardAccessDeniedException('You must be authenticated to access this dashboard');
        }

        if (! in_array($user->role?->role_name, $roles, true)) {
            throw new DashboardAccessDeniedException(
                sprintf(
                    'Access denied. Required role: %s. Your role: %s',
                    implode(' or ', $roles),
                    $user->role?->role_name ?? 'none'
                )
            );
        }
    }
}
