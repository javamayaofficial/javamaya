<?php

namespace App\Services\LMS;

use App\Models\Certificate;
use App\Models\ClassRoom;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Certificate BASIC Tahap 1: template PDF sederhana (A4 landscape,
 * background image opsional dari Settings + teks posisi tetap).
 * Idempotent: user+kelas yang sama selalu menghasilkan certificate & kode yang sama.
 */
class CertificateGenerator
{
    public function generateIfComplete(User $user, ClassRoom $class): ?Certificate
    {
        if (! $class->certificate_enabled) return null;
        if ($class->progressFor($user) < 100) return null;

        $existing = Certificate::where('user_id', $user->id)->where('class_id', $class->id)->first();
        if ($existing) return $existing;

        $certificate = Certificate::create([
            'user_id'          => $user->id,
            'class_id'         => $class->id,
            'code'             => $this->uniqueCode(),
            'participant_name' => $user->name,
            'issued_at'        => now(),
        ]);

        $certificate->update(['pdf_path' => $this->renderPdf($certificate)]);

        app(\App\Services\Notifications\NotificationService::class)->sendTemplate('certificate_issued', [
            'name'       => $user->name,
            'class_name' => $class->title,
            'code'       => $certificate->code,
            'verify_url' => route('certificate.verify', $certificate->code),
        ], $user->phone, $user->email);

        return $certificate;
    }

    protected function renderPdf(Certificate $certificate): string
    {
        $pdf = Pdf::loadView('certificate.pdf', [
            'certificate' => $certificate->load('classRoom'),
            'store_name'  => jm_setting('store_name', config('app.name')),
            'background'  => jm_setting('certificate_background_path'),
        ])->setPaper('a4', 'landscape');

        $path = 'certificates/' . $certificate->code . '.pdf';
        Storage::put($path, $pdf->output());
        return $path;
    }

    protected function uniqueCode(): string
    {
        do {
            $code = 'JVM-' . strtoupper(str()->random(4)) . '-' . strtoupper(str()->random(4));
        } while (Certificate::where('code', $code)->exists());
        return $code;
    }
}
