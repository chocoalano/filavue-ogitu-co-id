<?php

namespace App\Filament\Resources\CustomerWithdrawals\Exports;

use App\Models\CustomerWalletTransaction;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\Enums\Contracts\ExportFormat as ExportFormatContract;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CustomerWithdrawalExporter extends Exporter
{
    protected static ?string $model = CustomerWalletTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.username')
                ->label('Username Customer'),

            ExportColumn::make('customer.name')
                ->label('Nama Customer'),

            ExportColumn::make('amount')
                ->label('Nominal Penarikan'),

            ExportColumn::make('submission_admin_fee')
                ->label('Biaya Admin')
                ->state(fn (CustomerWalletTransaction $record): float => self::extractSubmissionAdminFee($record->notes)),

            ExportColumn::make('net_amount')
                ->label('Net Diterima')
                ->state(fn (CustomerWalletTransaction $record): float => max(0.0, (float) ($record->amount ?? 0) - self::extractSubmissionAdminFee($record->notes))),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (mixed $state): string => self::statusLabels()[$state] ?? (string) $state),

            ExportColumn::make('customer.bank_name')
                ->label('Bank'),

            ExportColumn::make('customer.bank_account')
                ->label('No. Rekening'),

            ExportColumn::make('payment_method')
                ->label('Metode Bayar'),

            ExportColumn::make('transaction_ref')
                ->label('Ref Transaksi'),

            ExportColumn::make('is_system')
                ->label('Sistem')
                ->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak'),

            ExportColumn::make('completed_at')
                ->label('Selesai')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('created_at')
                ->label('Dibuat')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),

            ExportColumn::make('updated_at')
                ->label('Diperbarui')
                ->formatStateUsing(fn (mixed $state): string => self::formatDateTime($state)),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            DateTimePicker::make('date_from')
                ->label('Dari Tanggal')
                ->seconds(false)
                ->placeholder('Awal periode'),

            DateTimePicker::make('date_until')
                ->label('Sampai Tanggal')
                ->seconds(false)
                ->placeholder('Akhir periode'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled',
                ])
                ->placeholder('Semua status'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query
            ->where('type', 'withdrawal')
            ->with([
                'customer:id,name,username,bank_name,bank_account',
            ]);
    }

    private static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * @return array<int, ExportFormatContract>
     */
    public function getFormats(): array
    {
        return [
            ExportFormat::Csv,
            ExportFormat::Xlsx,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data withdrawal selesai. '.Number::format($export->successful_rows).' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diexport.';
        }

        return $body;
    }

    private static function formatDateTime(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return filled($value) ? (string) $value : '-';
    }

    private static function extractSubmissionAdminFee(?string $notes): float
    {
        $normalized = trim((string) $notes);

        if ($normalized === '') {
            return 0.0;
        }

        if (! preg_match('/Biaya admin:\s*Rp\s*([0-9\.\,]+)/i', $normalized, $matches)) {
            return 0.0;
        }

        $rawAmount = trim((string) ($matches[1] ?? '0'));
        $normalizedAmount = str_replace(['.', ','], ['', '.'], $rawAmount);

        return max(0.0, (float) $normalizedAmount);
    }
}
