<?php

namespace App\Services\PaymentGateways;

use App\Models\IntegrationLog;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;

abstract class AbstractGateway implements GatewayInterface
{
    public function __construct(protected PaymentGateway $config) {}

    protected function cred(string $key, ?string $envKey = null): ?string
    {
        // Kredensial dari UI admin (encrypted DB) MENANG atas .env
        return $this->config->credential($key) ?: ($envKey ? env($envKey) : null);
    }

    protected function sandbox(): bool
    {
        return (bool) $this->config->sandbox_mode;
    }

    /** HTTP call standar: timeout 15 detik + retry 2x + integration_logs. */
    protected function http(string $action, string $method, string $url, array $options = []): array
    {
        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)
                ->{$method}($url, $options);
            $ok = $response->successful();
            IntegrationLog::create([
                'provider' => $this->code(), 'action' => $action,
                'request' => ['url' => $url] + collect($options)->except(['api_key', 'apiKey'])->all(),
                'response' => ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 2000)],
                'success' => $ok,
            ]);
            return ['ok' => $ok, 'json' => $ok ? $response->json() : null, 'status' => $response->status()];
        } catch (\Throwable $e) {
            IntegrationLog::create([
                'provider' => $this->code(), 'action' => $action,
                'request' => ['url' => $url], 'response' => ['error' => $e->getMessage()], 'success' => false,
            ]);
            return ['ok' => false, 'json' => null, 'status' => 0, 'error' => $e->getMessage()];
        }
    }
}
