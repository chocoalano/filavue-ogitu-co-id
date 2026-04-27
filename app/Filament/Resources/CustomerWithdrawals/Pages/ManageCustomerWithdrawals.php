<?php

namespace App\Filament\Resources\CustomerWithdrawals\Pages;

use App\Filament\Resources\CustomerWithdrawals\CustomerWithdrawalResource;
use App\Filament\Resources\CustomerWithdrawals\Exports\CustomerWithdrawalExporter;
use App\Filament\Resources\CustomerWithdrawals\Imports\CustomerWithdrawalImporter;
use App\Filament\Resources\CustomerWithdrawals\Widgets\CustomerWithdrawalOverview;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class ManageCustomerWithdrawals extends ManageRecords
{
    protected static string $resource = CustomerWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportAction::make()
                ->label('Import CSV/XLSX')
                ->importer(CustomerWithdrawalImporter::class)
                ->fileRules([
                    'max:10240',
                ])
                ->chunkSize(250),
            ExportAction::make()
                ->label('Export CSV/XLSX')
                ->exporter(CustomerWithdrawalExporter::class)
                ->modifyQueryUsing(function (Builder $query, array $options): Builder {
                    return $query
                        ->when(
                            filled($options['date_from'] ?? null),
                            fn (Builder $builder): Builder => $builder->where('created_at', '>=', $options['date_from'])
                        )
                        ->when(
                            filled($options['date_until'] ?? null),
                            fn (Builder $builder): Builder => $builder->where('created_at', '<=', $options['date_until'])
                        )
                        ->when(
                            filled($options['status'] ?? null),
                            fn (Builder $builder): Builder => $builder->where('status', $options['status'])
                        );
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerWithdrawalOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Registrasi & Verifikasi Nomor WA Pelanggan')
                ->info()
                ->description(
                    'Sebelum bisa mengajukan penarikan, pelanggan wajib memverifikasi nomor WhatsApp-nya. '
                    .'Saat mendaftar, pelanggan diarahkan ke WhatsApp dengan teks pesan yang sudah terisi otomatis (nama & link konfirmasi). '
                    .'Pelanggan wajib menekan tombol Kirim — sistem mencatat nomor tersebut valid saat pesan masuk ke gateway. '
                    .'Lalu pelanggan klik link di dalam pesan WA untuk mengaktifkan akun. '
                    .'Jika nomor belum terdeteksi, admin dapat mengkonfirmasi secara manual melalui tabel Data Member.'
                ),

            Callout::make('Penarikan Saldo (Perlu Persetujuan Admin)')
                ->warning()
                ->description(
                    'Pelanggan mengajukan penarikan saldo melalui Dashboard — pengajuan masuk ke tabel ini dengan status Pending. '
                    .'Admin klik "Approve" untuk menyetujui — saldo langsung dipotong dan sistem otomatis mengirim notifikasi WA ke nomor pelanggan menggunakan template Qontak. '
                    .'Notifikasi WA hanya berhasil dikirim jika nomor WA pelanggan sudah terkonfirmasi. '
                    .'Tombol penarikan di aplikasi hanya aktif jika nomor WA pelanggan sudah terkonfirmasi.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
