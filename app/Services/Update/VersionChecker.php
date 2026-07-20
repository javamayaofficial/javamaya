<?php

namespace App\Services\Update;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * TAHAP 1 — LENGKAP & HIDUP.
 * Cek versi ke server vendor, cache 1-6 jam, graceful bila vendor down:
 * aplikasi tetap jalan, UI menampilkan pesan ramah.
 */
class VersionChecker
{
    public function currentVersion(): string
    {
        return (string) config('javamaya.version');
    }

    /**
     * @return array{ok: bool, latest?: string, changelog?: string, download_url?: string,
     *               severity?: string, update_available?: bool, message?: string}
     */
    public function check(bool $force = false): array
    {
        $cacheKey = 'javamaya.update_check';
        if ($force) Cache::forget($cacheKey);

        return Cache::remember($cacheKey, now()->addHours(3), function () {
            $url = rtrim((string) config('javamaya.vendor.update_url'), '/') . '/check';
            $license = \App\Models\LicenseInfo::query()->first();
            $domain  = parse_url((string) config('app.url'), PHP_URL_HOST) ?: (string) config('app.url');
            try {
                $response = Http::timeout(10)->retry(1, 500, throw: false)->post($url, [
                    'license_key'     => $license?->license_key ?? '',
                    'domain'          => $domain,
                    'current_version' => $this->currentVersion(),
                    'channel'         => config('javamaya.vendor.channel', 'stable'),
                ]);

                if (! $response->successful()) {
                    return ['ok' => false, 'message' => 'Tidak dapat mengecek update saat ini. Coba lagi nanti.'];
                }

                $envelope = $response->json() ?? [];
                $json = $envelope['data'] ?? $envelope;   // server membungkus di 'data'
                $latest = (string) ($json['latest_version'] ?? $json['latest_stable'] ?? '');
                IntegrationLog::create([
                    'provider' => 'javamaya_vendor', 'action' => 'update_check',
                    'request' => ['url' => $url], 'response' => ['latest' => $latest], 'success' => true,
                ]);

                return [
                    'ok'               => true,
                    'update_available' => (bool) ($json['update_available'] ?? false),
                    'latest'           => $latest,
                    'latest_version'   => $latest,
                    'severity'         => (string) ($json['severity'] ?? 'normal'),
                    'min_required'     => $json['min_required'] ?? null,
                    'changelog'        => (string) ($json['changelog'] ?? ''),
                    'sha256'           => (string) ($json['sha256'] ?? ''),
                    'package_signature'=> $json['package_signature'] ?? null,
                    'download_url'     => (string) ($json['download_url'] ?? $json['package_url'] ?? ''),
                    'severity'         => (string) ($json['severity'] ?? 'normal'),
                    'update_available' => $latest !== '' && version_compare($latest, $this->currentVersion(), '>'),
                ];
            } catch (\Throwable $e) {
                IntegrationLog::create([
                    'provider' => 'javamaya_vendor', 'action' => 'update_check',
                    'request' => ['url' => $url], 'response' => ['error' => $e->getMessage()], 'success' => false,
                ]);
                return ['ok' => false, 'message' => 'Server update tidak dapat dihubungi. Aplikasi tetap berjalan normal.'];
            }
        });
    }
}
