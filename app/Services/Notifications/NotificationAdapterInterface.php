<?php

namespace App\Services\Notifications;

interface NotificationAdapterInterface
{
    public function providerKey(): string;
    public function isConfigured(): bool;
    /** @return array{ok: bool, error?: string} */
    public function send(string $recipient, string $message, ?string $subject = null): array;
}
