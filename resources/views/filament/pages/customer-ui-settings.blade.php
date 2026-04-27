<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-2">
            <x-filament::button type="button" color="gray" icon="heroicon-m-arrow-path" wire:click="resetForm">
                Reset
            </x-filament::button>
            <x-filament::button type="submit" icon="heroicon-m-check">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
