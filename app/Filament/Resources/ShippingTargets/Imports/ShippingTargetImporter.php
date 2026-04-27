<?php

namespace App\Filament\Resources\ShippingTargets\Imports;

use App\Models\ShippingTarget;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ShippingTargetImporter extends Importer
{
    protected static ?string $model = ShippingTarget::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('three_lc_code')
                ->label('3LC Code')
                ->requiredMapping()
                ->guess([
                    'three_lc',
                    'lc_code',
                    '3lc_code',
                    'code',
                    'kode',
                ])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('country')
                ->label('Negara')
                ->guess(['negara'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('province_id')
                ->label('Province ID')
                ->integer()
                ->guess(['provinsi_id', 'id_province', 'id_provinsi'])
                ->rules(['nullable', 'integer', 'min:1']),
            ImportColumn::make('province')
                ->label('Provinsi')
                ->requiredMapping()
                ->guess(['provinsi'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('city_id')
                ->label('City ID')
                ->integer()
                ->guess(['kota_id', 'id_city', 'id_kota'])
                ->rules(['nullable', 'integer', 'min:1']),
            ImportColumn::make('city')
                ->label('Kota / Kabupaten')
                ->requiredMapping()
                ->guess(['kota', 'kabupaten', 'kota_kabupaten'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('district')
                ->label('Kecamatan')
                ->requiredMapping()
                ->guess(['kecamatan'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('district_lion')
                ->label('Kecamatan (Lion)')
                ->requiredMapping()
                ->guess(['kecamatan_lion', 'district_lionparcel'])
                ->rules(['required', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ShippingTarget
    {
        return ShippingTarget::firstOrNew([
            'three_lc_code' => strtoupper(trim((string) ($this->data['three_lc_code'] ?? ''))),
        ]);
    }

    protected function beforeValidate(): void
    {
        $this->data['three_lc_code'] = strtoupper(trim((string) ($this->data['three_lc_code'] ?? '')));
        $this->data['country'] = $this->normalizeCountry($this->data['country'] ?? null);
        $this->data['province_id'] = $this->toNullableInteger($this->data['province_id'] ?? null);
        $this->data['province'] = trim((string) ($this->data['province'] ?? ''));
        $this->data['city_id'] = $this->toNullableInteger($this->data['city_id'] ?? null);
        $this->data['city'] = trim((string) ($this->data['city'] ?? ''));
        $this->data['district'] = trim((string) ($this->data['district'] ?? ''));
        $this->data['district_lion'] = strtoupper(trim((string) ($this->data['district_lion'] ?? '')));
    }

    protected function beforeFill(): void
    {
        if (! $this->record instanceof ShippingTarget) {
            return;
        }

        $this->record->country = $this->normalizeCountry($this->data['country'] ?? $this->record->country);
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return 'Import target pengiriman selesai';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = Number::format($import->successful_rows).' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimpor.';
        }

        return $body;
    }

    private function normalizeCountry(mixed $value): string
    {
        $country = trim((string) $value);

        return $country !== '' ? $country : 'Indonesia';
    }

    private function toNullableInteger(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }
}
