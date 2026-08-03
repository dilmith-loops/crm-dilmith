<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class MaintenanceModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        if (Setting::get('maintenance_mode') == 1) {
            // Allow access to login, logout, maintenance page and health check
            if ($request->is('login') || $request->is('logout') || $request->is('maintenance') || $request->is('up')) {
                return $next($request);
            }

            // If user is logged in
            if (auth()->check()) {
                // If they are a Super Admin, let them pass
                if (auth()->user()->hasRole('super_admin')) {
                    return $next($request);
                }

                // Log out non-super_admin users immediately
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('maintenance');
        }

        return $next($request);
    }
}
