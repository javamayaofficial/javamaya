<?php

namespace App\Services\Marketing;

use App\Models\EmailSequence;
use App\Models\EmailSequenceEnrollment;
use App\Models\EmailSequenceSend;
use App\Services\Notifications\NotificationService;

/**
 * Email sequence (drip): enroll saat pembelian produk / lead magnet / manual,
 * step dikirim bertahap sesuai delay_hours oleh tick (process-on-visit/cron).
 */
class EmailSequenceProcessor
{
    public function __construct(protected NotificationService $notifier) {}

    /** Enroll email ke semua sequence aktif yang trigger-nya cocok. Idempotent per (sequence, email). */
    public function enroll(string $email, string $triggerType, ?int $triggerId = null): void
    {
        $sequences = EmailSequence::where('active', true)
            ->where('trigger_type', $triggerType)
            ->when($triggerId, fn ($q) => $q->where(fn ($qq) => $qq->whereNull('trigger_id')->orWhere('trigger_id', $triggerId)))
            ->get();

        foreach ($sequences as $sequence) {
            $firstStep = $sequence->steps()->first();
            if (! $firstStep) continue;
            EmailSequenceEnrollment::firstOrCreate(
                ['email_sequence_id' => $sequence->id, 'email' => strtolower($email)],
                ['current_step' => 0, 'next_send_at' => now()->addHours($firstStep->delay_hours)]
            );
        }
    }

    /** Kirim step yang jatuh tempo. Dipanggil dari tick. */
    public function run(int $limit = 10): int
    {
        $due = EmailSequenceEnrollment::with('sequence')
            ->whereNull('unsubscribed_at')
            ->whereNotNull('next_send_at')->where('next_send_at', '<=', now())
            ->limit($limit)->get();

        foreach ($due as $enrollment) {
            $steps = $enrollment->sequence?->steps ?? collect();
            $step = $steps->values()->get($enrollment->current_step);
            if (! $step || ! $enrollment->sequence->active) {
                $enrollment->update(['next_send_at' => null]); // selesai
                continue;
            }

            $adapter = $this->notifier->emailProvider();
            $status = 'skipped_no_provider';
            if ($adapter) {
                $result = $adapter->send($enrollment->email, $step->body, $step->subject);
                $status = $result['ok'] ? 'sent' : 'failed';
            }
            EmailSequenceSend::create([
                'email_sequence_enrollment_id' => $enrollment->id,
                'email_sequence_step_id' => $step->id,
                'status' => $status,
            ]);

            $next = $steps->values()->get($enrollment->current_step + 1);
            $enrollment->update([
                'current_step' => $enrollment->current_step + 1,
                'next_send_at' => $next ? now()->addHours($next->delay_hours) : null,
            ]);
        }
        return $due->count();
    }
}
