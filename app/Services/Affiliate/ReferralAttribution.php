<?php

namespace App\Services\Affiliate;

use App\Models\Affiliate;
use App\Models\CommissionRule;

/** Cookie last-click. Window dari commission rule (default 30 hari). */
class ReferralAttribution
{
    public const COOKIE = 'jvm_ref';

    public function captureFromRequest(): void
    {
        $slug = request()->query('ref');
        if (! $slug) return;
        $affiliate = Affiliate::where('slug', $slug)->where('status', 'active')->first();
        if (! $affiliate) return;

        $days = (int) (CommissionRule::whereNull('product_id')->value('cookie_days') ?? 30);
        cookie()->queue(self::COOKIE, $slug, $days * 24 * 60);
    }

    public function currentSlug(): ?string
    {
        return request()->cookie(self::COOKIE);
    }
}
