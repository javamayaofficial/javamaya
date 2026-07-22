<?php

namespace App\Services\Notifications;

use App\Models\IntegrationLog;
use Illuminate\Support\Facades\Http;

class FonnteAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'fonnte'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('fonnte_token', 'FONNTE_TOKEN'));
    }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        $payload = [
            'target'  => jm_normalize_phone($recipient),
            'message' => $message,
        ];

        try {
            $response = Http::timeout(15)->retry(2, 500, throw: false)
                ->withHeaders(['Authorization' => (string) $this->cred('fonnte_token', 'FONNTE_TOKEN')])
                ->asForm()
                ->post('https://api.fonnte.com/send', $payload);

            $body = $response->json();
            $providerOk = is_array($body) ? (bool) ($body['status'] ?? false) : false;
            $ok = $response->successful() && $providerOk;
            $error = $ok ? null : ((is_array($body) ? ($body['reason'] ?? null) : null) ?: ('HTTP ' . $response->status()));

            IntegrationLog::create([
                'provider' => $this->providerKey(),
                'action' => 'send_wa',
                'request' => ['url' => 'https://api.fonnte.com/send', 'to' => $payload['target']],
                'response' => ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 1000)],
                'success' => $ok,
            ]);

            return ['ok' => $ok, 'error' => $error];
        } catch (\Throwable $e) {
            IntegrationLog::create([
                'provider' => $this->providerKey(),
                'action' => 'send_wa',
                'request' => ['url' => 'https://api.fonnte.com/send', 'to' => $payload['target']],
                'response' => ['error' => $e->getMessage()],
                'success' => false,
            ]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
