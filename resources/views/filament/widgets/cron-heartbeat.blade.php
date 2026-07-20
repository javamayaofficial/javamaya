<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <div class="text-2xl">{{ $healthy ? '🟢' : ($never ? '🟡' : '🔴') }}</div>
            <div class="flex-1">
                <div class="font-bold">Background Processor</div>
                @if ($never)
                    <p class="text-sm text-gray-500 mt-1">Belum pernah berjalan. Processor jalan otomatis saat ada pengunjung, atau pasang Cron URL (lihat menu <b>Webhook URL & Cron</b>) agar berjalan tiap menit.</p>
                @elseif ($healthy)
                    <p class="text-sm text-gray-500 mt-1">Terakhir berjalan {{ (int) ($seconds / 60) }} menit {{ $seconds % 60 }} detik lalu. Semua tugas latar (expire order, retry webhook, kirim notifikasi) berjalan normal.</p>
                @else
                    <p class="text-sm text-amber-600 mt-1 font-semibold">Terakhir berjalan {{ (int) ($seconds / 60) }} menit lalu (&gt;10 menit).</p>
                    <p class="text-xs text-gray-500 mt-1">Kemungkinan traffic sepi. Pasang Cron URL di cPanel (menu Webhook URL & Cron) agar tugas latar tetap berjalan tanpa pengunjung.</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
