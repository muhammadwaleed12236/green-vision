<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('company_name', 'Green Vision');
        Setting::set('company_phone', '0300 2529972 / 0334-2611233');
        Setting::set('company_address', '6-B Block-E, Latifabad No. 08, Hyderabad');
        Setting::set('company_logo', 'logos/logo.png');
    }
}
