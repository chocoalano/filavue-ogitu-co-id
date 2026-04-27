<?php

namespace App\Filament\Resources\Promotions\RelationManagers;

use App\Models\Product;
use App\Models\Promotion;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Produk dalam Promosi';
    }

    /**
     * Tab ini ditampilkan untuk semua tipe kecuali free_shipping (tidak butuh produk).
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type !== 'free_shipping';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->pivotFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Daftar Produk Terikat Promosi')
            ->description('Setiap produk memiliki setelan diskon/harga bundle tersendiri yang hanya berlaku dalam promosi ini.')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('base_price')
                    ->label('Harga Normal')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('pivot.min_qty')
                    ->label('Min. Qty')
                    ->numeric()
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('pivot.discount_percent')
                    ->label('Diskon %')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('-')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('pivot.discount_value')
                    ->label('Potongan Nominal')
                    ->money('IDR')
                    ->placeholder('-'),

                TextColumn::make('pivot.bundle_price')
                    ->label('Harga Bundle')
                    ->money('IDR')
                    ->placeholder('-'),
            ])
            ->filters([])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Produk ke Promosi')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'sku'])
                    ->recordTitle(fn (Product $record): string => "[{$record->sku}] {$record->name}")
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Produk')
                            ->helperText('Cari produk berdasarkan nama atau SKU.'),
                        ...$this->pivotFields(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit Diskon')
                    ->modalHeading('Ubah Setelan Diskon Produk'),
                DetachAction::make()
                    ->label('Lepas dari Promosi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Lepas semua yang dipilih'),
                ]),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    private function pivotFields(): array
    {
        /** @var Promotion|null $promotion */
        $promotion = $this->getOwnerRecord();
        $isBundle = $promotion?->type === 'bundle';

        return [
            TextInput::make('min_qty')
                ->label('Minimum Qty')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->extraInputAttributes([
                    'inputmode' => 'numeric',
                    'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                ])
                ->helperText('Jumlah minimum produk yang harus dibeli agar diskon berlaku.'),

            TextInput::make('discount_percent')
                ->label('Diskon Persen (%)')
                ->numeric()
                ->suffix('%')
                ->minValue(0)
                ->maxValue(100)
                ->live(onBlur: true)
                ->hidden($isBundle)
                ->readOnly(fn (Get $get): bool => filled($get('discount_value')))
                ->helperText(fn (Get $get): string => filled($get('discount_value'))
                    ? 'Tidak dapat diisi karena Potongan Nominal sudah diisi. Kosongkan Potongan Nominal terlebih dahulu.'
                    : 'Prioritas utama. Jika diisi, Potongan Nominal akan dikunci otomatis. Contoh: 20 → Diskon 20%'
                ),

            TextInput::make('discount_value')
                ->label('Potongan Nominal (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->minValue(0)
                ->live(onBlur: true)
                ->hidden($isBundle)
                ->readOnly(fn (Get $get): bool => filled($get('discount_percent')))
                ->helperText(fn (Get $get): string => filled($get('discount_percent'))
                    ? 'Tidak dapat diisi karena Diskon Persen sudah diisi. Kosongkan Diskon Persen terlebih dahulu.'
                    : 'Digunakan jika Diskon Persen kosong. Jika diisi, Diskon Persen akan dikunci otomatis. Contoh: 50000 → Potongan Rp50.000'
                ),

            TextInput::make('bundle_price')
                ->label('Harga Bundle (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->minValue(0)
                ->hidden(! $isBundle)
                ->helperText('Harga khusus bundling untuk produk ini dalam promosi bundle.'),
        ];
    }
}
