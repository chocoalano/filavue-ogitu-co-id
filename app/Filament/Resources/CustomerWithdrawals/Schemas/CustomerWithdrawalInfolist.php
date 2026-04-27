<?php

namespace App\Filament\Resources\CustomerWithdrawals\Schemas;

use App\Models\CustomerWalletTransaction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerWithdrawalInfolist
{
    private const WITHDRAWAL_ADMIN_FEE = 6500;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('customer.name')
                ->label('Customer')
                ->placeholder('-'),

            TextEntry::make('customer.ref_code')
                ->label('Kode Referral')
                ->placeholder('-'),

            TextEntry::make('customer.bank_name')
                ->label('Bank Customer')
                ->placeholder('-'),

            TextEntry::make('customer.bank_account')
                ->label('No. Rekening Customer')
                ->placeholder('-'),

            TextEntry::make('type')
                ->label('Tipe Transaksi')
                ->badge()
                ->formatStateUsing(fn (string $state): string => self::typeOptions()[$state] ?? $state)
                ->color(fn (string $state): string => self::typeColors()[$state] ?? 'gray'),

            TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                ->color(fn (string $state): string => self::statusColors()[$state] ?? 'gray'),

            TextEntry::make('amount')
                ->label('Nominal Transfer ke Customer (Net)')
                ->money('IDR'),

            TextEntry::make('balance_before')
                ->label('Saldo Sebelum')
                ->money('IDR'),

            TextEntry::make('admin')
                ->label('Biaya Admin Withdrawal')
                ->state(fn (): int => self::adminFee())
                ->money('IDR'),

            TextEntry::make('total_diterima')
                ->label('Total Diterima Customer (Net)')
                ->state(fn (?CustomerWalletTransaction $record): int => self::totalReceived($record))
                ->money('IDR'),

            TextEntry::make('total_potongan')
                ->label('Total Potongan Wallet (Net + Admin)')
                ->state(fn (?CustomerWalletTransaction $record): int => self::totalDeducted($record))
                ->money('IDR'),

            TextEntry::make('balance_after_formula')
                ->label('Saldo Sesudah (Formula Akunting)')
                ->state(fn (?CustomerWalletTransaction $record): int => self::balanceAfterByFormula($record))
                ->money('IDR'),

            TextEntry::make('balance_after')
                ->label('Saldo Sesudah (Actual)')
                ->money('IDR'),

            TextEntry::make('payment_method')
                ->label('Metode Pembayaran')
                ->placeholder('-'),

            TextEntry::make('transaction_ref')
                ->label('Referensi Transaksi')
                ->placeholder('-')
                ->copyable(),

            TextEntry::make('midtrans_transaction_id')
                ->label('ID Midtrans')
                ->placeholder('-')
                ->copyable(),

            IconEntry::make('is_system')
                ->label('Transaksi Sistem')
                ->boolean(),

            TextEntry::make('completed_at')
                ->label('Waktu Selesai')
                ->dateTime()
                ->placeholder('-'),

            TextEntry::make('notes')
                ->label('Catatan')
                ->placeholder('-')
                ->columnSpanFull(),

            TextEntry::make('midtrans_signature_key')
                ->label('Signature Key')
                ->placeholder('-')
                ->copyable()
                ->columnSpanFull(),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->placeholder('-'),

            TextEntry::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->placeholder('-'),
        ]);
    }

    private static function typeOptions(): array
    {
        return [
            'topup' => 'Topup',
            'withdrawal' => 'Withdrawal',
            'bonus' => 'Bonus',
            'purchase' => 'Purchase',
            'refund' => 'Refund',
            'tax' => 'Tax',
        ];
    }

    private static function typeColors(): array
    {
        return [
            'topup' => 'success',
            'withdrawal' => 'danger',
            'bonus' => 'info',
            'purchase' => 'primary',
            'refund' => 'warning',
            'tax' => 'gray',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
    }

    private static function statusColors(): array
    {
        return [
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'gray',
        ];
    }

    private static function adminFee(): int
    {
        return self::WITHDRAWAL_ADMIN_FEE;
    }

    private static function totalReceived(?CustomerWalletTransaction $record): int
    {
        $amount = (int) round((float) ($record?->amount ?? 0));

        return max(0, $amount);
    }

    private static function totalDeducted(?CustomerWalletTransaction $record): int
    {
        return self::totalReceived($record) + self::adminFee();
    }

    private static function balanceAfterByFormula(?CustomerWalletTransaction $record): int
    {
        $balanceBefore = (int) round((float) ($record?->balance_before ?? 0));
        $totalDeducted = self::totalDeducted($record);

        return $balanceBefore - $totalDeducted;
    }
}
