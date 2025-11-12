<?php

namespace App\Traits;

use App\Exceptions\VillageAccessDeniedException;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Village;

trait AuthorizesVillageAccess
{
    protected function authorizeVillageAccess(Village $village, User $user): void
    {
        $role = $user->role?->role_name;

        if ($role === UserRole::BPS_ADMIN) {
            return;
        }

        if ($role === UserRole::VILLAGE_OFFICER && (int) $user->desa_id === (int) $village->id) {
            return;
        }

        throw new VillageAccessDeniedException();
    }
}
