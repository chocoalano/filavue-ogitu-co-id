<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model ReportAnalytic (Laporan PPh21 Bonus Customer).
 *
 * Menggunakan sumber data dari view SQL `vw_customer_bonus_pph21`.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $tanggal
 * @property string|null $username
 * @property string|null $name
 * @property string|null $email
 * @property string|null $no_telepon
 * @property string|null $npwp
 * @property int|null $tahun_pajak
 * @property string|null $nik
 * @property string|null $fullname
 * @property string|null $alamat
 * @property float|null $jumlah_bruto
 * @property int|null $tarif
 * @property float|null $pph21
 */
class ReportAnalytic extends BaseModel
{
    use HasFactory;

    protected $table = 'vw_customer_bonus_pph21';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tahun_pajak' => 'integer',
            'jumlah_bruto' => 'decimal:2',
            'tarif' => 'integer',
            'pph21' => 'decimal:2',
        ];
    }
}
