<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;

/**
 * Pusat pengiriman notifikasi. Provider aktif dibaca dari Settings
 * (wa_provider / email_provider) — TIDAK di-hardcode di kode bisnis.
 * Kegagalan kirim TIDAK PERNAH menggagalkan transaksi (dipanggil via job).
 */
class NotificationService
{
    /** @var array<string, class-string<NotificationAdapterInterface>> */
    protected array $waMap = [
        'fonnte'     => FonnteAdapter::class,
        'onesender'  => OneSenderAdapter::class,
        'starsender' => StarSenderAdapter::class,
        'kirimdev'   => KirimdevAdapter::class,
    ];

    /** @var array<string, class-string<NotificationAdapterInterface>> */
    protected array $emailMap = [
        'mailketing' => MailketingAdapter::class,
        'dripsender' => DripSenderAdapter::class,
    ];

    public function waProvider(): ?NotificationAdapterInterface
    {
        $key = jm_setting('wa_provider', env('WA_PROVIDER', 'fonnte'));
        $class = $this->waMap[$key] ?? null;
        $adapter = $class ? new $class() : null;
        return $adapter?->isConfigured() ? $adapter : null;
    }

    public function emailProvider(): ?NotificationAdapterInterface
    {
        $key = jm_setting('email_provider', env('EMAIL_PROVIDER', 'mailketing'));
        $class = $this->emailMap[$key] ?? null;
        $adapter = $class ? new $class() : null;
        return $adapter?->isConfigured() ? $adapter : null;
    }

    /** Kirim berdasarkan template (WA + email sekaligus bila tersedia). */
    public function sendTemplate(string $templateKey, array $vars, ?string $waTo = null, ?string $emailTo = null): void
    {
        if ($waTo) {
            $this->sendChannel('wa', $templateKey, $vars, $waTo);
        }
        if ($emailTo) {
            $this->sendChannel('email', $templateKey, $vars, $emailTo);
        }
    }

    public function sendChannel(string $channel, string $templateKey, array $vars, string $recipient): void
    {
        $template = NotificationTemplate::where('key', $templateKey)->where('channel', $channel)->first();
        if (! $template) return;

        $body = $this->render($template->body, $vars);
        $subject = $template->subject ? $this->render($template->subject, $vars) : null;

        $adapter = $channel === 'wa' ? $this->waProvider() : $this->emailProvider();
        if (! $adapter) {
            // Provider belum dikonfigurasi: catat skipped, aplikasi tetap jalan.
            NotificationLog::create([
                'channel' => $channel, 'recipient' => $recipient,
                'template_key' => $templateKey, 'status' => 'skipped_no_provider',
            ]);
            return;
        }

        $result = $adapter->send($recipient, $body, $subject);
        NotificationLog::create([
            'channel' => $channel, 'recipient' => $recipient, 'template_key' => $templateKey,
            'status' => $result['ok'] ? 'sent' : 'failed',
            'error' => $result['error'] ?? null,
        ]);
    }

    /** Render {var} -> nilai. Variabel tak dikenal dibiarkan apa adanya. */
    protected function render(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{' . $key . '}', (string) $value, $text);
        }
        return $text;
    }
}
