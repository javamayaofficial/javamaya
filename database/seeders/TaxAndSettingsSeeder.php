<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\TaxSetting;
use Illuminate\Database\Seeder;

class TaxAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (! TaxSetting::query()->exists()) {
            TaxSetting::create([
                'percentage' => (float) env('TAX_PPN_DEFAULT_PERCENT', 11),
                'mode' => filter_var(env('TAX_PPN_INCLUSIVE', true), FILTER_VALIDATE_BOOL) ? 'inclusive' : 'exclusive',
            ]);
        }
        foreach ([
            'default_checkout_template' => 'standard',
            'wa_provider' => 'fonnte',
            'email_provider' => 'mailketing',
            'accent_color' => '#4f46e5',
            'social_proof_enabled' => '1',
        ] as $key => $value) {
            if (jm_setting($key) === null) Setting::put($key, $value);
        }
        if (jm_setting('cron_secret') === null) {
            Setting::put('cron_secret', env('CRON_SECRET') ?: bin2hex(random_bytes(20)));
        }
    }
}
