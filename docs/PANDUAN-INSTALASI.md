# Panduan Instalasi Javamaya (Bahasa Awam)

Waktu yang dibutuhkan: **± 15 menit**. Tidak perlu bisa coding.

## Yang Anda butuhkan
1. Hosting cPanel (paket apa pun yang mendukung PHP 8.2) + domain aktif.
2. File ZIP Javamaya yang Anda terima setelah membeli lisensi.

## Langkah 1 — Siapkan PHP
1. Login cPanel → cari menu **Select PHP Version**.
2. Pilih versi **8.2** atau **8.3**.
3. Di daftar Extensions, **centang: `sodium`** (WAJIB), lalu pastikan `pdo_mysql`, `mbstring`, `gd`, `zip`, `curl`, `openssl` juga tercentang. Klik Save.

## Langkah 2 — Buat database
1. cPanel → **MySQL® Databases**.
2. Create New Database → misal `javamaya`. Catat nama lengkapnya (biasanya `namacpanel_javamaya`).
3. Buat MySQL User baru + password kuat. Catat.
4. **Add User To Database** → pilih user + database → centang **ALL PRIVILEGES** → Save.

## Langkah 3 — Upload aplikasi
1. cPanel → **File Manager** → masuk folder situs Anda.
2. Upload file ZIP Javamaya → klik kanan → **Extract**.
3. Arahkan domain ke folder **`public/`**:
   - Domain utama: cPanel → Domains → ubah Document Root ke `.../public`.
   - Jika tidak bisa diubah (beberapa shared hosting): pindahkan isi folder `public/` ke `public_html/` lalu edit `index.php` sesuai catatan di `docs/deploy-cpanel.md` bagian "Document root tidak bisa diganti".

## Langkah 4 — Jalankan installer
1. Buka `https://domainanda.com/installer/` di browser.
2. **Langkah 1**: semua baris harus hijau. Kalau ada merah, ikuti petunjuk di layar (biasanya soal centang extension di Select PHP Version).
3. **Langkah 2**: isi data database dari Langkah 2 di atas.
4. **Langkah 3**: buat akun Super Admin Anda.
5. **Langkah 4**: selesai! Installer menghapus dirinya sendiri. Jika muncul peringatan merah, hapus folder `installer/` manual dari File Manager.

## Setelah install (penting!)
1. Login ke `https://domainanda.com/admin`.
2. Scan QR **2FA** dengan Google Authenticator, **simpan 8 backup codes** (screenshot/print — hanya tampil sekali!).
3. Ikuti **Setup Checklist** di dashboard: Branding → Pajak/PPN → aktifkan minimal 1 Payment Gateway → hubungkan Fonnte & Mailketing → buat produk pertama → backup pertama.
4. Uji beli produk sendiri dengan **Sandbox mode ON** di gateway.

## Kalau ada masalah
- Halaman putih/error 500 → cek versi PHP 8.2+ dan extension tercentang semua.
- "Tidak bisa terhubung ke database" → nama database biasanya berawalan nama akun cPanel Anda.
- Notifikasi WA tidak terkirim → cek token Fonnte di Admin → Settings → tab Notifikasi → tombol **Test kirim WA**.
