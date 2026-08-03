<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\SeniorManager;
use App\Models\StandardTerm;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Company Details
        Setting::set('company_name', 'Loops Digital (Pvt) Ltd', 'company');
        Setting::set('company_address_1', '291, Soloman Terrace,', 'company');
        Setting::set('company_address_2', 'Colombo 05, Sri Lanka.', 'company');
        Setting::set('company_phone', '+94 112 581 689', 'company');
        Setting::set('company_web', 'www.loops.lk', 'company');
        Setting::set('company_vat', '10246299 - 7000', 'company');

        // Brand Colors
        Setting::set('brand_pink', '#ff0878', 'branding');
        Setting::set('brand_purple', '#8035ca', 'branding');
        Setting::set('brand_blue', '#0057be', 'branding');
        Setting::set('brand_teal', '#2fc9c3', 'branding');

        // Senior Managers
        SeniorManager::create(['name' => 'Manager A']);
        SeniorManager::create(['name' => 'Manager B']);

        // Standard Terms
        StandardTerm::create(['content' => 'Cheques to be drawn in favour of "Loops Digital Private Limited"']);
        StandardTerm::create(['content' => 'Account: Loops Digital (Pvt) Ltd, 039010231847, HNB Bambalapitiya.']);
    }
}
