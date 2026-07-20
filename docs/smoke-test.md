# Smoke Test Pasca Install (± 15 menit)

Jalankan berurutan di instalasi baru. Semua harus lulus sebelum toko dibuka.

| # | Uji | Cara | Bukti lulus |
|---|---|---|---|
| 1 | Installer | Selesaikan 4 langkah | Redirect ke /admin, folder installer hilang |
| 2 | 2FA admin | Login pertama → scan QR → simpan 8 codes → konfirmasi | Masuk dashboard; logout-login diminta kode |
| 3 | Backup code | Logout → login → masukkan 1 backup code | Berhasil masuk; code sama ditolak kedua kalinya |
| 4 | Branding+Tax | Settings: nama toko, warna, PPN 11% inclusive, NPWP | Tersimpan; storefront berubah warna |
| 5 | Gateway sandbox | Payment Gateways: Duitku/Xendit sandbox ON + kredensial test; atau Transfer Manual + rekening | Badge "Kredensial: Lengkap" |
| 6 | Produk pertama | Buat produk publish, akses `n_days=30` | Tampil di storefront |
| 7 | Checkout | Beli produk sendiri dari HP (viewport ≤414px) | 1 kolom, tombol lengket, tanpa scroll samping |
| 8 | Webhook + idempotency | Bayar sandbox; kirim ulang callback yang sama (curl) | Order lunas SEKALI; log "Already processed" |
| 9 | Notifikasi + invoice | Cek WA & email masuk | Pesan lunas + link invoice PDF terbuka (PPN benar) |
| 10 | Member area + expiry | Login sebagai pembeli | Akses tampil "s/d [tanggal+30hari]" |
| 11 | Kelas + sertifikat | Tandai semua materi selesai | Progress 100% → link sertifikat → PDF → verifikasi publik VALID |
| 12 | Download token | Downloads → ambil file; buka URL sama >15 menit kemudian | File terunduh; lalu 410 kedaluwarsa |
| 13 | Refund | Admin → order → Refund + alasan | Status refunded, akses pembeli tercabut, notif terkirim |
| 14 | Backup manual | Backups → Backup Sekarang → download .sql | File berisi CREATE TABLE + INSERT |
| 15 | Health check | `curl https://domain/healthz` | 200 + JSON db_ok:true, storage_writable:true |
| 16 | API key | Generate key scope products:read → `curl -H "Authorization: Bearer jvm_..." /api/v1/products` | 200 + data; tanpa key = 401 pesan jelas |
| 17 | Outbound webhook | Subscribe order.completed ke webhook.site → tombol test | Delivery success + header X-Javamaya-Signature |
| 18 | Cron heartbeat | Buka storefront 2x → dashboard admin | Widget hijau "terakhir berjalan < 1 menit lalu" |
| 19 | Upsell | Set upsell produk A→B → beli A sandbox → thank-you | Tawaran tampil; terima → order B pending harga spesial |
| 20 | Waitlist | Set stok produk = 0 → buka halaman produk → daftar tunggu → admin Kabari Restock | Tombol berubah; WA restock masuk; status notified |
| 21 | Trusted device | Login admin, 2FA + centang "ingat perangkat" → logout-login | Challenge dilewati; cabut dari /akun/sesi → diminta lagi |
| 22 | Drip sequence | Buat sequence trigger produk A, step +0 jam → beli A | Enrollment tercatat; tick berikutnya email terkirim |
| 23 | Broadcast | Kampanye WA segmen "semua customer" throttle 5 → Mulai Kirim | Penerima queued → sent bertahap |
| 24 | Abandoned cart | Order pending, ubah created_at -1 jam (DB) → jalankan cron URL | Reminder WA+email masuk, log reminders_sent=1 |
| 25 | Lead magnet | /go/{slug} → isi form → OTP → verifikasi | Deliverable WA masuk; lead verified; enroll drip (bila ada) |
| 26 | Social proof | Buka storefront setelah ada order lunas | Bubble "Bud** baru saja membeli X" muncul |
| 27 | Review | Sebagai pembeli beri ★5 → admin setujui | Ulasan tampil di halaman produk |
| 28 | Sales page /l | Buat Sales Page, isi HTML, terbitkan → buka /l/{slug} | Halaman tampil dengan styling rapi |
| 29 | Sales page homepage | Centang "Jadikan homepage" → buka domain utama "/" | Sales page tampil menggantikan katalog |
| 30 | Homepage tunggal | Centang homepage di halaman B → cek halaman A | is_homepage A otomatis mati (hanya 1 homepage) |
| 31 | Embed | Tempel iframe YouTube di Embed HTML → lihat halaman | Video muncul di akhir halaman |
| 32 | Auto-update cek | Isi license key valid → Update Manager → Cek Update | Muncul versi baru + tombol "Update Otomatis Sekarang" |
| 33 | Auto-update apply | Klik Update Otomatis (staging!) | Maintenance ON → backup → unduh → verify → swap → migrate → sukses, versi naik |
| 34 | Rollback | Rusak paket sengaja (hash beda) → Update Otomatis | Ditolak "tanda tangan tidak sah", sistem tetap di versi lama |
| 35 | Signature | Ganti public key salah → cek update | Paket ditolak; update tidak jalan (aman) |

# Checklist Area Rawan Bug (sudah diantisipasi di kode)

- **Webhook dobel** → unique constraint DB `webhook_idempotency`, bukan cek aplikasi.
- **Race dua webhook bersamaan** → pipeline paid idempotent (cek `isPaid` dalam transaksi).
- **Nominal kembar Moota** → kode unik direservasi terhadap seluruh order pending (range total+1..total+999).
- **Order dibayar setelah expired** → tetap dilunaskan + flag `paid_after_expiry_flag` di log untuk review admin.
- **Double-submit checkout** → idempotency key di form; submit ganda mengembalikan order yang sama.
- **Provider WA/email kosong** → status `skipped_no_provider`, transaksi jalan terus.
- **Vendor update down** → pesan ramah, aplikasi normal (cache + timeout 10 dtk).
- **shell_exec dimatikan hosting** → dumper PHP-native otomatis.
- **Admin lockout 2FA** → 8 backup codes sekali pakai.
- **File invoice/sertifikat hilang pasca pindah hosting** → regenerate on-demand saat diakses.
- **PPN diubah buyer** → order lama aman (snapshot tax_percent/mode/amount per order).
- **Upload PHP berbahaya** → `.htaccess` deny-PHP di folder upload + validasi tipe di form.
- **Kupon melebihi harga** → diskon di-cap ke subtotal; komisi persen di-cap 90%.
- **Session dicabut tapi masih login** → revoke menghapus row session Laravel (logout paksa nyata).
