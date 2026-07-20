<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-3">
            <div class="font-bold text-lg">🚀 Setup Toko Anda</div>
            <span class="text-sm text-gray-500">{{ $doneCount }}/{{ count($items) }} selesai</span>
        </div>
        <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden mb-4">
            <div class="h-full bg-primary-600 transition-all" style="width: {{ count($items) ? (int) ($doneCount / count($items) * 100) : 0 }}%"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-2">
            @foreach ($items as $item)
                <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <span class="text-lg">{{ $item['done'] ? '✅' : '⬜' }}</span>
                    <span class="text-sm font-medium {{ $item['done'] ? 'text-gray-400 line-through' : '' }}">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-500">Selesaikan checklist ini untuk menerima transaksi pertama Anda. Widget hilang otomatis setelah transaksi lunas pertama. 🎉</p>
    </x-filament::section>
</x-filament-widgets::widget>
