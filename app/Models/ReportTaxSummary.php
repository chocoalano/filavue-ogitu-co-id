<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model ReportTaxSummary (Ringkasan PPh21 per Tahun/Bulan).
 *
 * Sumber data dari view SQL `vw_customer_bonus_pph21`.
 * Digunakan untuk query view pajak dan agregasi ad-hoc di resource.
 *
 * @property int $tahun_pajak
 * @property float|null $jumlah_bruto Tersedia dalam direct query (non-aggregated)
 * @property float|null $pph21        Tersedia dalam direct query (non-aggregated)
 */
class ReportTaxSummary extends BaseModel
{
    use HasFactory;

    protected $table = 'vw_customer_bonus_pph21';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_pajak' => 'integer',
            'jumlah_bruto' => 'decimal:2',
            'pph21' => 'decimal:2',
        ];
    }
}
