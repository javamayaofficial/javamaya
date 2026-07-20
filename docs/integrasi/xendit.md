# Integrasi Xendit

1. Daftar https://xendit.co → ambil **Secret API Key** (mode Test untuk sandbox).
2. Dashboard Xendit → **Settings → Webhooks** → *Invoices paid* → isi URL dari Javamaya Admin → **Webhook URL & Cron** → baris Xendit. Salin **Verification Token**.
3. Javamaya: Admin → **Payment Gateways** → **Xendit** → Edit:
   - credentials: `api_key` (secret key), `webhook_token` (verification token)
   - Sandbox ON untuk uji (gunakan API key mode Test).
   - Toggle **Aktif** ON.
4. Uji checkout → redirect ke halaman invoice Xendit → bayar simulasi → webhook `x-callback-token` diverifikasi → order lunas.
