<x-filament-panels::page>
    <div class="space-y-5">
        <div class="flex flex-wrap gap-3">
            <select wire:model.live="days" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                <option value="7">7 hari terakhir</option>
                <option value="30">30 hari terakhir</option>
                <option value="90">90 hari terakhir</option>
            </select>
            <select wire:model.live="productId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                <option value="">Semua produk</option>
                @foreach ($products as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
            </select>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <div class="flex items-baseline justify-between mb-5">
                <div class="font-bold text-lg">Funnel konversi</div>
                <div class="text-sm text-gray-500">Konversi total: <b class="text-primary-600 text-lg">{{ $conversionRate }}%</b></div>
            </div>
            <div class="space-y-4">
                @foreach ($stages as $stage)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-semibold">{{ $stage['label'] }}</span>
                            <span class="text-gray-500">{{ number_format($stage['count'], 0, ',', '.') }} <span class="text-xs">({{ $stage['pct'] }}%)</span></span>
                        </div>
                        <div class="h-6 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full bg-primary-600 transition-all duration-700 rounded-lg" style="width: {{ max(2, $stage['pct']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($upsells > 0)
                <p class="mt-4 text-sm text-emerald-600 font-semibold">🎁 +{{ $upsells }} upsell diterima pada periode ini.</p>
            @endif
            <p class="mt-3 text-xs text-gray-500">Drop terbesar antar tahap = titik perbaikan prioritas Anda (mis. banyak "mulai checkout" tapi sedikit "order dibuat" → sederhanakan form / tambah metode bayar).</p>
        </div>
    </div>
</x-filament-panels::page>
