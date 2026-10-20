<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized permission.'], 403);
            }

            $dashboardRoute = $user->role?->dashboardRoute() ?? 'account.dashboard';
            return redirect()->route($dashboardRoute)
                ->with('error', "Access restricted: You do not have permission to access that section.");
        }

        return $next($request);
    }
}
