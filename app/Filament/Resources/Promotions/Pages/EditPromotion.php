<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Schemas\PromotionForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPromotion extends EditRecord
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('autofill')
                ->label('Isi Data Contoh')
                ->icon('heroicon-m-beaker')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Timpa data form dengan data contoh?')
                ->modalDescription('Semua field form akan ditimpa dengan data acak. Data yang belum disimpan akan hilang.')
                ->modalSubmitActionLabel('Ya, timpa data')
                ->action(function (): void {
                    $this->form->fill(PromotionForm::testingAutofillData());

                    Notification::make()
                        ->success()
                        ->title('Form berhasil diisi ulang dengan data acak yang valid.')
                        ->body('Periksa dan sesuaikan data sebelum menyimpan perubahan.')
                        ->send();
                }),
        ];
    }
}
