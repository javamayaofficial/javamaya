<?php

namespace App\Services\Marketing;

use App\Models\Product;
use App\Models\ProductWaitlist;
use App\Services\Notifications\NotificationService;

class WaitlistService
{
    public function __construct(protected NotificationService $notifier) {}

    public function join(Product $product, string $name, string $whatsapp, ?string $email): ProductWaitlist
    {
        return ProductWaitlist::firstOrCreate(
            ['product_id' => $product->id, 'whatsapp' => jm_normalize_phone($whatsapp)],
            ['name' => $name, 'email' => $email, 'ip' => request()->ip(), 'user_id' => auth()->id()]
        );
    }

    /** Dipanggil admin saat restock: kirim notif ke semua yang menunggu (batched). */
    public function notifyWaiting(Product $product, int $limit = 50): int
    {
        $waiting = ProductWaitlist::where('product_id', $product->id)
            ->where('status', 'waiting')->limit($limit)->get();

        foreach ($waiting as $entry) {
            $this->notifier->sendTemplate('waitlist_restock', [
                'name'    => $entry->name,
                'product' => $product->name,
                'url'     => route('product.show', $product->slug),
            ], $entry->whatsapp, $entry->email);
            $entry->update(['status' => 'notified', 'notified_at' => now()]);
        }
        return $waiting->count();
    }
}
