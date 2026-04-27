<?php

namespace App\Filament\Resources\Rewards;

use App\Filament\Resources\Rewards\Pages\ManageRewards;
use App\Filament\Resources\Rewards\Schemas\RewardForm;
use App\Filament\Resources\Rewards\Schemas\RewardInfolist;
use App\Filament\Resources\Rewards\Tables\RewardsTable;
use App\Models\Reward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Promotions Rewards Progress';
    protected static ?string $navigationLabel = 'Promotions Rewards Progress';
    protected static ?string $modelLabel = 'Promotions Rewards Progress';
    protected static ?string $pluralModelLabel = 'Promotions Rewards Progress';
    protected static string | UnitEnum | null $navigationGroup = 'Bonus & Komisi MLM';

    public static function form(Schema $schema): Schema
    {
        return RewardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RewardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RewardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRewards::route('/'),
        ];
    }
}
