<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <div class="text-2xl">{{ ($check['update_available'] ?? false) ? '🔵' : '🟢' }}</div>
            <div class="flex-1">
                <div class="font-bold">Versi Sistem</div>
                @if ($check['update_available'] ?? false)
                    <p class="text-sm mt-1">v{{ $current }} → <b>v{{ $check['latest'] }} tersedia</b>
                        @if (($check['severity'] ?? '') === 'security')<span class="text-xs font-bold text-rose-600 uppercase">security</span>@endif
                    </p>
                    <a href="{{ \App\Filament\Pages\UpdateManagerPage::getUrl() }}" class="text-xs font-semibold text-primary-600 mt-1 inline-block">Buka Update Manager ➜</a>
                @elseif (($check['ok'] ?? true) === false)
                    <p class="text-sm text-gray-500 mt-1">v{{ $current }} — server update tidak terjangkau, aplikasi tetap normal.</p>
                @else
                    <p class="text-sm text-gray-500 mt-1">v{{ $current }} — versi terbaru ✓</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
