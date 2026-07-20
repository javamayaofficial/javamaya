<!DOCTYPE html>
@php $accent = jm_setting('accent_color', '#4f46e5'); @endphp
<html lang="id">
<head>
    <meta charset="UTF-8">
    {{-- Anti-FOUC: terapkan class dark SEBELUM paint pertama (render-blocking, paling atas) --}}
    <script>!function(){var d=localStorage.getItem('jvm_dark');if(d==='1')document.documentElement.classList.add('dark');}();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', jm_setting('store_name', config('app.name')))</title>
    <link rel="icon" href="{{ jm_setting('store_favicon_path') ? asset('storage/'.jm_setting('store_favicon_path')) : '/favicon.ico' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- CSS ter-compile lokal — tanpa CDN saat runtime (cepat & production-grade) --}}
    <link rel="stylesheet" href="{{ asset('assets/jm.css') }}?v={{ config('javamaya.version') }}">
    <style>:root { --jm-accent: {{ $accent }}; }</style>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.1/cdn.min.js"></script>
    @foreach (\App\Models\TrackingPixel::where('active', true)->get() as $pixel)
        @if ($pixel->provider === 'meta')
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $pixel->pixel_id }}');fbq('track','PageView');</script>
        @endif
    @endforeach
    @stack('head')
</head>
<body class="antialiased relative min-h-screen flex flex-col">
    <div id="jm-nav-progress"></div>
    <div class="jm-aurora"></div>

    @unless (isset($hideChrome) && $hideChrome)
    <header class="jm-header">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 min-w-0">
                @if (jm_setting('store_logo_path'))
                    <img src="{{ asset('storage/'.jm_setting('store_logo_path')) }}" alt="" class="h-9 w-9 rounded-xl object-contain">
                @else
                    <span class="h-9 w-9 rounded-xl btn-accent text-white font-extrabold grid place-items-center text-sm shrink-0">
                        {{ mb_strtoupper(mb_substr(jm_setting('store_name', config('app.name')), 0, 1)) }}
                    </span>
                @endif
                <span class="font-extrabold text-[17px] tracking-tight truncate">{{ jm_setting('store_name', config('app.name')) }}</span>
            </a>
            <nav class="flex items-center gap-2 text-sm shrink-0">
                <button type="button" onclick="jvmToggleDark()" aria-label="Ganti tampilan terang/gelap"
                        class="h-9 w-9 rounded-full jm-pill grid place-items-center text-base transition">
                    <span class="dark:hidden">🌙</span><span class="hidden dark:inline">☀️</span>
                </button>
                @auth
                    <a href="{{ route('user.dashboard') }}"
                       class="flex items-center gap-2 rounded-full jm-pill pl-1.5 pr-4 py-1.5 font-semibold transition">
                        <span class="h-7 w-7 rounded-full bg-accent-soft2 text-accent-c grid place-items-center text-xs font-extrabold">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline">Akun Saya</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-4 py-2 font-semibold text-muted hover:text-ink transition">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-full btn-accent text-white px-4 py-2 font-bold shadow-cta hover:opacity-90 transition">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>
    @endunless

    @if (session('status'))
        <div class="relative z-10 max-w-5xl mx-auto px-4 mt-4 w-full">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium flex items-center gap-2">
                <span>✅</span> {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="relative z-10 flex-1 w-full">@yield('content')</main>

    <footer class="relative z-10 mt-20 jm-footer">
        <div class="max-w-5xl mx-auto px-4 py-10">
            <div class="flex flex-col sm:flex-row gap-6 justify-between">
                <div class="max-w-xs">
                    <div class="font-extrabold tracking-tight">{{ jm_setting('store_name', config('app.name')) }}</div>
                    <p class="mt-1.5 text-sm text-muted leading-relaxed">{{ jm_setting('store_tagline', 'Produk digital & kelas online — akses instan setelah pembayaran.') }}</p>
                </div>
                <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm content-start">
                    @foreach (app(\App\Services\ContentCMS\StaticPageRenderer::class)->footerPages() as $p)
                        <a class="text-muted hover:text-ink font-medium transition" href="{{ route('page.show', $p->slug) }}">{{ $p->title }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="mt-8 pt-6 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs text-muted">
                <span>&copy; {{ date('Y') }} {{ jm_setting('store_name', config('app.name')) }}. Semua hak dilindungi.</span>
                <span class="flex items-center gap-1.5">🔒 Pembayaran aman &middot; Akses instan</span>
            </div>
        </div>
    </footer>

    {{-- Dark toggle + nav progress --}}
    <script>
    function jvmToggleDark() {
        const el = document.documentElement;
        const dark = el.classList.toggle('dark');
        localStorage.setItem('jvm_dark', dark ? '1' : '0');
        document.cookie = 'jvm_dark=' + (dark ? '1' : '0') + ';path=/;max-age=31536000;samesite=lax';
    }
    (function () {
        const bar = document.getElementById('jm-nav-progress');
        let timer = null;
        function start() {
            if (!bar) return;
            bar.classList.add('active'); bar.style.width = '0';
            let w = 0;
            timer = setInterval(() => { w = Math.min(88, w + Math.random() * 14); bar.style.width = w + '%'; }, 180);
        }
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || a.target === '_blank' || a.href.startsWith('javascript') || a.hash) return;
            if (new URL(a.href, location.href).origin !== location.origin) return;
            start();
        });
        document.addEventListener('submit', () => start());
        window.addEventListener('pageshow', () => { if (timer) clearInterval(timer); if (bar) { bar.style.width = '100%'; setTimeout(() => { bar.classList.remove('active'); bar.style.width = '0'; }, 250); } });
    })();
    </script>

    @if (filter_var(jm_setting('social_proof_enabled', '1'), FILTER_VALIDATE_BOOL))
    <div id="jvm-social-proof" class="fixed bottom-20 left-3 z-40 max-w-[290px] transition-all duration-500 opacity-0 translate-y-3 pointer-events-none">
        <div class="jm-card px-4 py-3 flex items-center gap-3">
            <span class="h-9 w-9 rounded-full bg-accent-soft grid place-items-center text-base shrink-0">🛒</span>
            <div class="text-xs leading-snug">
                <span class="font-bold" id="jvm-sp-name"></span>
                <span class="text-muted"> baru saja membeli </span>
                <span class="font-semibold" id="jvm-sp-product"></span>
                <div class="text-stone-400 mt-0.5" id="jvm-sp-ago"></div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        let items = [], idx = 0;
        const el = document.getElementById('jvm-social-proof');
        function show() {
            if (!items.length) return;
            const item = items[idx % items.length]; idx++;
            document.getElementById('jvm-sp-name').textContent = item.name;
            document.getElementById('jvm-sp-product').textContent = item.product;
            document.getElementById('jvm-sp-ago').textContent = item.ago;
            el.classList.remove('opacity-0', 'translate-y-3');
            setTimeout(() => el.classList.add('opacity-0', 'translate-y-3'), 5000);
        }
        fetch('{{ route('socialproof.feed') }}').then(r => r.json()).then(d => {
            items = d.data || [];
            if (items.length) { setTimeout(show, 4000); setInterval(show, 18000); }
        }).catch(() => {});
    })();
    </script>
    @endif
</body>
</html>
