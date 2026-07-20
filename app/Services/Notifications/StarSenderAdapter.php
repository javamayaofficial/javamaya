<?php

namespace App\Services\Notifications;

class StarSenderAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'starsender'; }
    public function isConfigured(): bool { return filled($this->cred('starsender_token', 'STARSENDER_TOKEN')); }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        return $this->post('send_wa', 'https://api.starsender.online/api/send', [
            'messageType' => 'text',
            'to'      => jm_normalize_phone($recipient),
            'body'    => $message,
        ], ['Authorization' => (string) $this->cred('starsender_token', 'STARSENDER_TOKEN')]);
    }
}
