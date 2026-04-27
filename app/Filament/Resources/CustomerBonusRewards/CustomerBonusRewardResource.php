<?php

namespace App\Filament\Resources\CustomerBonusRewards;

use App\Filament\Resources\CustomerBonusRewards\Pages\ManageCustomerBonusRewards;
use App\Filament\Resources\CustomerBonusRewards\Schemas\CustomerBonusRewardForm;
use App\Filament\Resources\CustomerBonusRewards\Schemas\CustomerBonusRewardInfolist;
use App\Filament\Resources\CustomerBonusRewards\Tables\CustomerBonusRewardsTable;
use App\Models\CustomerBonusReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CustomerBonusRewardResource extends Resource
{
    protected static ?string $model = CustomerBonusReward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CustomerBonusReward';
    protected static ?string $navigationLabel = 'Bonus Reward';
    protected static ?string $modelLabel = 'Bonus Reward';
    protected static ?string $pluralModelLabel = 'Bonus Reward';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return CustomerBonusRewardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerBonusRewardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerBonusRewardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerBonusRewards::route('/'),
        ];
    }
}
