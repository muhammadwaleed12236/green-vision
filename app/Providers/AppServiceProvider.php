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
                'company_address_2' => Setting::get('company_address_2', ''),
                'company_website' => Setting::get('company_website', ''),
                'company_social'  => Setting::get('company_social', ''),
                'company_logo'    => Setting::get('company_logo', ''),
                'secondary_logo'  => Setting::get('secondary_logo', ''),
                'invoice_terms'   => Setting::get('invoice_terms', "Green Vision basically provides high quality installation and commissioning services, therefore if any manufacturer rejects warranty claims, Green Vision will not be held responsible.\nWiring cost may vary from site to site.\n06 Months Free complaint warranty."),
            ]);
        });
    }
}
