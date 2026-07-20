<?php

use App\Models\Setting;
use Throwable;

if (! function_exists('jm_setting')) {
    /** Baca setting runtime; fallback ke default. Cache 60 detik. */
    function jm_setting(string $key, mixed $default = null): mixed
    {
        try {
            return cache()->remember("setting.$key", 60, fn () => Setting::query()
                ->where('key', $key)->value('value')) ?? $default;
        } catch (Throwable) {
            // Saat instalasi pertama tabel settings mungkin belum ada.
            return $default;
        }
    }
}

if (! function_exists('jm_rupiah')) {
    /** Format integer rupiah -> "Rp 150.231". Semua uang disimpan integer. */
    function jm_rupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (! function_exists('jm_normalize_phone')) {
    /** Normalisasi nomor WA ke format 62xxxxxxxx. */
    function jm_normalize_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0'))  return '62' . substr($digits, 1);
        if (str_starts_with($digits, '62')) return $digits;
        return '62' . $digits;
    }
}
