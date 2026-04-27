<x-filament-widgets::widget>
    <div class="space-y-3">
        @if ($totalTanpaNpwp > 0)
            <x-filament::callout
                icon="heroicon-o-identification"
                color="warning"
            >
                <x-slot name="heading">
                    {{ number_format($totalTanpaNpwp, 0, ',', '.') }} Wajib Pajak Tanpa NPWP
                </x-slot>

                <x-slot name="description">
                    {{ number_format($totalTanpaNpwp, 0, ',', '.') }} dari {{ number_format($total, 0, ',', '.') }} wajib pajak belum memiliki NPWP terdaftar.
                    Sesuai ketentuan Pasal 21 UU PPh, tarif PPh21 yang dikenakan lebih tinggi 20% dari tarif normal berlaku.
                </x-slot>
            </x-filament::callout>
        @endif

        @if ($totalPph21 < 0)
            <x-filament::callout
                icon="heroicon-o-exclamation-triangle"
                color="danger"
            >
                <x-slot name="heading">
                    Nilai PPh21 Negatif Terdeteksi
                </x-slot>

                <x-slot name="description">
                    Total akumulasi PPh21 menunjukkan nilai negatif sebesar
                    <strong>Rp {{ number_format(abs($totalPph21), 0, ',', '.') }}</strong>.
                    Hal ini dapat mengindikasikan adanya koreksi pajak, pengembalian lebih bayar, atau data yang perlu diverifikasi kembali bersama tim perpajakan.
                </x-slot>
            </x-filament::callout>
        @endif

        <x-filament::callout
            icon="heroicon-o-information-circle"
            color="info"
        >
            <x-slot name="heading">
                Sumber Data Laporan
            </x-slot>

            <x-slot name="description">
                Data bersumber dari view SQL <code class="rounded bg-blue-100 px-1 py-0.5 text-xs font-mono text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">vw_customer_bonus_pph21</code>.
                Pastikan data bonus telah diproses dan diverifikasi sebelum digunakan sebagai dasar pelaporan pajak resmi.
            </x-slot>
        </x-filament::callout>
    </div>
</x-filament-widgets::widget>
