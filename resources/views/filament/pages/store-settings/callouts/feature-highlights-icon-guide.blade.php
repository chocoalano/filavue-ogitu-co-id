<x-filament::callout
    icon="heroicon-o-information-circle"
    color="info"
    heading="Panduan Penggunaan Icon"
    description="Icon yang diterima pada Feature Highlights hanya dari library Lucide Icons. Tulis nama icon dengan format prefix i-lucide- diikuti nama icon, misalnya i-lucide-truck, i-lucide-shield-check, i-lucide-star, i-lucide-gift. Pastikan nama icon sudah tersedia di Lucide sebelum disimpan."
>
    <x-slot name="footer">
        <div class="flex flex-wrap gap-2">
            <x-filament::button
                tag="a"
                size="sm"
                icon="heroicon-o-arrow-top-right-on-square"
                href="https://lucide.dev/icons"
                target="_blank"
                rel="noopener noreferrer"
            >
                Lihat Semua Icon Lucide
            </x-filament::button>

            <x-filament::button
                tag="a"
                size="sm"
                color="gray"
                icon="heroicon-o-document-text"
                href="https://lucide.dev/guide/packages/lucide"
                target="_blank"
                rel="noopener noreferrer"
            >
                Dokumentasi Lucide
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::callout>
