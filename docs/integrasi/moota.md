# Integrasi Moota (Konfirmasi Transfer Otomatis)

Moota membaca mutasi rekening bank Anda dan Javamaya mencocokkannya otomatis dengan order (kode unik nominal 3 digit).

1. Daftar https://moota.co → hubungkan rekening bank Anda.
2. Moota → **Integrations / Webhook** → tambahkan:
   - URL: salin dari Javamaya Admin → **Webhook URL & Cron** → baris Moota.
   - Secret: buat string acak kuat.
3. Javamaya: Admin → **Payment Gateways** → **Transfer Bank (Moota)** → Edit:
   - credentials: `webhook_secret` = secret yang sama dengan langkah 2.
   - Isi rekening tujuan di Settings → tab **Transfer Manual & QRIS** (rekening yang sama dengan yang dihubungkan ke Moota).
   - Toggle **Aktif** ON.
4. Uji: buat order → transfer TEPAT sesuai nominal (termasuk 3 digit unik) → status berubah lunas otomatis saat Moota mengirim mutasi.

Keamanan & keandalan:
- Signature webhook diverifikasi HMAC-SHA256 atas raw body.
- Mutasi tanpa order yang cocok dicatat sebagai `unmatched` di Payment Transaction Logs (bisa dicocokkan manual lewat tombol **Tandai Lunas**).
- Webhook duplikat otomatis diabaikan (idempotency).
