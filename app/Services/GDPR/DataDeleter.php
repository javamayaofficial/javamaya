<?php

namespace App\Services\GDPR;

use App\Models\ActivityLog;
use App\Models\GdprRequest;
use Illuminate\Support\Facades\DB;

/**
 * Deletion: cooling period 30 hari -> admin approve -> anonymize.
 * Order & invoice TIDAK dihapus (kepatuhan pajak) — data pembeli dianonimkan.
 */
class DataDeleter
{
    public function approveAndAnonymize(GdprRequest $request): void
    {
        abort_if($request->cooling_until?->isFuture(), 422, 'Masa tunggu 30 hari belum selesai.');
        $user = $request->user;

        DB::transaction(function () use ($request, $user) {
            $anon = 'deleted-user-' . $user->id;
            $user->orders()->update([
                'buyer_name' => $anon, 'buyer_email' => $anon . '@anonymized.local', 'buyer_phone' => '0',
            ]);
            $user->memberAccess()->update(['revoked_at' => now()]);
            $user->update([
                'name' => $anon, 'email' => $anon . '@anonymized.local',
                'phone' => null, 'google_id' => null, 'password' => bcrypt(str()->random(40)),
            ]);
            $request->update(['status' => 'done', 'processed_at' => now()]);
            ActivityLog::record('gdpr.user_anonymized', $user);
        });
    }
}
