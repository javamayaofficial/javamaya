<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\LeadMagnetController;
use App\Http\Controllers\Public\ReviewController;
use App\Http\Controllers\Public\SalesPageController;
use App\Http\Controllers\Public\SocialProofController;
use App\Http\Controllers\Public\UpsellController;
use App\Http\Controllers\Public\WaitlistController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\ClassController;
use App\Http\Controllers\User\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIK (mobile-first) — grup ini memicu ProcessOnVisit + MaintenanceMode
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'jm.maintenance', 'jm.tick'])->group(function () {

    Route::get('/', function () {
        // Kalau ada Sales Page yang dicentang sebagai homepage, tampilkan itu.
        if ($homepage = \App\Models\CanvasPage::activeHomepage()) {
            return app(SalesPageController::class)->homepage($homepage);
        }
        // Default: katalog produk.
        return view('public.home', [
            'products' => \App\Models\Product::published()->latest()->limit(12)->get(),
        ]);
    })->name('home');

    // Sales page di URL sendiri
    Route::get('/l/{slug}', [SalesPageController::class, 'show'])->name('salespage.show');

    Route::get('/p/{slug}', [ProductController::class, 'show'])->name('product.show');
    Route::post('/p/{slug}/waitlist', [WaitlistController::class, 'store'])
        ->middleware('jm.ratelimit:10')->name('product.waitlist');
    Route::post('/p/{slug}/review', [ReviewController::class, 'store'])
        ->middleware(['auth', 'jm.ratelimit:5'])->name('product.review');

    // Upsell pasca-beli (dari thank-you)
    Route::post('/upsell/{orderRef}/{upsell}', [UpsellController::class, 'accept'])
        ->middleware('jm.ratelimit:10')->name('upsell.accept');

    // Lead magnet landing + OTP capture
    Route::get('/go/{slug}', [LeadMagnetController::class, 'show'])->name('lead.show');
    Route::post('/go/{slug}/otp', [LeadMagnetController::class, 'requestOtp'])
        ->middleware('jm.ratelimit:10')->name('lead.otp');
    Route::post('/go/{slug}/verify', [LeadMagnetController::class, 'verify'])
        ->middleware('jm.ratelimit:15')->name('lead.verify');

    // Social proof feed (JSON, cache 60 dtk)
    Route::get('/social-proof/feed', [SocialProofController::class, 'feed'])->name('socialproof.feed');

    // Checkout (4 template dipilih Checkout Template Engine)
    Route::get('/checkout/{slug}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{slug}', [CheckoutController::class, 'store'])
        ->middleware('jm.ratelimit')->name('checkout.store');
    Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])
        ->middleware('jm.ratelimit:20')->name('checkout.coupon');
    Route::get('/pay/{orderRef}', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/thank-you/{orderRef}', [CheckoutController::class, 'thankYou'])->name('checkout.thankyou');
    Route::get('/order-status/{orderRef}', [CheckoutController::class, 'status'])
        ->middleware('jm.ratelimit:30')->name('checkout.status');

    // Invoice + certificate verify + static pages + healthz
    Route::get('/invoice/{orderRef}/{token}', [PageController::class, 'invoice'])->name('invoice.show');
    Route::get('/certificate/verify/{code}', [PageController::class, 'certificateVerify'])
        ->middleware('jm.ratelimit:30')->name('certificate.verify');
    Route::get('/page/{slug}', [PageController::class, 'contentPage'])->name('page.show');

    // Auth publik
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('jm.ratelimit:20');
    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register', [LoginController::class, 'register'])->middleware('jm.ratelimit:10');
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('jm.ratelimit:5')->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset.form');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('jm.ratelimit:10')->name('password.reset.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // WA OTP (rate limit ketat di service: 3/menit/nomor)
    Route::get('/login-otp', fn () => view('public.login-otp'))->name('login.otp');
    Route::post('/otp/request', [OtpController::class, 'request'])->middleware('jm.ratelimit:10')->name('otp.request');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->middleware('jm.ratelimit:15')->name('otp.verify');

    // Google OAuth (tombol auto-hide bila credentials kosong)
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

/* /healthz TANPA maintenance middleware agar monitoring tetap bisa membaca status. */
Route::get('/healthz', [PageController::class, 'healthz'])->name('healthz');

/*
|--------------------------------------------------------------------------
| 2FA (admin & staff) — di luar require-2fa agar tidak loop
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/twofactor/enroll', [TwoFactorController::class, 'showEnroll'])->name('twofactor.enroll');
    Route::post('/twofactor/enroll', [TwoFactorController::class, 'confirmEnroll'])->middleware('jm.ratelimit:15');
    Route::get('/twofactor/challenge', [TwoFactorController::class, 'showChallenge'])->name('twofactor.challenge');
    Route::post('/twofactor/challenge', [TwoFactorController::class, 'verifyChallenge'])->middleware('jm.ratelimit:15');
});

/*
|--------------------------------------------------------------------------
| USER AREA (customer) — session + tracking device untuk Session Management
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'jm.maintenance', 'jm.session', 'jm.tick'])
    ->prefix('akun')->name('user.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/area-member', [AccountController::class, 'memberArea'])->name('member.area');
    Route::get('/transaksi', [AccountController::class, 'transactions'])->name('transactions');
    Route::get('/downloads', [AccountController::class, 'downloads'])->name('downloads');
    Route::get('/downloads/{file}/ambil', [AccountController::class, 'requestDownload'])->name('download.request');
    Route::get('/unduh/{token}', [AccountController::class, 'serveDownload'])->name('download.serve');

    Route::get('/kelas/{slug}', [ClassController::class, 'show'])->name('class.show');
    Route::post('/materi/{material}/selesai', [ClassController::class, 'toggleComplete'])
        ->middleware('jm.ratelimit:60')->name('material.toggle');
    Route::get('/sertifikat', [AccountController::class, 'certificates'])->name('certificates');
    Route::get('/certificate/{certificate}', [AccountController::class, 'certificateDownload'])->name('certificate.download');
    Route::get('/affiliate', [AccountController::class, 'affiliate'])->name('affiliate');
    Route::get('/profil', [AccountController::class, 'profile'])->name('profile');

    Route::get('/sesi', [AccountController::class, 'sessions'])->name('sessions');
    Route::post('/sesi/{id}/cabut', [AccountController::class, 'revokeSession'])->name('sessions.revoke');
    Route::post('/sesi/cabut-lainnya', [AccountController::class, 'revokeOtherSessions'])->name('sessions.revokeOthers');
    Route::post('/sesi/perangkat-2fa/{id}/cabut', [AccountController::class, 'revokeTrustedDevice'])->name('sessions.revokeTrusted');

    Route::get('/data-saya', [AccountController::class, 'gdpr'])->name('gdpr');
    Route::post('/data-saya', [AccountController::class, 'gdprStore'])->middleware('jm.ratelimit:5')->name('gdpr.store');
    Route::get('/data-saya/{gdpr}/unduh', [AccountController::class, 'gdprDownload'])->name('gdpr.download');
});
