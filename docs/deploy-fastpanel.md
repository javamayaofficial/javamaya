# Deploy ke FastPanel

1. **Buat situs**: FastPanel > Websites > Create → pilih domain, PHP-FPM 8.2/8.3.
2. **Extension PHP**: menu PHP > versi terpasang > pastikan `sodium`, `pdo_mysql`, `mbstring`, `gd`, `zip`, `curl` aktif (FastPanel umumnya sudah mengaktifkan semua; `php -m | grep sodium` untuk cek).
3. **Database**: Databases > Create database + user, catat kredensial.
4. **Upload**: via File Manager FastPanel atau SFTP, extract ZIP ke folder situs.
5. **Document root**: Websites > pengaturan situs > Document root → arahkan ke subfolder `public`.
6. **Nginx**: FastPanel memakai template Laravel-friendly secara default (`try_files $uri $uri/ /index.php?$query_string;`). Bila konfigurasi custom, gunakan contoh di `docs/deploy-vps-manual.md`.
7. **Installer**: buka `https://domain.com/installer/`, ikuti 4 langkah.
8. **Cron**: FastPanel > Cron → tiap 5 menit: `curl -s "https://domain.com/cron/run?secret=..."` atau tiap menit `php /var/www/.../artisan schedule:run`.
9. **Deploy dari GitHub (opsional)**: clone repo privat ke folder situs → `composer install --no-dev` → lanjut installer seperti biasa.
