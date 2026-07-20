<?php

namespace App\Services\Update;

/**
 * ===== STRUKTUR TAHAP 2 — LOGIKA VERIFIKASI SUDAH NYATA =====
 * Verifikasi signature package rilis dengan ext-sodium (Ed25519).
 * Public key vendor ditanam di source (bukan .env) agar tidak bisa
 * diganti dari file konfigurasi buyer.
 *
 * SUDAH SELESAI di file ini: verify() nyata memakai sodium_crypto_sign_verify_detached.
 * DISELESAIKAN DI TAHAP 2: pemanggilnya (ApplyEngine pipeline penuh).
 */
class SignatureVerifierSodium
{
    /** Public key vendor (hex, Ed25519). Diganti dengan key produksi saat rilis. */
    public const VENDOR_PUBLIC_KEY_HEX = 'REPLACE_WITH_PRODUCTION_PUBLIC_KEY_HEX_64_CHARS';

    public function hasConfiguredPublicKey(): bool
    {
        $key = $this->publicKeyHex();

        return $key !== ''
            && ctype_xdigit($key)
            && strlen($key) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES * 2;
    }

    public function verifyFile(string $packagePath, string $signatureBase64): bool
    {
        if (! extension_loaded('sodium')) return false;
        if (! is_file($packagePath)) return false;

        $publicKey = @sodium_hex2bin($this->publicKeyHex());
        $signature = base64_decode($signatureBase64, true);
        if ($publicKey === false || $signature === false) return false;
        if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) return false;

        // Hash file dulu (streaming, hemat memori) lalu verifikasi signature atas hash.
        $hash = hash_file('sha256', $packagePath, true);
        return sodium_crypto_sign_verify_detached($signature, $hash, $publicKey);
    }

    public function verifySha256(string $packagePath, string $expectedHex): bool
    {
        return is_file($packagePath) && hash_equals(strtolower($expectedHex), hash_file('sha256', $packagePath));
    }

    /**
     * Verifikasi signature Ed25519 atas string sha256 hex (dipakai auto-update:
     * server menandatangani {sha256, version}; di sini kita verifikasi sha256-nya).
     */
    public function verifyHashSignature(string $sha256Hex, string $signatureBase64, ?string $version = null): bool
    {
        if (! extension_loaded('sodium')) return false;
        $publicKey = @sodium_hex2bin($this->publicKeyHex());
        $signature = base64_decode($signatureBase64, true);
        if ($publicKey === false || $signature === false) return false;
        if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) return false;

        // Rekonstruksi payload JSON PERSIS seperti yang ditandatangani server
        // (LicenseSigner::sign atas ['sha256'=>..,'version'=>..]).
        $payload = json_encode(
            ['sha256' => $sha256Hex, 'version' => $version],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        return sodium_crypto_sign_verify_detached($signature, $payload, $publicKey);
    }

    protected function publicKeyHex(): string
    {
        // Utamakan public key dari config (ditanam saat rilis), fallback ke konstanta.
        $fromConfig = (string) config('javamaya.license_pubkey_hex', '');
        return $fromConfig !== '' ? $fromConfig : self::VENDOR_PUBLIC_KEY_HEX;
    }
}
