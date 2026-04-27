<x-filament-widgets::widget>
    <div class="space-y-3">
        @if ($totalPph21 < 0)
            <x-filament::callout
                icon="heroicon-o-exclamation-triangle"
                color="danger"
            >
                <x-slot name="heading">
                    Total PPh21 Negatif Terdeteksi
                </x-slot>

                <x-slot name="description">
                    Akumulasi total PPh21 seluruh periode menunjukkan nilai negatif sebesar
                    <strong>Rp {{ number_format(abs($totalPph21), 0, ',', '.') }}</strong>.
                    Hal ini dapat mengindikasikan adanya koreksi pajak massal, pengembalian lebih bayar, atau ketidaksesuaian data yang perlu diverifikasi bersama tim perpajakan.
                </x-slot>
            </x-filament::callout>
        @endif

        <x-filament::callout
            icon="heroicon-o-information-circle"
            color="info"
        >
            <x-slot name="heading">
                Ringkasan Data Pajak — {{ number_format($totalTahun, 0, ',', '.') }} Tahun Pajak
            </x-slot>

            <x-slot name="description">
                Menampilkan ringkasan <strong>{{ number_format($totalTransaksi, 0, ',', '.') }} transaksi</strong>
                yang dikelompokkan per bulan dari
                <code class="rounded bg-blue-100 px-1 py-0.5 text-xs font-mono text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">vw_customer_bonus_pph21</code>.
                Gunakan filter Tahun untuk mempersempit data ke periode tertentu.
            </x-slot>
        </x-filament::callout>
    </div>
</x-filament-widgets::widget>
