# Deploy Manual ke VPS (Ubuntu 22.04/24.04)

## 1. Paket dasar
```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-intl unzip
# ext-sodium sudah builtin di PHP 8.3 Ubuntu; verifikasi:
php -m | grep sodium
```

## 2. Database
```bash
sudo mysql -e "CREATE DATABASE javamaya CHARACTER SET utf8mb4;
CREATE USER 'javamaya'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';
GRANT ALL ON javamaya.* TO 'javamaya'@'localhost'; FLUSH PRIVILEGES;"
```

## 3. Aplikasi
```bash
sudo mkdir -p /var/www/javamaya && cd /var/www/javamaya
# upload & extract ZIP rilis (sudah berisi vendor/)
sudo chown -R www-data:www-data /var/www/javamaya
sudo chmod -R 775 storage bootstrap/cache
```

## 4. Nginx
```nginx
server {
    listen 80;
    server_name domain.com;
    root /var/www/javamaya/public;
    index index.php;
    client_max_body_size 64M;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
```
Aktifkan + SSL: `sudo ln -s /etc/nginx/sites-available/javamaya /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx`, lalu `certbot --nginx`.

## 5. Installer
Buka `https://domain.com/installer/` → 4 langkah.

## 6. Cron + queue worker (VPS direkomendasikan)
```bash
# Cron scheduler (menggantikan process-on-visit sepenuhnya)
sudo crontab -u www-data -e
* * * * * cd /var/www/javamaya && php artisan schedule:run >> /dev/null 2>&1
```
Queue worker systemd (opsional, untuk beban tinggi — QUEUE_CONNECTION=database tetap default aman):
```ini
# /etc/systemd/system/javamaya-queue.service
[Unit]
Description=Javamaya Queue Worker
[Service]
User=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/javamaya/artisan queue:work --sleep=3 --tries=3
[Install]
WantedBy=multi-user.target
```
`sudo systemctl enable --now javamaya-queue`
