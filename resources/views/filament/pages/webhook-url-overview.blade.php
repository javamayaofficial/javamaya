<x-filament-panels::page>
    <div class="space-y-4">
        @foreach ($rows as $row)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-semibold">{{ $row['label'] }}</div>
                    @if (! is_null($row['status']))
                        <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $row['status'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $row['status'] ? '✓ Webhook pernah masuk' : 'Belum ada webhook masuk' }}
                        </span>
                    @endif
                </div>
                <div class="mt-2 flex gap-2">
                    <input readonly value="{{ $row['url'] }}" id="url-{{ $loop->index }}"
                           class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 font-mono">
                    <x-filament::button color="gray" size="sm"
                        x-on:click="navigator.clipboard.writeText(document.getElementById('url-{{ $loop->index }}').value); $tooltip('Tersalin!')">
                        Copy
                    </x-filament::button>
                </div>
                <p class="mt-2 text-xs text-gray-500">{{ $row['hint'] }}</p>
            </div>
        @endforeach

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center justify-between">
            <div>
                <div class="font-semibold">Regenerate Cron Secret</div>
                <p class="text-xs text-gray-500 mt-1">Gunakan bila secret bocor. Secret lama langsung nonaktif.</p>
            </div>
            <x-filament::button color="danger" wire:click="regenerateCronSecret" wire:confirm="Yakin regenerate? Cron job lama harus diperbarui.">
                Regenerate
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
