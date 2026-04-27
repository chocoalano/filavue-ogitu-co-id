<?php

namespace App\Filament\Resources\ShippingTargets\Pages;

use App\Filament\Resources\ShippingTargets\Exports\ShippingTargetExporter;
use App\Filament\Resources\ShippingTargets\ShippingTargetResource;
use App\Jobs\ProcessShippingTargetImportJob;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class ManageShippingTargets extends ManageRecords
{
    protected static string $resource = ShippingTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import_shipping_targets')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Target Pengiriman')
                ->modalDescription('File diproses melalui queue agar tetap stabil untuk data besar.')
                ->modalSubmitActionLabel('Upload & Proses')
                ->schema([
                    FileUpload::make('file')
                        ->label('File CSV/XLSX')
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/csv',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->rules([
                            'required',
                            'file',
                            'mimes:csv,xlsx',
                            'max:15360',
                        ])
                        ->required()
                        ->storeFiles(false)
                        ->visibility('private'),
                ])
                ->action(function (array $data): void {
                    try {
                        $fileState = $data['file'] ?? null;

                        if (is_array($fileState)) {
                            $fileState = Arr::first($fileState);
                        }

                        if (! $fileState instanceof TemporaryUploadedFile) {
                            throw new RuntimeException('File upload tidak valid.');
                        }

                        $extension = strtolower(trim((string) $fileState->getClientOriginalExtension()));

                        if (! in_array($extension, ['csv', 'xlsx'], true)) {
                            throw new RuntimeException('Format file tidak didukung. Gunakan CSV atau XLSX.');
                        }

                        $storedFileName = now()->format('YmdHis').'-'.Str::uuid().'.'.$extension;
                        $storedFilePath = Storage::disk('local')->putFileAs(
                            'imports/shipping-targets',
                            $fileState,
                            $storedFileName,
                        );

                        if (! is_string($storedFilePath) || $storedFilePath === '') {
                            throw new RuntimeException('Gagal menyimpan file import sementara.');
                        }

                        $initiatorUserId = auth()->id();
                        $normalizedUserId = is_numeric($initiatorUserId) ? (int) $initiatorUserId : null;

                        ProcessShippingTargetImportJob::dispatch(
                            storedFilePath: $storedFilePath,
                            initiatorUserId: $normalizedUserId,
                            originalFileName: $fileState->getClientOriginalName(),
                        );

                        Notification::make()
                            ->title('Import dijadwalkan')
                            ->body('File berhasil diupload dan sedang diproses di queue.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Gagal menjadwalkan import')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ExportAction::make()
                ->label('Export CSV/XLSX')
                ->exporter(ShippingTargetExporter::class),
        ];
    }
}
