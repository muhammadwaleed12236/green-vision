<?php

namespace App\Providers;

use App\Models\Permission;
use App\Services\PermissionRegistrar;
use Blade;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionRegistrar::class);
    }

    public function boot(): void
    {
        Blade::if('haspermission', function (string $slug) {
            $user = auth()->user();
            return $user && $user->hasPermission($slug);
        });

        Blade::if('hasanypermission', function (array $slugs) {
            $user = auth()->user();
            if (!$user) return false;
            foreach ($slugs as $slug) {
                if ($user->hasPermission($slug)) return true;
            }
            return false;
        });
    }
}
