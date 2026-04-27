<?php

namespace App\Filament\Resources\CustomerBonusSponsors;

use App\Filament\Resources\CustomerBonusSponsors\Pages\ManageCustomerBonusSponsors;
use App\Filament\Resources\CustomerBonusSponsors\Schemas\CustomerBonusSponsorForm;
use App\Filament\Resources\CustomerBonusSponsors\Schemas\CustomerBonusSponsorInfolist;
use App\Filament\Resources\CustomerBonusSponsors\Tables\CustomerBonusSponsorsTable;
use App\Models\CustomerBonusSponsor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusSponsorResource extends Resource
{
    protected static ?string $model = CustomerBonusSponsor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusSponsor';
    protected static ?string $navigationLabel = 'Bonus Sponsor';
    protected static ?string $modelLabel = 'Bonus Sponsor';
    protected static ?string $pluralModelLabel = 'Bonus Sponsor';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusSponsorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusSponsorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusSponsorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusSponsors::route('/'),
        ];
    }
}
