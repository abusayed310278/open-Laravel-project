<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('role:admin') or ->middleware('role:business,saler')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $allowed = array_map(fn (string $role) => UserRole::from($role), $roles);

        if (! in_array($user->role, $allowed, strict: true)) {
            // Check if an equivalent route exists for the user's current role
            $routeName = $request->route()?->getName();

            if ($routeName && $user->role) {
                $prefix = explode('.', $routeName)[0] ?? '';
                $suffix = substr($routeName, strlen($prefix) + 1);

                if ($suffix !== '') {
                    $targetRoute = $user->role->value . '.' . $suffix;
                    if (Route::has($targetRoute)) {
                        try {
                            return redirect()->route($targetRoute, $request->route()->parameters());
                        } catch (\Throwable $e) {
                            // Route parameter mismatch, fallback to dashboard
                        }
                    }
                }
            }

            // For GET requests, redirect gracefully to the user's portal dashboard
            if ($request->isMethod('GET') && $user->role) {
                return redirect()->route($user->role->dashboardRoute())
                    ->with('info', 'Redirected to your dashboard.');
            }

            abort(403);
        }

        return $next($request);
    }
}
