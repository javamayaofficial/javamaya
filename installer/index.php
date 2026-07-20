<?php
/**
 * JAVAMAYA INSTALLER — 4 langkah untuk buyer non-teknis.
 * Standalone PHP (tidak butuh Laravel boot penuh) agar tetap jalan
 * meski .env belum ada. Setelah selesai, folder installer/ WAJIB terhapus
 * (self-delete otomatis; aplikasi menolak jalan bila installer masih ada).
 *
 * Langkah: 1) Cek kebutuhan server  2) Database  3) Akun admin  4) Selesai
 */
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);

define('JVM_BASE', dirname(__DIR__));
$step = isset($_GET['step']) ? max(1, min(4, (int) $_GET['step'])) : 1;
$errors = [];

// ---------- Guard: sudah terinstall? ----------
if (is_file(JVM_BASE . '/storage/installed.flag') && $step < 4) {
    render('sudah', ['message' => 'Javamaya sudah terinstall. Hapus folder installer/ dari server Anda demi keamanan.']);
    exit;
}

// ---------- STEP 1: Requirements ----------
function checkRequirements(): array {
    $writable = fn (string $p) => is_dir(JVM_BASE . $p) && is_writable(JVM_BASE . $p);
    return [
        ['PHP >= 8.2', PHP_VERSION_ID >= 80200, 'Versi terpasang: ' . PHP_VERSION . '. Naikkan via cPanel > Select PHP Version.'],
        ['Ekstensi pdo_mysql', extension_loaded('pdo_mysql'), 'Aktifkan di cPanel > Select PHP Version > Extensions.'],
        ['Ekstensi mbstring', extension_loaded('mbstring'), 'Aktifkan di Select PHP Version.'],
        ['Ekstensi openssl', extension_loaded('openssl'), 'Aktifkan di Select PHP Version.'],
        ['Ekstensi gd', extension_loaded('gd'), 'Aktifkan di Select PHP Version (untuk gambar & PDF).'],
        ['Ekstensi zip', extension_loaded('zip'), 'Aktifkan di Select PHP Version (untuk backup & update).'],
        ['Ekstensi curl', extension_loaded('curl'), 'Aktifkan di Select PHP Version.'],
        ['Ekstensi sodium (WAJIB — Auto-Update & License)', extension_loaded('sodium'),
            'Buka cPanel > Select PHP Version > centang "sodium" > Save. Tanpa ini update aman tidak bisa diverifikasi.'],
        ['Folder storage/ writable', $writable('/storage'), 'Set permission 755/775 via File Manager.'],
        ['Folder bootstrap/cache writable', $writable('/bootstrap/cache'), 'Set permission 755/775.'],
        ['File vendor/ (dependensi) tersedia', is_dir(JVM_BASE . '/vendor'), 'Paket rilis Javamaya sudah menyertakan vendor/. Upload ulang ZIP bila hilang.'],
    ];
}

// ---------- STEP 2: Database + tulis .env + migrate ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = trim($_POST['db_port'] ?? '3306');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = (string) ($_POST['db_pass'] ?? '');
    $url  = rtrim(trim($_POST['app_url'] ?? ''), '/');

    if ($name === '' || $user === '' || $url === '') {
        $errors[] = 'Nama database, user database, dan URL website wajib diisi.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8,
            ]);
            $pdo->query('SELECT 1');
        } catch (Throwable $e) {
            $errors[] = 'Tidak bisa terhubung ke database: ' . htmlspecialchars($e->getMessage()) .
                '. Cek kembali nama DB / user / password dari cPanel > MySQL Databases.';
        }
    }

    if (! $errors) {
        // Tulis .env dari .env.example
        $env = file_get_contents(JVM_BASE . '/.env.example');
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $cronSecret = bin2hex(random_bytes(20));
        $replacements = [
            'APP_KEY=' => 'APP_KEY=' . $appKey,
            'APP_URL=https://tokoanda.com' => 'APP_URL=' . $url,
            'DB_HOST=127.0.0.1' => 'DB_HOST=' . $host,
            'DB_PORT=3306' => 'DB_PORT=' . $port,
            'DB_DATABASE=javamaya' => 'DB_DATABASE=' . $name,
            'DB_USERNAME=' => 'DB_USERNAME=' . $user,
            'DB_PASSWORD=' => 'DB_PASSWORD=' . $pass,
            'CRON_SECRET=' => 'CRON_SECRET=' . $cronSecret,
        ];
        $env = strtr($env, $replacements);
        if (file_put_contents(JVM_BASE . '/.env', $env) === false) {
            $errors[] = 'Gagal menulis file .env. Pastikan folder aplikasi writable.';
        }
    }

    if (! $errors) {
        // Jalankan migrate + seed via Artisan (proses PHP yang sama)
        try {
            require JVM_BASE . '/vendor/autoload.php';
            $app = require JVM_BASE . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('migrate', ['--force' => true]);
            $kernel->call('db:seed', ['--force' => true]);
            $kernel->call('storage:link');
            $_SESSION['jvm_db_ok'] = true;
            header('Location: index.php?step=3'); exit;
        } catch (Throwable $e) {
            $errors[] = 'Migrasi database gagal: ' . htmlspecialchars(substr($e->getMessage(), 0, 300));
        }
    }
}

// ---------- STEP 3: Akun admin ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
    if (empty($_SESSION['jvm_db_ok'])) { header('Location: index.php?step=2'); exit; }
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    $pass2 = (string) ($_POST['password2'] ?? '');

    if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Nama & email valid wajib diisi.';
    if (strlen($pass) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($pass !== $pass2) $errors[] = 'Konfirmasi password tidak sama.';

    if (! $errors) {
        try {
            require JVM_BASE . '/vendor/autoload.php';
            $app = require JVM_BASE . '/bootstrap/app.php';
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            \App\Models\User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => bcrypt($pass), 'role' => 'super_admin']
            );
            file_put_contents(JVM_BASE . '/storage/installed.flag', date('c'));
            header('Location: index.php?step=4'); exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal membuat akun admin: ' . htmlspecialchars(substr($e->getMessage(), 0, 300));
        }
    }
}

// ---------- STEP 4: Selesai + self-delete ----------
$selfDeleted = false;
if ($step === 4) {
    $selfDeleted = deleteInstallerDir(__DIR__);
}

function deleteInstallerDir(string $dir): bool {
    try {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            if (str_ends_with($item->getPathname(), basename(__FILE__)) ) continue; // file ini terakhir
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        // Hapus diri sendiri terakhir (berhasil di Linux; file tetap tereksekusi sampai selesai)
        @unlink(__FILE__);
        @rmdir($dir);
        return ! is_file(__FILE__);
    } catch (Throwable) { return false; }
}

// ---------- Render ----------
function render(string $view, array $data = []): void {
    extract($data);
    $step = $GLOBALS['step']; $errors = $GLOBALS['errors'];
    include __DIR__ . '/views/layout.php';
}

render($step === 1 ? 'requirements' : ($step === 2 ? 'database' : ($step === 3 ? 'admin' : 'finish')), [
    'requirements' => $step === 1 ? checkRequirements() : [],
    'selfDeleted' => $selfDeleted,
]);
