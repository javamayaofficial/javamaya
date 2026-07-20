<?php
// Konfigurasi inti Javamaya. Nilai runtime yang bisa diubah buyer
// dibaca dari tabel `settings` (Setting::get) dan MENANG atas file ini.
return [
    'version' => '1.4.0',
    'vendor' => [
        // Pertahankan fallback env lama agar instalasi/staging lama tidak putus,
        // tetapi jadikan nama env baru sebagai sumber utama.
        'update_url' => env(
            'JAVAMAYA_UPDATE_URL',
            env('JAVAMAYA_VENDOR_UPDATE_URL', 'https://license.javamaya.id/api/update')
        ),
        'license_url' => env(
            'JAVAMAYA_LICENSE_URL',
            env('JAVAMAYA_VENDOR_LICENSE_URL', 'https://license.javamaya.id/api/license')
        ),
        'channel' => env(
            'JAVAMAYA_CHANNEL',
            env('JAVAMAYA_UPDATE_CHANNEL', 'stable')
        ),
    ],
    'download_token_ttl_minutes' => 15,
    'otp' => ['ttl_minutes' => 5, 'max_per_minute' => 3, 'max_verify_attempts' => 5],
    'order' => ['pending_expiry_minutes' => 1440],
    'rate_limit' => ['public_post_per_minute' => 60],
    'webhook' => ['retry_schedule_minutes' => [1, 5, 30, 120]],          // inbound: 4x lalu dead letter
    'outbound_webhook' => ['retry_schedule_minutes' => [1, 5, 30, 120, 720]], // 5x lalu dead letter
    'backup' => ['retention' => (int) env('BACKUP_RETENTION', 7)],
    'process_on_visit' => ['max_jobs_per_tick' => 20, 'max_seconds_per_tick' => 5, 'lock_seconds' => 55],

    // ===== Lisensi & Auto-Update (arahkan ke License Server vendor) =====
    'license_pubkey_hex' => env('JAVAMAYA_LICENSE_PUBKEY', ''),
];
