<?php

namespace App\Filament\Resources\CommodityCodes\Imports;

use App\Models\CommodityCode;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CommodityCodeImporter extends Importer
{
    protected static ?string $model = CommodityCode::class;

    public function getJobQueue(): ?string
    {
        return 'default';
    }

    public function getJobConnection(): ?string
    {
        return 'database';
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label('Kode')
                ->requiredMapping()
                ->guess([
                    'kode',
                    'commodity_code',
                    'kode_komoditas',
                ])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name')
                ->label('Nama Komoditas')
                ->requiredMapping()
                ->guess([
                    'nama',
                    'commodity_name',
                    'nama_komoditas',
                ])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('dangerous_good')
                ->label('Barang Berbahaya')
                ->guess([
                    'barang_berbahaya',
                    'dangerous',
                ])
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_quarantine')
                ->label('Wajib Karantina')
                ->guess([
                    'quarantine',
                    'wajib_karantina',
                    'karantina',
                ])
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): CommodityCode
    {
        return CommodityCode::firstOrNew([
            'code' => trim((string) ($this->data['code'] ?? '')),
        ]);
    }

    protected function beforeValidate(): void
    {
        $this->data['code'] = trim((string) ($this->data['code'] ?? ''));
        $this->data['name'] = trim((string) ($this->data['name'] ?? ''));
        $this->data['dangerous_good'] = $this->toBoolean($this->data['dangerous_good'] ?? null, true);
        $this->data['is_quarantine'] = $this->toBoolean($this->data['is_quarantine'] ?? null, true);
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        return 'Import kode komoditas selesai';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = Number::format($import->successful_rows).' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal diimpor.';
        }

        return $body;
    }

    private function toBoolean(mixed $value, bool $default = false): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'ya', 'y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'tidak', 'n'], true)) {
            return false;
        }

        return $default;
    }
}
