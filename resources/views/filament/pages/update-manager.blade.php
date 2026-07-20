<x-filament-panels::page>
    <div class="space-y-5">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="text-sm text-gray-500">Versi terpasang</div>
                <div class="text-3xl font-extrabold">v{{ $this->current }}</div>
            </div>
            <x-filament::button wire:click="checkNow" icon="heroicon-o-arrow-path">Cek Update</x-filament::button>
        </div>

        @php $check = $this->check; @endphp
        @if (($check['ok'] ?? false) === false)
            <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 p-4 text-sm">
                {{ $check['message'] ?? 'Tidak dapat mengecek update.' }} Aplikasi tetap berjalan normal.
            </div>
        @elseif ($check['update_available'] ?? false)
            <div class="rounded-xl border-2 border-primary-500 bg-white dark:bg-gray-800 p-5">
                <div class="flex items-center gap-2">
                    <span class="text-lg font-extrabold">Versi baru tersedia: v{{ $check['latest'] }}</span>
                    @if (($check['severity'] ?? '') === 'security')
                        <span class="text-xs font-bold uppercase bg-rose-100 text-rose-700 rounded-full px-2 py-0.5">Security</span>
                    @endif
                </div>
                <div class="prose prose-sm dark:prose-invert mt-3 max-w-none">
                    {!! \Illuminate\Support\Str::markdown($check['changelog'] ?? '', ['html_input' => 'escape']) !!}
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-filament::button color="success" size="lg" icon="heroicon-o-bolt"
                        wire:click="runAutoUpdate"
                        wire:confirm="Jalankan update otomatis ke v{{ $check['latest'] }}? Sistem akan: backup → unduh → verifikasi tanda tangan → ganti file → migrasi. Bila ada langkah gagal, sistem otomatis dikembalikan ke versi sekarang. Toko masuk mode maintenance sebentar selama proses.">
                        ⚡ Update Otomatis Sekarang ke v{{ $check['latest'] }}
                    </x-filament::button>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Proses otomatis: backup DB &amp; .env → unduh paket → verifikasi sha256 + tanda tangan Ed25519 →
                    ganti file (atomic) → migrasi database. Gagal di langkah mana pun = rollback otomatis ke versi sekarang.
                </p>
                <details class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-900 p-4 text-sm">
                    <summary class="cursor-pointer font-semibold text-gray-600 dark:text-gray-300">Atau update manual (bila hosting membatasi proses otomatis)</summary>
                    <div class="mt-3 flex flex-wrap gap-3">
                        <x-filament::button tag="a" href="{{ $check['download_url'] }}" target="_blank" color="gray" icon="heroicon-o-arrow-down-tray">
                            Download ZIP v{{ $check['latest'] }}
                        </x-filament::button>
                    </div>
                    <p class="mt-3">Backup dari halaman Backups → download ZIP → upload &amp; extract via File Manager
                    (timpa file, JANGAN hapus folder <code>storage</code> &amp; file <code>.env</code>) → klik tombol di bawah.</p>
                    <div class="mt-3">
                        <x-filament::button color="gray" wire:click="finalizeManual"
                            wire:confirm="Jalankan migrasi database & bersihkan cache sekarang? Pastikan file versi baru sudah diupload.">
                            Selesai upload — Jalankan Migrasi &amp; Finalisasi
                        </x-filament::button>
                    </div>
                </details>
            </div>
        @else
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 text-sm">
                ✓ Anda memakai versi terbaru.
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <div class="font-bold mb-3">Riwayat update</div>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500">
                    <th class="py-2">Tanggal</th><th>Dari</th><th>Ke</th><th>Status</th><th>Durasi</th></tr></thead>
                <tbody>
                @forelse ($history as $h)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="py-2">{{ $h->created_at->format('d M Y H:i') }}</td>
                        <td>v{{ $h->from_version }}</td><td>v{{ $h->to_version }}</td>
                        <td><span class="text-xs font-bold rounded-full px-2 py-0.5 {{ $h->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $h->status }}</span></td>
                        <td>{{ $h->duration_seconds }}s</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada riwayat update.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
