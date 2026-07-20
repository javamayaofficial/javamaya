<x-filament-panels::page>
    <div class="grid md:grid-cols-2 gap-5">
        @foreach ([['🔥 Bulan ini', $monthly], ['🏆 Sepanjang masa', $alltime]] as [$title, $rows])
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <div class="font-bold mb-4">{{ $title }}</div>
                <div class="space-y-2">
                    @forelse ($rows as $i => $row)
                        <div class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ $i === 0 ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200' : 'bg-gray-50 dark:bg-gray-900' }}">
                            <span class="w-7 text-center font-extrabold {{ $i < 3 ? 'text-amber-500' : 'text-gray-400' }}">{{ ['🥇','🥈','🥉'][$i] ?? $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold truncate">{{ $row->affiliate?->user?->name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $row->orders_count }} penjualan</div>
                            </div>
                            <div class="font-extrabold text-sm">{{ jm_rupiah((int) $row->total) }}</div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 py-6 text-sm">Belum ada komisi tercatat.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    <p class="mt-4 text-xs text-gray-500">Komisi berstatus pending/approved/paid dihitung; rejected (refund) tidak.</p>
</x-filament-panels::page>
