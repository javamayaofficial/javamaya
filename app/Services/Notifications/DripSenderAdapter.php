<?php

namespace App\Services\Notifications;

class DripSenderAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'dripsender'; }
    public function isConfigured(): bool { return filled($this->cred('dripsender_api_key', 'DRIPSENDER_API_KEY')); }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        return $this->post('send', 'https://api.dripsender.id/send', [
            'api_key' => (string) $this->cred('dripsender_api_key', 'DRIPSENDER_API_KEY'),
            'phone'   => jm_normalize_phone($recipient),
            'text'    => $message,
        ]);
    }
}
