<?php

namespace App\Filament\Imports;

use App\Models\JneOrigin;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class JneOriginImporter extends Importer
{
    protected static ?string $model = JneOrigin::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('origin_code')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('origin_name')
                ->requiredMapping()
                ->rules(['required', 'max:150']),
        ];
    }

    public function resolveRecord(): JneOrigin
    {
        return new JneOrigin;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your jne origin import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
