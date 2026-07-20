# Integrasi Duitku

1. Daftar https://duitku.com → buat Proyek → dapatkan **Merchant Code** + **API Key** (tersedia mode Sandbox & Production).
2. Di dashboard Duitku, set **Callback URL** = URL dari Javamaya Admin → **Webhook URL & Cron** → baris Duitku.
3. Javamaya: Admin → **Payment Gateways** → **Duitku** → Edit:
   - credentials: `merchant_code`, `api_key`
   - **Sandbox mode ON** untuk uji coba.
   - Toggle **Aktif** ON.
4. Uji checkout: pilih Duitku → redirect ke halaman pembayaran Duitku sandbox → bayar dummy → webhook masuk → order lunas otomatis (cek badge "webhook pernah masuk ✓").
5. Produksi: ganti kredensial production + Sandbox OFF.

Signature callback diverifikasi (md5 merchantCode+amount+orderId+apiKey). Callback dobel diabaikan via idempotency.
