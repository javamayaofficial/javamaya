<?php

namespace App\Services\Notifications;

class FonnteAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'fonnte'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('fonnte_token', 'FONNTE_TOKEN'));
    }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        return $this->post('send_wa', 'https://api.fonnte.com/send', [
            'target'  => jm_normalize_phone($recipient),
            'message' => $message,
        ], ['Authorization' => (string) $this->cred('fonnte_token', 'FONNTE_TOKEN')]);
    }
}
