<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model NewsletterSubscriber (Pelanggan Newsletter).
 *
 * Email subscriber untuk newsletter/mailing list.
 *
 * @property int $id
 * @property string $email Email subscriber
 * @property \Illuminate\Support\Carbon $subscribed_at Tanggal berlangganan
 * @property string|null $ip_address IP address saat subscribe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class NewsletterSubscriber extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'email',
        'subscribed_at',
        'ip_address',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
        ];
    }
}
