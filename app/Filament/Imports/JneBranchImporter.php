<?php

namespace App\Filament\Imports;

use App\Models\JneBranch;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class JneBranchImporter extends Importer
{
    protected static ?string $model = JneBranch::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('branch_code')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('branch_name')
                ->requiredMapping()
                ->rules(['required', 'max:150']),
        ];
    }

    public function resolveRecord(): JneBranch
    {
        return new JneBranch;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your jne branch import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
