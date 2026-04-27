<?php

namespace App\Filament\Resources\WhatsAppBroadcasts;

use App\Filament\Resources\WhatsAppBroadcasts\Pages\ManageWhatsAppBroadcasts;
use App\Filament\Resources\WhatsAppBroadcasts\Schemas\WhatsAppBroadcastForm;
use App\Filament\Resources\WhatsAppBroadcasts\Schemas\WhatsAppBroadcastInfolist;
use App\Filament\Resources\WhatsAppBroadcasts\Tables\WhatsAppBroadcastTable;
use App\Models\WhatsAppBroadcast;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WhatsAppBroadcastResource extends Resource
{
    protected static ?string $model = WhatsAppBroadcast::class;

    protected static string|BackedEnum|null $navigationIcon = 'bi-whatsapp';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'WhatsApp Broadcast';

    protected static ?string $modelLabel = 'WhatsApp Broadcast';

    protected static ?string $pluralModelLabel = 'WhatsApp Broadcasts';

    public static function form(Schema $schema): Schema
    {
        return WhatsAppBroadcastForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WhatsAppBroadcastInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppBroadcastTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWhatsAppBroadcasts::route('/'),
        ];
    }
}
