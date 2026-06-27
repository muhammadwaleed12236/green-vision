<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('appSettings', [
                'company_name'    => Setting::get('company_name', config('app.name')),
                'company_phone'   => Setting::get('company_phone', ''),
                'company_address' => Setting::get('company_address', ''),
                'company_logo'    => Setting::get('company_logo', ''),
            ]);
        });
    }
}
