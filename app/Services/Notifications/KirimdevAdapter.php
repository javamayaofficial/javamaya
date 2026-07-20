<?php

namespace App\Services\Notifications;

class KirimdevAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'kirimdev'; }
    public function isConfigured(): bool { return filled($this->cred('kirimdev_token', 'KIRIMDEV_TOKEN')); }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        return $this->post('send_wa', 'https://kirimwa.dev/api/v1/messages', [
            'phone_number' => jm_normalize_phone($recipient),
            'message'      => $message,
            'message_type' => 'text',
        ], ['Authorization' => 'Bearer ' . $this->cred('kirimdev_token', 'KIRIMDEV_TOKEN')]);
    }
}
