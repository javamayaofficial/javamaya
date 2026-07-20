<?php

namespace App\Services\Notifications;

class MailketingAdapter extends AbstractProvider
{
    public function providerKey(): string { return 'mailketing'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('mailketing_api_key', 'MAILKETING_API_KEY'));
    }

    public function send(string $recipient, string $message, ?string $subject = null): array
    {
        return $this->post('send_email', 'https://api.mailketing.co.id/api/v1/send', [
            'api_token'  => (string) $this->cred('mailketing_api_key', 'MAILKETING_API_KEY'),
            'from_name'  => jm_setting('store_name', config('app.name')),
            'from_email' => jm_setting('mail_from', env('MAIL_FROM_ADDRESS')),
            'recipient'  => $recipient,
            'subject'    => $subject ?? jm_setting('store_name', config('app.name')),
            'content'    => nl2br(e($message)),
        ]);
    }
}
