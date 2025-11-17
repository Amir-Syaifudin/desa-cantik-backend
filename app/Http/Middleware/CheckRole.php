<?php

namespace App\Http\Middleware;

use App\Exceptions\DashboardAccessDeniedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed role names
     *
     * @throws DashboardAccessDeniedException
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            throw new DashboardAccessDeniedException('You must be authenticated to access this resource');
        }

        $userRole = $user->role?->role_name;

        if (! $userRole || ! in_array($userRole, $roles, true)) {
            throw new DashboardAccessDeniedException(
                sprintf(
                    'Access denied. Required role: %s. Your role: %s',
                    implode(' or ', $roles),
                    $userRole ?? 'none'
                )
            );
        }

        return $next($request);
    }
}
