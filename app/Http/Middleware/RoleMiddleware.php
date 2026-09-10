<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access. Please login.');
        }

        $user = auth()->user();

        // Flatten any comma-separated roles passed in middleware parameters
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        // If 'Finance Admin' or 'Super Admin' is allowed, 'Management' also has full administrative privileges
        if (in_array('Finance Admin', $allowedRoles) || in_array('Super Admin', $allowedRoles)) {
            $allowedRoles[] = 'Management';
            $allowedRoles[] = 'Finance Admin';
            $allowedRoles[] = 'Super Admin';
        }

        // Check if user matches any of the allowed roles
        foreach ($allowedRoles as $allowedRole) {
            if ($user->hasRole($allowedRole) || ($allowedRole === 'Management' && $user->role === 'Management')) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized access. Only ' . implode(' or ', array_unique($allowedRoles)) . ' can access this section.');
    }
}
