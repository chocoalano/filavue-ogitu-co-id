<?php

namespace App\Filament\Resources\Products\Livewire;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\TableComponent;
use Illuminate\Contracts\View\View;

class ProductOrderItemsTable extends TableComponent
{
    public ?Product $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->with('order:id,order_no')
                    ->where('product_id', $this->record?->getKey() ?? 0)
            )
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('order.order_no')
                    ->label('No. Order')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('row_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public function render(): View
    {
        return view('filament.resources.products.livewire.product-order-items-table');
    }
}
