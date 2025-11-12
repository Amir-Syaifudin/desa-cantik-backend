<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Publication;
use App\Models\User;
use App\Models\Village;
use App\Models\VillageProfile;
use App\Models\VillageStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DashboardStatisticsService
{
    public function getAdminDashboard(): array
    {
        return Cache::remember('dashboard:admin', config('dashboard.cache_ttl'), function () {
            return [
                'summary' => $this->adminSummary(),
                'recent_activities' => $this->recentActivities(),
                'villages_statistics' => $this->villageStatisticsOverview(),
                'monthly_activities' => $this->monthlyActivities(),
            ];
        });
    }

    public function getVillageDashboard(User $user, ?int $villageId = null): array
    {
        $targetVillageId = $villageId ?? $user->desa_id;

        if (! $targetVillageId) {
            throw new RuntimeException('Village context is required.');
        }

        $cacheKey = sprintf('dashboard:village:%d', $targetVillageId);

        return Cache::remember($cacheKey, config('dashboard.cache_ttl'), function () use ($targetVillageId) {
            $village = Village::with(['profile', 'statistics', 'publications', 'thematicMaps', 'mapPoints'])
                ->findOrFail($targetVillageId);

            return [
                'village' => [
                    'id' => $village->id,
                    'name' => $village->nama_desa,
                    'code' => $village->kode_desa,
                ],
                'summary' => $this->villageSummary($village),
                'recent_activities' => $this->recentActivities($village->id),
                'statistics_by_category' => $this->statisticsByCategory($village),
                'profile_completeness' => $this->profileCompleteness($village->profile),
            ];
        });
    }

    public function getPublicDashboard(): array
    {
        return Cache::remember('dashboard:public', config('dashboard.cache_ttl'), function () {
            return [
                'summary' => $this->publicSummary(),
                'featured_villages' => $this->featuredVillages(),
                'latest_publications' => $this->latestPublications(),
                'statistics_overview' => $this->publicStatisticsOverview(),
            ];
        });
    }

    protected function adminSummary(): array
    {
        $totalVillages = Village::count();
        $activeVillages = Village::where('is_visible', true)->count();
        $inactiveVillages = $totalVillages - $activeVillages;

        return [
            'total_villages' => $totalVillages,
            'active_villages' => $activeVillages,
            'inactive_villages' => $inactiveVillages,
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_statistics' => VillageStatistic::count(),
            'total_publications' => Publication::count(),
            'total_thematic_maps' => DB::table('thematic_maps')->count(),
        ];
    }

    protected function recentActivities(?int $villageId = null): array
    {
        return ActivityLog::query()
            ->when($villageId, fn($query) => $query->where('village_id', $villageId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->with('user:id,full_name,username')
            ->get()
            ->map(fn(ActivityLog $log) => [
                'id' => $log->id,
                'user' => $log->user?->full_name ?? $log->user?->username ?? 'System',
                'action' => $log->action,
                'description' => $log->description,
                'timestamp' => $log->created_at?->toISOString(),
            ])
            ->toArray();
    }

    protected function villageStatisticsOverview(): array
    {
        $villages = Village::query()
            ->select(['id', 'nama_desa'])
            ->withCount(['statistics', 'publications'])
            ->withMax('statistics', 'updated_at')
            ->orderByDesc('statistics_count')
            ->limit(10)
            ->get();

        return $villages->map(function (Village $village) {
            $statisticsUpdatedAt = $village->statistics_max_updated_at;
            $publicationsUpdatedAt = $village->publications_max_updated_at ?? null;
            $lastUpdated = collect([$statisticsUpdatedAt, $publicationsUpdatedAt])
                ->filter()
                ->map(fn($value) => Carbon::parse($value))
                ->max();

            return [
                'village_name' => $village->nama_desa,
                'statistics_count' => $village->statistics_count,
                'publications_count' => $village->publications_count,
                'last_updated' => $lastUpdated?->toISOString(),
            ];
        })->toArray();
    }

    protected function monthlyActivities(): array
    {
        $statisticModel = addslashes(VillageStatistic::class);
        $publicationModel = addslashes(Publication::class);

        $rows = ActivityLog::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN model_type = '{$statisticModel}' AND action = 'create' THEN 1 ELSE 0 END) as statistics_created")
            ->selectRaw("SUM(CASE WHEN model_type = '{$statisticModel}' AND action = 'update' THEN 1 ELSE 0 END) as statistics_updated")
            ->selectRaw("SUM(CASE WHEN model_type = '{$publicationModel}' AND action = 'create' THEN 1 ELSE 0 END) as publications_uploaded")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return $rows->map(fn($row) => [
            'month' => $row->month,
            'statistics_created' => (int) $row->statistics_created,
            'statistics_updated' => (int) $row->statistics_updated,
            'publications_uploaded' => (int) $row->publications_uploaded,
        ])->toArray();
    }

    protected function villageSummary(Village $village): array
    {
        $currentYear = now()->year;

        $statisticsThisYear = $village->statistics()
            ->where('year', $currentYear)
            ->count();

        $publicationsThisYear = $village->publications()
            ->whereYear('published_at', $currentYear)
            ->count();

        $latestUpdate = collect([
            $village->statistics()->max('updated_at'),
            $village->publications()->max('updated_at'),
            $village->thematicMaps()->max('updated_at'),
        ])->filter()->map(fn($timestamp) => Carbon::parse($timestamp))->max();

        return [
            'total_statistics' => $village->statistics->count(),
            'statistics_this_year' => $statisticsThisYear,
            'total_publications' => $village->publications->count(),
            'publications_this_year' => $publicationsThisYear,
            'thematic_maps' => $village->thematicMaps->count(),
            'map_points' => $village->mapPoints()->count(),
            'last_update' => $latestUpdate?->toISOString(),
        ];
    }

    protected function statisticsByCategory(Village $village): array
    {
        $rows = $village->statistics()
            ->selectRaw('statistic_types.category, COUNT(*) as count')
            ->join('statistic_types', 'statistic_types.id', '=', 'village_statistics.statistic_type_id')
            ->groupBy('statistic_types.category')
            ->get();

        return $rows->map(fn($row) => [
            'category' => $row->category,
            'count' => (int) $row->count,
        ])->toArray();
    }

    protected function profileCompleteness(?VillageProfile $profile): array
    {
        if (! $profile) {
            return [
                'percentage' => 0,
                'missing_fields' => config('dashboard.profile_required_fields'),
            ];
        }

        $requiredFields = config('dashboard.profile_required_fields');
        $missing = [];

        foreach ($requiredFields as $field) {
            if (empty($profile->{$field})) {
                $missing[] = $field;
            }
        }

        $filled = count($requiredFields) - count($missing);
        $percentage = count($requiredFields) > 0
            ? (int) round(($filled / count($requiredFields)) * 100)
            : 100;

        return [
            'percentage' => $percentage,
            'missing_fields' => $missing,
        ];
    }

    protected function publicSummary(): array
    {
        return [
            'total_villages' => Village::where('is_visible', true)->count(),
            'total_statistics' => VillageStatistic::count(),
            'total_publications' => Publication::count(),
            'last_updated' => VillageStatistic::max('updated_at')?->toISOString(),
        ];
    }

    protected function featuredVillages(): array
    {
        return VillageProfile::query()
            ->where('is_featured', true)
            ->with('village:id,nama_desa,kecamatan')
            ->limit(5)
            ->get()
            ->map(function (VillageProfile $profile) {
                return [
                    'id' => $profile->village?->id,
                    'name' => $profile->village?->nama_desa,
                    'district' => $profile->village?->kecamatan,
                    'population' => $profile->population,
                    'thumbnail' => $profile->thumbnail_url ?? $profile->foto_url,
                ];
            })
            ->toArray();
    }

    protected function latestPublications(): array
    {
        return Publication::query()
            ->with('village:id,nama_desa')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get()
            ->map(fn(Publication $publication) => [
                'id' => $publication->id,
                'title' => $publication->title,
                'village_name' => $publication->village?->nama_desa,
                'published_at' => optional($publication->published_at)->toDateString(),
                'download_url' => $publication->download_url,
            ])
            ->toArray();
    }

    protected function publicStatisticsOverview(): array
    {
        $codes = config('dashboard.public_statistics_codes');
        $results = [];

        foreach ($codes as $key => $code) {
            $value = VillageStatistic::query()
                ->whereHas('statisticType', fn($query) => $query->where('code', $code))
                ->orderByDesc('year')
                ->limit(1)
                ->value('value');

            $results[$key] = $value ? (float) $value : null;
        }

        return $results;
    }
}
