# Javamaya

**Self-Hosted Digital Product & Online Class Platform untuk Kreator dan Bisnis Indonesia.**

Toko digital, kelas online, dan marketing automation — di domainmu sendiri, tanpa potongan, tanpa ribet. Digital Storefront + LMS + Checkout multi-template multi-gateway + Affiliate + Integration Hub (REST API + Outbound Webhook) + Auto-Update Client, dirancang untuk berjalan mulus di **cPanel biasa**, FastPanel, dan VPS.

## Fitur utama per tahap

**Tahap 1 (rilis ini):** produk multi-tipe + access expiry (lifetime/N hari), checkout 4 template, Duitku + Xendit + Moota simultan + Transfer Manual + QRIS Manual (toggle & sandbox per gateway), notifikasi WA (Fonnte) + email (Mailketing) berbasis template, login email/pass + WA OTP + Google OAuth, **2FA admin TOTP + 8 backup codes**, kelas + materi + progress + **certificate PDF + verifikasi publik**, member area lengkap (transaksi, downloads token 15 menit, sesi/perangkat, GDPR export/delete), refund workflow auto-revoke, invoice PDF standar pajak (PPN dari Tax Settings, snapshot per order), kupon, order bump, komisi affiliate dasar (atribusi cookie + pencatatan komisi), Content CMS, Public REST API v1 + API Keys + scope + rate limit, Outbound Webhook HMAC + retry + dead letter, `/healthz`, cron heartbeat, backup DB (mysqldump + fallback PHP-native) + retensi, Update Manager manual + update history, installer 4 langkah, process-on-visit.

**v1.1.0 (paritas & keunggulan atas Averion):** upsell pasca-beli one-click di thank-you page, waitlist produk stok habis + notif restock, 2FA trusted devices "ingat perangkat 30 hari" + cabut per perangkat, email sequences (drip) + UI langkah, broadcast WA/email tersegmentasi + throttle anti-banned, abandoned cart recovery otomatis (1 jam & 6 jam), social proof bubble (nama disamarkan, toggle), funnel analytics visual + upsell tracking, leaderboard affiliate, submission ulasan pembeli + moderasi, lead magnet landing + OTP WA + auto-delivery + auto-enroll drip, editor template notifikasi, admin UI file download/bump/bundle/activity log/sertifikat.

**v1.2.0 (belajar dari demo Averion, dieksekusi lebih baik):** 🌙 dark mode storefront + member area (toggle di header, anti-flicker, tersimpan per perangkat; admin Filament sudah punya dark mode bawaan), CSS Tailwind ter-compile lokal 28KB (tanpa CDN saat runtime — lebih cepat & production-grade, regenerate via `tools/build-css.sh`), progress bar navigasi halus antar halaman.

**v1.3.0:** Sales Page satu-kolom (editor teks kaya + embed HTML), bisa dipilih jadi homepage (menggantikan katalog di "/") atau tampil di /l/{slug}; hanya satu homepage aktif (dijaga otomatis); toggle header/footer + meta SEO per halaman.

**v1.4.0:** Auto-update 1-klik (Tingkat 1) — dari Update Manager admin: backup DB+.env → unduh paket dari License Server → verifikasi sha256 + tanda tangan Ed25519 → atomic swap direktori inti → migrasi idempotent → rollback otomatis bila langkah mana pun gagal (maintenance mode selama proses). Terhubung ke Javamaya License Server (aplikasi vendor terpisah: manajemen rilis + tanda tangan paket). Update manual tetap tersedia sebagai fallback.

**Tahap 2 (sisa):** auto-update penuh tanpa sentuhan (dijadwal cron) (signed package ed25519 + backup + atomic apply + rollback), Meta CAPI Purchase, affiliate UI lengkap + payout, membership recurring reminder H-7/H-1, scheduled backup otomatis + restore UI, payment channels per-gateway (pilih VA/e-wallet spesifik).

**Tahap 3:** license system vendor, canvas page builder, AI chat, social proof, provider WA/email alternatif penuh (OneSender/StarSender/Kirimdev/DripSender — adapter sudah tersedia), backup S3.

## Instalasi cepat (buyer)

1. Upload ZIP rilis ke hosting, extract ke folder situs. Arahkan **document root ke `public/`**.
2. Buka `https://domainanda.com/installer/` → ikuti 4 langkah (cek server → database → akun admin → selesai).
3. Login admin → aktifkan 2FA → ikuti Setup Checklist di dashboard.

Panduan awam lengkap: [`docs/PANDUAN-INSTALASI.md`](docs/PANDUAN-INSTALASI.md).

## Setup lokal (developer)

```bash
composer install
cp .env.example .env && php artisan key:generate
# isi kredensial DB di .env
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buat super admin cepat: `php artisan tinker` → `App\Models\User::create(['name'=>'Admin','email'=>'admin@test.com','password'=>bcrypt('password'),'role'=>'super_admin']);`

## Dokumentasi

| Topik | File |
|---|---|
| Instalasi bahasa awam | `docs/PANDUAN-INSTALASI.md` |
| Deploy cPanel / FastPanel / VPS | `docs/deploy-cpanel.md`, `docs/deploy-fastpanel.md`, `docs/deploy-vps-manual.md` |
| Integrasi per provider | `docs/integrasi/{fonnte,mailketing,moota,duitku,xendit,google-oauth,meta-capi}.md` |
| Operasional vendor (kita) | `docs/vendor-operations.md` |
| Smoke test & area rawan bug | `docs/smoke-test.md` |

## Keamanan (kontrak rilis)

Webhook payment: signature verify + idempotency unique-constraint + retry (1m/5m/30m/2h) + dead letter. Download: token 15 menit + audit log. WA OTP: 3/menit/nomor, expiry 5 menit, 5x percobaan. 2FA admin wajib + 8 backup codes hashed. Outbound webhook: HMAC `X-Javamaya-Signature` + retry (1m/5m/30m/2h/12h) + dead letter. Rate limit universal POST publik 60/menit/IP (konfigurable). Upload folder deny-PHP. Update package diverifikasi ed25519 (ext-sodium) — public key tertanam di source.
