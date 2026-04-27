<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-filament::button type="button" color="gray" wire:click="resetForm">
                Reset
            </x-filament::button>

            <x-filament::button type="submit" icon="heroicon-m-check">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
