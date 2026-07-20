<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <div class="text-2xl">{{ $last && ! $stale ? '🟢' : '🟡' }}</div>
            <div class="flex-1">
                <div class="font-bold">Backup Terakhir</div>
                @if ($last)
                    <p class="text-sm text-gray-500 mt-1">{{ $last->created_at->translatedFormat('d M Y H:i') }} ({{ number_format($last->size / 1024, 0) }} KB)</p>
                    @if ($stale)<p class="text-xs text-amber-600 font-semibold mt-1">Sudah lebih dari 7 hari — buat backup baru.</p>@endif
                @else
                    <p class="text-sm text-amber-600 mt-1 font-semibold">Belum ada backup!</p>
                @endif
                <a href="{{ \App\Filament\Pages\BackupsPage::getUrl() }}" class="text-xs font-semibold text-primary-600 mt-2 inline-block">Buka halaman Backups ➜</a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
