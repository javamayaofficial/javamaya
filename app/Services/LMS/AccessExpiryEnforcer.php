<?php

namespace App\Services\LMS;

use App\Models\MemberAccess;
use App\Services\Notifications\NotificationService;

/**
 * Dijalankan via process-on-visit / cron: tandai akses expired + kirim
 * notif "Akses berakhir, perpanjang di sini" sekali per akses.
 */
class AccessExpiryEnforcer
{
    public function __construct(protected NotificationService $notifier) {}

    public function run(int $limit = 20): int
    {
        $expired = MemberAccess::with(['user', 'product'])
            ->whereNotNull('expires_at')->where('expires_at', '<=', now())
            ->whereNull('revoked_at')->where('expiry_notified', false)
            ->limit($limit)->get();

        foreach ($expired as $access) {
            $access->update(['expiry_notified' => true]);
            if ($access->user && $access->product) {
                $this->notifier->sendTemplate('access_expired', [
                    'name'        => $access->user->name,
                    'product'     => $access->product->name,
                    'renew_url'   => route('product.show', $access->product->slug),
                ], $access->user->phone, $access->user->email);
            }
        }
        return $expired->count();
    }
}
