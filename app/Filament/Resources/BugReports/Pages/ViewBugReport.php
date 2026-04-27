<?php

namespace App\Filament\Resources\BugReports\Pages;

use App\Filament\Resources\BugReports\BugReportResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBugReport extends ViewRecord
{
    protected static string $resource = BugReportResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            static::class,
            DiscussBugReport::class,
            EditBugReport::class,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('chat')
                ->label('Percakapan')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('gray')
                ->url(fn (): string => $this->getResourceUrl('chat')),
            EditAction::make(),
        ];
    }
}
