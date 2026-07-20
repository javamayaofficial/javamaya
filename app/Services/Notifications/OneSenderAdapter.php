<?php

namespace App\Services\Notifications;

class OneSenderAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'onesender'; }
    public function isConfigured(): bool { return filled($this->cred('onesender_token', 'ONESENDER_TOKEN')) && filled(jm_setting('onesender_url')); }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        // OneSender self-hosted: URL API diisi buyer di Settings (onesender_url).
        return $this->post('send_wa', rtrim((string) jm_setting('onesender_url'), '/') . '/api/v1/messages', [
            'recipient_type' => 'individual',
            'to'   => jm_normalize_phone($recipient) . '@s.whatsapp.net',
            'type' => 'text',
            'text' => ['body' => $message],
        ], ['Authorization' => 'Bearer ' . $this->cred('onesender_token', 'ONESENDER_TOKEN')]);
    }
}
