<x-filament-panels::page>
    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 flex items-center justify-between">
            <div>
                <div class="font-bold">Backup database sekarang</div>
                <p class="text-xs text-gray-500 mt-1">mysqldump otomatis; fallback dumper PHP bila shell_exec dimatikan hosting. Retensi: {{ $retention }} backup terakhir.</p>
            </div>
            <x-filament::button wire:click="backupNow" icon="heroicon-o-archive-box-arrow-down">Backup Sekarang</x-filament::button>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500"><th class="py-2">Tanggal</th><th>Tipe</th><th>Ukuran</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($backups as $b)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-2">{{ $b->created_at->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($b->type) }}</td>
                        <td>{{ number_format($b->size / 1024, 0) }} KB</td>
                        <td><span class="text-xs font-bold rounded-full px-2 py-0.5 {{ $b->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $b->status }}</span></td>
                        <td class="text-right">
                            @if ($b->status === 'success')
                                <x-filament::button size="xs" color="gray" wire:click="download({{ $b->id }})">Download</x-filament::button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada backup. Klik "Backup Sekarang".</td></tr>
                @endforelse
                </tbody>
            </table>
            <p class="mt-3 text-xs text-gray-500">Restore: unduh file .sql lalu import via phpMyAdmin (panduan di docs). Restore satu-klik dengan konfirmasi ganda hadir bersama Scheduled Backup di Tahap 2.</p>
        </div>
    </div>
</x-filament-panels::page>
