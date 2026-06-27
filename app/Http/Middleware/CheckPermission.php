<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role;
        if (!$role && $user->usertype) {
            $role = \App\Models\Role::where('slug', $user->usertype)->first();
        }

        if (!$role) {
            abort(403, 'No role assigned or invalid role. Contact administrator.');
        }

        if ($role->slug === 'super-admin') {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route->getName();

        if (!$routeName) {
            return $next($request);
        }

        $permissionSlug = $this->resolvePermissionSlug($routeName, $route);

        if (!$permissionSlug) {
            return $next($request);
        }

        $hasPerm = $role->permissions()
            ->where(function($q) use ($permissionSlug) {
                $q->where('slug', $permissionSlug);
                $parts = explode('.', $permissionSlug);
                if (count($parts) > 1) {
                    array_pop($parts);
                    $parentSlug = implode('.', $parts);
                    $q->orWhere('slug', $parentSlug);
                }
            })
            ->exists();

        if (!$hasPerm) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }

    protected function resolvePermissionSlug(string $routeName, $route): ?string
    {
        $registrar = app(\App\Services\PermissionRegistrar::class);
        $module = $registrar->resolveModule($routeName);
        $action = $registrar->resolveAction($routeName, $route);

        return Str::slug($module) . '.' . Str::slug($action);
    }
}
