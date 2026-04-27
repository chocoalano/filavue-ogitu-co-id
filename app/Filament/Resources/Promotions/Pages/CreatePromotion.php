<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Schemas\PromotionForm;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('autofill')
                ->label('Isi Data Contoh')
                ->icon('heroicon-m-beaker')
                ->color('gray')
                ->action(function (): void {
                    $this->form->fill(PromotionForm::testingAutofillData());

                    Notification::make()
                        ->success()
                        ->title('Form berhasil diisi dengan data acak yang valid.')
                        ->body('Periksa dan sesuaikan data sebelum menyimpan.')
                        ->send();
                }),
        ];
    }
}
