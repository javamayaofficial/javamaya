# Deploy ke cPanel

## A. Persiapan
- PHP 8.2+/8.3 via **Select PHP Version**, extension: `sodium` (WAJIB), `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `curl`.
- MySQL 5.7+/MariaDB 10.4+ (bawaan cPanel modern sudah memenuhi).
- Paket rilis Javamaya **sudah berisi `vendor/`** — tidak perlu composer di server.

## B. Upload & document root
1. Upload ZIP ke folder situs, Extract.
2. Ubah Document Root domain ke `.../public` (cPanel > Domains).

### Document root tidak bisa diganti?
1. Pindahkan **isi** folder `public/` ke `public_html/`.
2. Edit `public_html/index.php`: ganti dua path `__DIR__.'/../vendor/autoload.php'` dan `__DIR__.'/../bootstrap/app.php'` menjadi path folder aplikasi Anda, misal `__DIR__.'/../javamaya/vendor/autoload.php'`.
3. Pastikan file `.htaccess` ikut terpindah.

## C. Install
Jalankan `https://domain.com/installer/` (4 langkah). Installer menulis `.env`, memasang tabel, membuat admin, dan menghapus dirinya.

## D. Cron (opsional tapi direkomendasikan)
Tanpa cron pun sistem berjalan (process-on-visit). Untuk toko sepi pengunjung, pasang:
- cPanel > **Cron Jobs** > setiap 5 menit:
  `curl -s "https://domain.com/cron/run?secret=SECRET_ANDA" > /dev/null`
  (URL lengkap siap-copy ada di Admin > **Webhook URL & Cron**.)
- Alternatif CLI: `cd /home/user/situs && php artisan schedule:run` tiap menit.

## E. Webhook payment
Copy URL dari Admin > **Webhook URL & Cron** ke dashboard Duitku / Xendit / Moota (petunjuk per provider di `docs/integrasi/`). Halaman itu juga menunjukkan status "webhook pernah masuk ✓" untuk verifikasi.

## F. Update versi (Tahap 1 manual)
Admin > **Update Sistem** → Cek Update → download ZIP → upload & extract timpa (JANGAN hapus `storage/` & `.env`) → klik **Jalankan Migrasi & Finalisasi**. Selalu backup dulu.
