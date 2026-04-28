<?php

namespace App\Filament\Imports;

use App\Models\JneDestination;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class JneDestinationImporter extends Importer
{
    protected static ?string $model = JneDestination::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('country_name')
                ->label('Country Name')
                ->guess(['country_name', 'COUNTRY_NAME', 'Country Name', 'country', 'COUNTRY'])
                ->exampleHeader('country_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:100']),

            ImportColumn::make('province_name')
                ->label('Province Name')
                ->guess(['province_name', 'PROVINCE_NAME', 'Province Name', 'province', 'PROVINCE'])
                ->exampleHeader('province_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:150']),

            ImportColumn::make('city_name')
                ->label('City Name')
                ->guess(['city_name', 'CITY_NAME', 'City Name', 'city', 'CITY', 'POSTAL_CITY'])
                ->exampleHeader('city_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:150']),

            ImportColumn::make('district_name')
                ->label('District Name')
                ->guess(['district_name', 'DISTRICT_NAME', 'District Name', 'district', 'DISTRICT', 'DISTRICT_DESTINATION'])
                ->exampleHeader('district_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:150']),

            ImportColumn::make('subdistrict_name')
                ->label('Subdistrict Name')
                ->guess(['subdistrict_name', 'SUBDISTRICT_NAME', 'Subdistrict Name', 'subdistrict', 'SUBDISTRICT', 'POSTAL_DESC'])
                ->exampleHeader('subdistrict_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:150']),

            ImportColumn::make('zip_code')
                ->label('Zip Code')
                ->guess(['zip_code', 'ZIP_CODE', 'Zip Code', 'postal_code', 'POSTAL_CODE', 'POSTA'])
                ->exampleHeader('zip_code')
                ->rules(['nullable', 'string', 'max:10']),

            ImportColumn::make('tariff_code')
                ->label('Tariff Code')
                ->guess(['tariff_code', 'TARIFF_CODE', 'Tariff Code', 'tariff', 'POSTAL_DES'])
                ->exampleHeader('tariff_code')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:20']),
        ];
    }

    public function resolveRecord(): JneDestination
    {
        return JneDestination::firstOrNew([
            'tariff_code' => trim((string) ($this->data['tariff_code'] ?? '')),
            'zip_code' => trim((string) ($this->data['zip_code'] ?? '')),
            'subdistrict_name' => trim((string) ($this->data['subdistrict_name'] ?? '')),
        ]);
    }

    protected function beforeValidate(): void
    {
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $this->data[$key] = trim($value);
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import JNE destination selesai. '
            . Number::format($import->successful_rows) . ' '
            . Str::plural('row', $import->successful_rows) . ' berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' '
                . Str::plural('row', $failedRowsCount) . ' gagal diimport.';
        }

        return $body;
    }
}
