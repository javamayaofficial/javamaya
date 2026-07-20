<x-filament-panels::page>
    <form wire:submit="save" class="space-y-5">
        {{ $this->form }}
        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit">Simpan</x-filament::button>
            <x-filament::button color="gray" wire:click="testWa" type="button" icon="heroicon-o-chat-bubble-left">Test kirim WA</x-filament::button>
            <x-filament::button color="gray" wire:click="testEmail" type="button" icon="heroicon-o-envelope">Test kirim Email</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
