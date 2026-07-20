# Vendor Operations — Panduan Internal Javamaya (untuk KITA sebagai vendor)

Dokumen ini bukan untuk buyer. Ini panduan menjalankan infrastruktur vendor: Release Registry, signing key, Version Check API, Package Download API, dan kill switch.

## 1. Server vendor

Satu VPS kecil (2 vCPU/2GB) cukup untuk ribuan instalasi (endpoint di-cache klien 3 jam). Komponen:
- **Release Registry**: penyimpanan metadata rilis (versi, channel, changelog, URL paket, sha256, signature).
- **Version Check API** — `GET /api/updates/check?current_version=&channel=`
  Respons:
  ```json
  {
    "latest_stable": "1.1.0",
    "changelog": "## 1.1.0\n- Perbaikan X\n- Fitur Y",
    "package_url": "https://vendor.javamaya.com/packages/javamaya-1.1.0.zip",
    "package_sha256": "…",
    "package_signature": "base64-ed25519-detached",
    "severity": "normal|security",
    "min_version_allowed": "1.0.0"
  }
  ```
- **Package Download API**: file ZIP statis di belakang CDN/Nginx. (Tahap 3: tautkan verifikasi lisensi per-domain sebelum download.)

## 2. Signing key (KRITIS)

- Generate SEKALI di mesin **offline**:
  ```php
  $pair = sodium_crypto_sign_keypair();
  file_put_contents('secret.key', sodium_crypto_sign_secretkey($pair));   // JANGAN pernah ke server online
  echo bin2hex(sodium_crypto_sign_publickey($pair));                      // tanam ke source klien
  ```
- **Private key disimpan offline** (USB terenkripsi + salinan cadangan di tempat terpisah). Tidak pernah menyentuh server vendor.
- **Public key (hex)** ditanam di `app/Services/Update/SignatureVerifierSodium::VENDOR_PUBLIC_KEY_HEX` sebelum build rilis. Ganti nilai placeholder saat menyiapkan rilis produksi pertama.
- Rotasi key = rilis mayor baru yang membawa public key baru + masa transisi dua key.

## 3. Proses release (checklist)

1. Freeze kode → naikkan `config/javamaya.php` `version`.
2. `composer install --no-dev --optimize-autoloader` → bersihkan file dev → zip (`vendor/` DISERTAKAN, `installer/` DISERTAKAN, `.env` TIDAK).
3. Di mesin offline: hitung `sha256`, tanda tangani hash file:
   ```php
   $sig = sodium_crypto_sign_detached(hash_file('sha256', 'javamaya-1.1.0.zip', true), $secretKey);
   echo base64_encode($sig);
   ```
4. Upload ZIP ke server paket; tambahkan entri Release Registry (versi, sha256, signature, changelog markdown, severity).
5. Uji dari instalasi staging: Cek Update → download → (Tahap 2: auto-apply; Tahap 1: alur manual) → verifikasi.
6. Publish ke channel `stable`.

## 4. Kill switch & mitigasi insiden

- **Tarik rilis buruk**: hapus/putar balik entri registry → klien yang belum update tidak lagi menawarkan versi itu (cache maks 3 jam).
- **Security force notice**: set `"severity": "security"` → klien menampilkan banner merah di dashboard admin.
- **Kompromi private key**: karena key offline, skenario utama adalah kebocoran fisik → segera terbitkan advisory manual ke semua buyer (email), siapkan rilis dengan key baru, dan JANGAN terbitkan paket baru dengan key lama.
- Klien selalu gagal-aman: signature invalid = update ditolak, aplikasi buyer tetap jalan di versi lama.

## 5. Monitoring vendor

Log setiap `check` (versi klien, channel, hash instance anonim) → dashboard sebaran versi → dasar keputusan EOL `min_version_allowed`.
