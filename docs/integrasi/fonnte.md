# Integrasi Fonnte (WhatsApp)

1. Daftar di https://fonnte.com → hubungkan nomor WhatsApp bisnis Anda (scan QR di dashboard Fonnte).
2. Salin **Token** dari dashboard Fonnte (menu Device).
3. Javamaya: Admin → **Settings & Branding** → tab **Notifikasi** → pilih provider `Fonnte` → tempel token → **Simpan**.
4. Klik **Test kirim WA** — pesan uji masuk ke nomor WhatsApp akun admin Anda.

Catatan:
- Semua notifikasi (order dibuat, pembayaran lunas, OTP, sertifikat, akses berakhir) otomatis lewat Fonnte setelah token terpasang.
- Isi template pesan bisa diubah di tabel Notification Templates (menyusul UI penuh; sementara via seeder/DB).
- Gagal kirim tercatat di `integration_logs` + `notification_logs` — aplikasi tidak pernah crash karena WA gagal.
