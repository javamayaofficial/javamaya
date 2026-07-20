<?php

namespace App\Services\GDPR;

use App\Models\GdprRequest;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Storage;

/** Export data user -> ZIP JSON; link expire 24 jam dikirim via email. */
class DataExporter
{
    public function process(GdprRequest $request): void
    {
        $request->update(['status' => 'processing']);
        $user = $request->user;

        $data = [
            'profile'      => $user->only(['name', 'email', 'phone', 'created_at']),
            'orders'       => $user->orders()->with('items')->get()->toArray(),
            'access'       => $user->memberAccess()->get()->toArray(),
            'certificates' => $user->certificates()->get()->toArray(),
        ];

        $dir = 'gdpr';
        Storage::makeDirectory($dir);
        $zipRel = "$dir/export-user-{$user->id}-" . now()->format('YmdHis') . '.zip';
        $zipAbs = storage_path('app/' . $zipRel);

        $zip = new \ZipArchive();
        if ($zip->open($zipAbs, \ZipArchive::CREATE) !== true) {
            $request->update(['status' => 'requested']);
            return;
        }
        $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        $request->update([
            'status' => 'ready',
            'export_path' => $zipRel,
            'export_expires_at' => now()->addHours(24),
        ]);

        app(NotificationService::class)->sendChannel('email', 'gdpr_export_ready', [
            'name' => $user->name,
            'download_url' => route('user.gdpr.download', $request->id),
        ], $user->email);
    }
}
