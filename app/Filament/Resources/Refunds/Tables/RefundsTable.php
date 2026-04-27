<?php

namespace App\Filament\Resources\Refunds\Tables;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Refunds\RefundOptionLabelFormatter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Refund')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'order:id,order_no',
                'payment:id,order_id,method_id,transaction_id,provider_txn_id,status',
                'payment.method:id,name',
            ]))
            ->columns([
                TextColumn::make('order.order_no')
                    ->label('Nomor Pesanan')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment.provider_txn_id')
                    ->label('ID Pembayaran')
                    ->numeric()
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment.transaction_id')
                    ->label('ID Transaksi')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('payment.method.name')
                    ->label('Metode Bayar')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('payment.status')
                    ->label('Status Bayar')
                    ->placeholder('-')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status Refund')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? '-')
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'processed' => 'primary',
                        'refunded' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Jumlah Refund')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Refund')
                            ->money('IDR')
                    ),

                TextColumn::make('reason')
                    ->label('Alasan Refund')
                    ->placeholder('-')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label('Nomor Pesanan')
                    ->relationship('order', 'order_no', fn (Builder $query): Builder => $query->latest('id'))
                    ->getOptionLabelFromRecordUsing(fn (Order $record): string => RefundOptionLabelFormatter::formatOrder($record))
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pesanan'),

                SelectFilter::make('payment_id')
                    ->label('ID Pembayaran')
                    ->relationship('payment', 'provider_txn_id', fn (Builder $query): Builder => $query->with(['method:id,name'])->latest('id'))
                    ->getOptionLabelFromRecordUsing(fn (Payment $record): string => RefundOptionLabelFormatter::formatPayment($record))
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pembayaran'),

                SelectFilter::make('status')
                    ->label('Status Refund')
                    ->options(self::statusOptions())
                    ->placeholder('Semua status'),

                TernaryFilter::make('has_reason')
                    ->label('Ada Alasan')
                    ->placeholder('Semua')
                    ->trueLabel('Ada alasan')
                    ->falseLabel('Tanpa alasan')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('reason')->where('reason', '!=', ''),
                        false: fn (Builder $query): Builder => $query->where(function (Builder $builder): void {
                            $builder->whereNull('reason')
                                ->orWhere('reason', '=', '');
                        }),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Filter::make('amount_range')
                    ->label('Rentang Nominal')
                    ->schema([
                        TextInput::make('min')
                            ->label('Nominal Minimum')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max')
                            ->label('Nominal Maksimum')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['min'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '>=', $data['min']))
                        ->when(filled($data['max'] ?? null), fn (Builder $builder): Builder => $builder->where('amount', '<=', $data['max']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['min'] ?? null)) {
                            $indicators[] = Indicator::make('Nominal ≥ Rp'.number_format((float) $data['min'], 0, ',', '.'))->removeField('min');
                        }

                        if (filled($data['max'] ?? null)) {
                            $indicators[] = Indicator::make('Nominal ≤ Rp'.number_format((float) $data['max'], 0, ',', '.'))->removeField('max');
                        }

                        return $indicators;
                    }),

                Filter::make('keyword')
                    ->label('Kata Kunci')
                    ->schema([
                        TextInput::make('q')
                            ->label('Cari Data')
                            ->maxLength(100)
                            ->placeholder('Nomor pesanan / alasan refund / ID transaksi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        if ($keyword === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $builder) use ($keyword): void {
                            $builder->where('reason', 'like', '%'.$keyword.'%')
                                ->orWhereHas('order', fn (Builder $order): Builder => $order->where('order_no', 'like', '%'.$keyword.'%'))
                                ->orWhereHas('payment', function (Builder $payment) use ($keyword): Builder {
                                    return $payment->where('transaction_id', 'like', '%'.$keyword.'%')
                                        ->orWhere('provider_txn_id', 'like', '%'.$keyword.'%');
                                });
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        return $keyword !== ''
                            ? [Indicator::make("Kata kunci: {$keyword}")->removeField('q')]
                            : [];
                    }),

                self::betweenDateFilter('created_between', 'Periode Dibuat', 'created_at'),
                self::betweenDateFilter('updated_between', 'Periode Diperbarui', 'updated_at'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['order_id']->columnSpan(2),
                $filters['payment_id']->columnSpan(2),
                $filters['status']->columnSpan(2),
                $filters['has_reason']->columnSpan(2),
                $filters['amount_range']->columnSpan(2),
                $filters['keyword']->columnSpan(2),
                $filters['created_between']->columnSpan(3),
                $filters['updated_between']->columnSpan(3),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function betweenDateFilter(string $name, string $label, string $column): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->schema([
                DateTimePicker::make('from')
                    ->label('Dari')
                    ->seconds(false),
                DateTimePicker::make('until')
                    ->label('Sampai')
                    ->seconds(false),
            ])
            ->columns(2)
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '>=', $data['from']))
                ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where($column, '<=', $data['until']))
            )
            ->indicateUsing(function (array $data) use ($label): array {
                $indicators = [];

                if (filled($data['from'] ?? null)) {
                    $indicators[] = Indicator::make($label.' ≥ '.$data['from'])->removeField('from');
                }

                if (filled($data['until'] ?? null)) {
                    $indicators[] = Indicator::make($label.' ≤ '.$data['until'])->removeField('until');
                }

                return $indicators;
            });
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'processed' => 'Diproses',
            'refunded' => 'Refund Selesai',
        ];
    }
}
