<?php

namespace App\Services\Download;

use App\Models\DownloadLog;
use App\Models\DownloadToken;
use App\Models\ProductDownload;
use App\Models\User;
use App\Services\LMS\MemberAccessGrantor;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download token: unik, expire (default 15 menit dari config), audit log.
 * File dilayani via controller dari storage/app/downloads (di luar public/).
 */
class DownloadTokenManager
{
    public function issue(User $user, ProductDownload $file): DownloadToken
    {
        abort_unless(
            MemberAccessGrantor::hasActiveAccess($user, $file->product_id),
            403, 'Anda tidak memiliki akses aktif ke file ini.'
        );

        return DownloadToken::create([
            'token'               => Str::random(64),
            'user_id'             => $user->id,
            'product_download_id' => $file->id,
            'expires_at'          => now()->addMinutes((int) config('javamaya.download_token_ttl_minutes')),
        ]);
    }

    public function serve(string $token): StreamedResponse
    {
        $record = DownloadToken::with('file')->where('token', $token)->first();
        abort_if(! $record, 404);
        abort_if($record->expires_at->isPast(), 410, 'Link download kedaluwarsa. Silakan buat link baru dari member area.');
        abort_if((int) $record->user_id !== (int) auth()->id(), 403);

        $record->update(['used_at' => now()]);
        DownloadLog::create([
            'user_id' => $record->user_id,
            'product_download_id' => $record->product_download_id,
            'ip' => request()->ip(),
        ]);

        $absolute = storage_path('app/' . ltrim($record->file->file_path, '/'));
        abort_unless(is_file($absolute), 404, 'File tidak ditemukan. Hubungi admin toko.');
        return response()->streamDownload(function () use ($absolute) {
            $stream = fopen($absolute, 'rb');
            while (! feof($stream)) { echo fread($stream, 1024 * 512); }
            fclose($stream);
        }, basename($record->file->label) ?: basename($absolute));
    }
}
