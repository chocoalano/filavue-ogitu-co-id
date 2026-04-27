<x-filament-widgets::widget>
    <x-filament::callout
        icon="heroicon-o-information-circle"
        color="info"
        heading="Instruksi Kelengkapan Data Belanja, Pengiriman, dan Ketentuan Dari Penerapan Expedisi (Lion Parcel)"
        description="Lengkapi data Komoditi dan Attribute Pengiriman agar proses checkout, perhitungan ongkir, serta pengalaman pengguna berbelanja berjalan nyaman dan minim kendala. Sesuai dengan ketentuan penggunaan vendor expedisi baru (Lion Parcel), pembeli wajib memperbaharui alamat pengiriman mereka atau memilih pengisian alamat pengiriman secara manual demi penyesuaian regulasi vendor expedisi, Sesuaikan juga kode komoditi produk untuk penyesuaian barang pengiriman dari ketentuan vendor expedisi."
    >
        <x-slot name="footer">
            <div class="flex flex-wrap gap-2">
                <x-filament::button
                    tag="a"
                    size="sm"
                    icon="heroicon-o-tag"
                    :href="$this->getCommodityCodeUrl()"
                >
                    Lengkapi Data Komoditi
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    size="sm"
                    color="gray"
                    icon="heroicon-o-truck"
                    :href="$this->getShippingTargetUrl()"
                >
                    Lengkapi Attribute Pengiriman
                </x-filament::button>
                <x-filament::button
                    tag="a"
                    size="sm"
                    color="warning"
                    icon="heroicon-o-truck"
                    href="https://docs.google.com/spreadsheets/d/1uzFj_2qZTf1SQHVTSbGpY_vXqWEmE3mKnaKWy7weQjw/edit?gid=142450122#gid=142450122"
                >
                    Periksa Ketersediaan Kode Komoditi Lion Parcel
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::callout>
</x-filament-widgets::widget>
