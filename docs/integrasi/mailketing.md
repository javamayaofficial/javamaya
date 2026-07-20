# Integrasi Mailketing (Email)

1. Daftar di https://mailketing.co.id → verifikasi domain pengirim Anda (ikuti wizard SPF/DKIM Mailketing agar email tidak masuk spam).
2. Ambil **API Token** dari dashboard Mailketing (menu API).
3. Javamaya: Admin → **Settings & Branding** → tab **Notifikasi** → provider `Mailketing` → tempel API key + isi **Email pengirim** (harus dari domain terverifikasi) → **Simpan**.
4. Klik **Test kirim Email**.

Semua email transaksi (invoice, akses, sertifikat, export GDPR) memakai template di Notification Templates.
