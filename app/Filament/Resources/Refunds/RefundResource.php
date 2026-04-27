<?php

namespace App\Filament\Resources\Refunds;

use App\Filament\Resources\Refunds\Pages\ManageRefunds;
use App\Filament\Resources\Refunds\Schemas\RefundForm;
use App\Filament\Resources\Refunds\Schemas\RefundInfolist;
use App\Filament\Resources\Refunds\Tables\RefundsTable;
use App\Models\Refund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Refund Pesanan';
    protected static ?string $modelLabel = 'Refund Pesanan';
    protected static ?string $pluralModelLabel = 'Refund Pesanan';
    protected static string | UnitEnum | null $navigationGroup = 'Pesanan';

    public static function form(Schema $schema): Schema
    {
        return RefundForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RefundInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefundsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRefunds::route('/'),
        ];
    }
}
