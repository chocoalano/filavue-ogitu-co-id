<?php

namespace App\Filament\Resources\Promotions\RelationManagers;

use App\Models\Customer;
use App\Models\CustomerVoucher;
use App\Models\Promotion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VouchersRelationManager extends RelationManager
{
    protected static string $relationship = 'customerVouchers';

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Voucher Customer';
    }

    /**
     * Tab ini hanya tampil untuk tipe yang mendukung assign voucher per-customer.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return in_array($ownerRecord->type, ['voucher', 'member'], true);
    }

    public function form(Schema $schema): Schema
    {
        /** @var Promotion $promotion */
        $promotion = $this->getOwnerRecord();

        return $schema->components([
            Grid::make(2)->schema([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Customer $record): string => "[{$record->username}] {$record->name}")
                    ->searchable(['name', 'username', 'email', 'phone'])
                    ->preload()
                    ->required()
                    ->helperText('Pilih customer yang akan menerima voucher ini.'),

                TextInput::make('code')
                    ->label('Kode Voucher')
                    ->required()
                    ->maxLength(50)
                    ->unique(CustomerVoucher::class, 'code', ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('code', strtoupper(trim((string) $state))))
                    ->helperText('Kode yang digunakan customer saat checkout.')
                    ->suffixAction(
                        Action::make('generateVoucherCode')
                            ->label('Generate')
                            ->icon(Heroicon::OutlinedArrowPath)
                            ->tooltip('Generate kode voucher unik otomatis')
                            ->action(fn (Set $set) => $set('code', strtoupper(Str::random(4).'-'.Str::random(4))))
                    ),
            ]),

            Grid::make(2)->schema([
                Select::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percent' => 'Persen (%) — Potongan berdasarkan persentase',
                        'fixed' => 'Nominal (Rp) — Potongan harga tetap',
                    ])
                    ->native(false)
                    ->required()
                    ->live()
                    ->default('percent')
                    ->helperText('Pilih apakah diskon dihitung dalam persen atau nominal Rupiah.'),

                TextInput::make('discount_amount')
                    ->label(fn (Get $get): string => $get('discount_type') === 'fixed' ? 'Nominal Potongan (Rp)' : 'Persentase Diskon (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(fn (Get $get): ?int => $get('discount_type') === 'percent' ? 100 : null)
                    ->prefix(fn (Get $get): ?string => $get('discount_type') === 'fixed' ? 'Rp' : null)
                    ->suffix(fn (Get $get): ?string => $get('discount_type') === 'percent' ? '%' : null)
                    ->helperText(fn (Get $get): string => $get('discount_type') === 'fixed'
                        ? 'Contoh: 50000 → Potongan Rp50.000 dari total belanja.'
                        : 'Contoh: 20 → Diskon 20% dari total belanja. Maksimal 100.'
                    ),
            ]),

            Grid::make(2)->schema([
                TextInput::make('min_spend')
                    ->label('Minimum Pembelian (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->placeholder('Kosongkan jika tidak ada minimum')
                    ->helperText('Voucher hanya berlaku jika total belanja mencapai nilai ini.'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Nonaktifkan agar voucher tidak bisa digunakan sementara.'),
            ]),

            Grid::make(2)->schema([
                DateTimePicker::make('valid_from')
                    ->label('Berlaku Mulai')
                    ->seconds(false)
                    ->default($promotion->start_at)
                    ->helperText('Default mengikuti tanggal mulai promosi. Isi untuk override.'),

                DateTimePicker::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->seconds(false)
                    ->afterOrEqual('valid_from')
                    ->default($promotion->end_at)
                    ->helperText('Default mengikuti tanggal berakhir promosi. Isi untuk override.'),
            ]),

            Textarea::make('notes')
                ->label('Catatan Admin')
                ->rows(2)
                ->placeholder('Catatan internal untuk voucher ini...')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->heading('Daftar Voucher Customer')
            ->description('Kelola voucher yang di-assign ke customer. Customer menggunakan kode ini saat checkout.')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Voucher')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kode disalin!')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (CustomerVoucher $record): string => $record->customer?->username ?? '')
                    ->sortable(),

                TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => $state === 'percent' ? 'Persen' : 'Nominal')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'percent' ? 'info' : 'warning'),

                TextColumn::make('discount_amount')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(function (CustomerVoucher $record): string {
                        if ($record->discount_type === 'percent') {
                            return number_format($record->discount_amount, 0).'%';
                        }

                        return 'Rp'.number_format($record->discount_amount, 0, ',', '.');
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('min_spend')
                    ->label('Min. Belanja')
                    ->money('IDR')
                    ->placeholder('-'),

                TextColumn::make('valid_from')
                    ->label('Berlaku')
                    ->dateTime('d M Y')
                    ->description(fn (CustomerVoucher $record): string => $record->valid_until
                        ? 's/d '.$record->valid_until->format('d M Y')
                        : '-'
                    )
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('used_at')
                    ->label('Digunakan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum dipakai')
                    ->description(fn (CustomerVoucher $record): string => $record->usedByOrder
                        ? 'Order #'.($record->usedByOrder->order_no ?? $record->used_by_order_id)
                        : ''
                    )
                    ->color(fn (CustomerVoucher $record): string => $record->used_at ? 'gray' : 'success'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                SelectFilter::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'percent' => 'Persen',
                        'fixed' => 'Nominal',
                    ]),

                TernaryFilter::make('used')
                    ->label('Sudah Digunakan')
                    ->nullable()
                    ->attribute('used_at'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Voucher Baru')
                    ->modalHeading('Assign Voucher ke Customer')
                    ->modalDescription('Buat voucher baru dan assign ke customer yang dipilih.'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit Voucher'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
