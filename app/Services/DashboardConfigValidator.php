<?php

namespace App\Services;

use App\Models\StatisticType;
use Illuminate\Support\Facades\Log;

class DashboardConfigValidator
{
    /**
     * Validate that all statistic codes in dashboard config exist in database
     * 
     * @return array Array of missing codes
     */
    public static function validateStatisticCodes(): array
    {
        $configuredCodes = config('dashboard.public_statistics_codes', []);
        $missingCodes = [];

        foreach ($configuredCodes as $key => $code) {
            $exists = StatisticType::where('code', $code)->exists();

            if (! $exists) {
                $missingCodes[] = $code;
                Log::warning("Dashboard config references non-existent statistic code: {$code}");
            }
        }

        return $missingCodes;
    }

    /**
     * Check if dashboard configuration is valid
     * 
     * @return bool
     */
    public static function isConfigValid(): bool
    {
        $missingCodes = self::validateStatisticCodes();

        if (count($missingCodes) > 0) {
            Log::warning('Dashboard configuration has invalid statistic codes', [
                'missing_codes' => $missingCodes,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get validation report
     * 
     * @return array
     */
    public static function getValidationReport(): array
    {
        $configuredCodes = config('dashboard.public_statistics_codes', []);
        $missingCodes = self::validateStatisticCodes();

        return [
            'is_valid' => count($missingCodes) === 0,
            'total_codes' => count($configuredCodes),
            'missing_codes' => $missingCodes,
            'valid_codes' => array_diff(array_values($configuredCodes), $missingCodes),
        ];
    }
}
