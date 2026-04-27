<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerNpwp (Data NPWP Customer).
 *
 * Informasi NPWP dan data perpajakan member
 * untuk keperluan pemotongan PPh 21.
 *
 * @property int $id
 * @property int $member_id Member pemilik NPWP
 * @property string $nama Nama pada NPWP
 * @property string $npwp Nomor NPWP
 * @property int $jk Jenis kelamin
 * @property \Illuminate\Support\Carbon $npwp_date Tanggal NPWP
 * @property string $alamat Alamat NPWP
 * @property string $menikah Status pernikahan (Y|N)
 * @property string $anak Jumlah anak (0-3)
 * @property string $kerja Status bekerja (Y|N)
 * @property string $office Nama kantor
 * @property \Illuminate\Support\Carbon $created
 * @property string $createdby Pembuat record
 * @property \Illuminate\Support\Carbon $updated
 * @property string $updatedby Pengubah terakhir
 */
class CustomerNpwp extends BaseModel
{
    public $timestamps = false;

    protected $table = 'customer_npwp';

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'nama',
        'npwp',
        'jk',
        'npwp_date',
        'alamat',
        'menikah',
        'anak',
        'kerja',
        'office',
        'created',
        'createdby',
        'updated',
        'updatedby',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'npwp_date' => 'date',
            'created' => 'datetime',
            'updated' => 'datetime',
        ];
    }

    /**
     * Member pemilik NPWP.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }
}
