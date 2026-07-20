<?php

namespace App\Services\Notifications;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;

abstract class AbstractProvider implements NotificationAdapterInterface
{
    /** Kredensial: Settings (DB, diisi via UI admin) menang atas .env */
    protected function cred(string $settingKey, string $envKey): ?string
    {
        return jm_setting($settingKey) ?: env($envKey);
    }

    protected function post(string $action, string $url, array $data, array $headers = []): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)
                ->withHeaders($headers)->asForm()->post($url, $data);
            $ok = $response->successful();
            IntegrationLog::create([
                'provider' => $this->providerKey(), 'action' => $action,
                'request' => ['url' => $url, 'to' => $data['target'] ?? ($data['to'] ?? null)],
                'response' => ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 1000)],
                'success' => $ok,
            ]);
            return ['ok' => $ok, 'error' => $ok ? null : 'HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            IntegrationLog::create([
                'provider' => $this->providerKey(), 'action' => $action,
                'request' => ['url' => $url], 'response' => ['error' => $e->getMessage()], 'success' => false,
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
