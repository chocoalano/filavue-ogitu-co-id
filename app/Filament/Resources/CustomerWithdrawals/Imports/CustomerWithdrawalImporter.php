<?php

namespace App\Filament\Resources\CustomerWithdrawals\Imports;

use App\Models\CustomerWalletTransaction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class CustomerWithdrawalImporter extends Importer
{
    protected static ?string $model = CustomerWalletTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer_id')
                ->label('Customer ID')
                ->requiredMapping()
                ->integer()
                ->guess(['customer_id', 'id_customer', 'customer'])
                ->rules(['required', 'integer', 'exists:customers,id']),
            ImportColumn::make('transaction_ref')
                ->label('Referensi Transaksi')
                ->requiredMapping()
                ->guess(['transaction_ref', 'ref', 'reference', 'referensi'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('amount')
                ->label('Nominal Transfer (Net)')
                ->requiredMapping()
                ->numeric()
                ->guess(['amount', 'nominal', 'total_diterima'])
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('balance_before')
                ->label('Saldo Sebelum')
                ->numeric()
                ->guess(['balance_before', 'saldo_sebelum'])
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('balance_after')
                ->label('Saldo Sesudah')
                ->numeric()
                ->guess(['balance_after', 'saldo_sesudah'])
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('status')
                ->label('Status')
                ->guess(['status'])
                ->rules(['nullable', Rule::in(['pending', 'completed', 'failed', 'cancelled'])]),
            ImportColumn::make('payment_method')
                ->label('Metode Bayar')
                ->guess(['payment_method', 'metode_bayar', 'method'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('midtrans_transaction_id')
                ->label('Midtrans Transaction ID')
                ->guess(['midtrans_transaction_id', 'midtrans_id'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('notes')
                ->label('Catatan')
                ->guess(['notes', 'catatan', 'keterangan'])
                ->rules(['nullable', 'string']),
            ImportColumn::make('completed_at')
                ->label('Waktu Selesai')
                ->guess(['completed_at', 'waktu_selesai'])
                ->rules(['nullable', 'date']),
            ImportColumn::make('is_system')
                ->label('Transaksi Sistem')
                ->guess(['is_system', 'sistem'])
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): CustomerWalletTransaction
    {
        $record = CustomerWalletTransaction::firstOrNew([
            'transaction_ref' => trim((string) ($this->data['transaction_ref'] ?? '')),
        ]);

        $record->type = 'withdrawal';

        return $record;
    }

    protected function beforeValidate(): void
    {
        $transactionRef = trim((string) ($this->data['transaction_ref'] ?? ''));
        $amount = $this->toPositiveDecimal($this->data['amount'] ?? null);
        $balanceBefore = $this->toPositiveDecimal($this->data['balance_before'] ?? null);
        $balanceAfter = $this->toPositiveDecimal($this->data['balance_after'] ?? null);

        $this->data['customer_id'] = $this->toNullableInteger($this->data['customer_id'] ?? null);
        $this->data['transaction_ref'] = $transactionRef;
        $this->data['type'] = 'withdrawal';
        $this->data['amount'] = $amount ?? 0;
        $this->data['balance_before'] = $balanceBefore ?? 0;
        $this->data['balance_after'] = $balanceAfter ?? max(0, ($balanceBefore ?? 0) - ($amount ?? 0));
        $this->data['status'] = $this->normalizeStatus($this->data['status'] ?? null);
        $this->data['payment_method'] = $this->normalizeString($this->data['payment_method'] ?? null) ?? 'bank_transfer';
        $this->data['midtrans_transaction_id'] = $this->normalizeString($this->data['midtrans_transaction_id'] ?? null);
        $this->data['notes'] = $this->normalizeString($this->data['notes'] ?? null);
        $this->data['completed_at'] = $this->normalizeDateTime($this->data['completed_at'] ?? null);
        $this->data['is_system'] = $this->toBoolean($this->data['is_system'] ?? null, false);
    }

    protected function beforeSave(): void
    {
        if (! $this->record instanceof CustomerWalletTransaction) {
            return;
        }

        $amount = $this->toPositiveDecimal($this->data['amount'] ?? $this->record->amount) ?? 0;
        $balanceBefore = $this->toPositiveDecimal($this->data['balance_before'] ?? $this->record->balance_before) ?? 0;
        $balanceAfter = $this->toPositiveDecimal($this->data['balance_after'] ?? $this->record->balance_after) ?? max(0, $balanceBefore - $amount);

        $this->record->type = 'withdrawal';
        $this->record->amount = $amount;
        $this->record->balance_before = $balanceBefore;
        $this->record->balance_after = $balanceAfter;
        $this->record->status = $this->normalizeStatus($this->data['status'] ?? $this->record->status);
        $this->record->payment_method = $this->normalizeString($this->data['payment_method'] ?? $this->record->payment_method) ?? 'bank_transfer';
        $this->record->transaction_ref = $this->normalizeString($this->data['transaction_ref'] ?? $this->record->transaction_ref);
        $this->record->midtrans_transaction_id = $this->normalizeString($this->data['midtrans_transaction_id'] ?? $this->record->midtrans_transaction_id);
        $this->record->notes = $this->normalizeString($this->data['notes'] ?? $this->record->notes);
        $this->record->completed_at = $this->normalizeDateTime($this->data['completed_at'] ?? $this->record->completed_at);
        $this->record->is_system = $this->toBoolean($this->data['is_system'] ?? $this->record->is_system, false);
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return 'Import withdrawal selesai';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = Number::format($import->successful_rows).' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimpor.';
        }

        return $body;
    }

    private function toPositiveDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return max(0, (float) $value);
    }

    private function toNullableInteger(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function toBoolean(mixed $value, bool $default): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'ya', 'y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'tidak', 'n'], true)) {
            return false;
        }

        return $default;
    }

    private function normalizeStatus(mixed $value): string
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['pending', 'completed', 'failed', 'cancelled'], true)) {
            return $normalized;
        }

        return 'pending';
    }

    private function normalizeString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
