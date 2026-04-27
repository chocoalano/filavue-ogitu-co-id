<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model Customer (Member / Mitra).
 *
 * Merepresentasikan member MLM sekaligus customer e-commerce.
 * Memiliki struktur binary tree (upline/downline) dan sponsor network.
 *
 * @property int $id
 * @property int|null $sponsor_id Sponsor referral yang merekrut
 * @property string|null $ref_code Kode referral unik
 * @property string|null $username Username unik member
 * @property string|null $nik Nomor Induk Kependudukan
 * @property string $name Nama lengkap
 * @property string $email Email untuk login dan komunikasi
 * @property string|null $phone Nomor telepon/WhatsApp
 * @property string $password Password hash
 * @property string|null $gender Jenis kelamin (male|female|L|P)
 * @property string|null $alamat Alamat lengkap (legacy)
 * @property string|null $address Alamat singkat
 * @property int|null $city_id ID kota
 * @property int|null $province_id ID provinsi
 * @property string|null $remember_token Token remember me
 * @property Carbon|null $email_verified_at
 * @property string|null $ewallet_id ID unik e-wallet
 * @property float $ewallet_saldo Saldo e-wallet
 * @property float $bonus_pending Bonus yang belum diproses
 * @property float $bonus_processed Bonus yang sudah diproses
 * @property string|null $bank_name Nama bank untuk penarikan
 * @property string|null $bank_account Nomor rekening bank
 * @property string|null $description Catatan tambahan
 * @property int|null $package_id Paket sesuai omset
 * @property int $sponsor_left Jumlah member disponsori kaki kiri
 * @property int $pv Point value
 * @property float $omzet Omset personal
 * @property float $omzet_group Omset group keseluruhan
 * @property string|null $level Level member (Associate|Senior Associate|Executive|Director)
 * @property bool $is_stockist Apakah member adalah stockist
 * @property bool $network_generated Apakah network sudah digenerate
 * @property int $status Status (1=prospek, 2=pasif, 3=aktif)
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'customer';

    protected static function booted(): void
    {
        static::created(function (self $customer): void {
            $updates = [];

            if (
                blank($customer->ref_code)
                && Schema::hasColumn($customer->getTable(), 'ref_code')
            ) {
                $updates['ref_code'] = self::generateUniqueRefCode($customer);
            }

            if (
                blank($customer->ewallet_id)
                && Schema::hasColumn($customer->getTable(), 'ewallet_id')
            ) {
                $updates['ewallet_id'] = self::generateUniqueEwalletId($customer);
            }

            if ($updates === []) {
                return;
            }

            $customer->forceFill($updates);
            $customer->saveQuietly();
        });
    }

    /** @var list<string> */
    protected $fillable = [
        'sponsor_id',
        'ref_code',
        'username',
        'nik',
        'name',
        'email',
        'phone',
        'password',
        'gender',
        'alamat',
        'address',
        'city_id',
        'province_id',
        'email_verified_at',
        'ewallet_id',
        'ewallet_saldo',
        'bonus_pending',
        'bonus_processed',
        'bank_name',
        'bank_account',
        'description',
        'package_id',
        'sponsor_left',
        'pv',
        'omzet',
        'omzet_group',
        'level',
        'is_stockist',
        'stockist_kabupaten_id',
        'stockist_kabupaten_name',
        'stockist_province_id',
        'stockist_province_name',
        'network_generated',
        'status',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ewallet_saldo' => 'decimal:2',
            'bonus_pending' => 'decimal:2',
            'bonus_processed' => 'decimal:2',
            'pv' => 'integer',
            'omzet' => 'decimal:2',
            'omzet_group' => 'decimal:2',
            'is_stockist' => 'boolean',
            'network_generated' => 'boolean',
            'status' => 'integer',
        ];
    }

    private static function generateUniqueRefCode(self $customer): string
    {
        $customerId = max(1, (int) $customer->getKey());
        $timestamp = ($customer->created_at ?? now())->format('ymdHis');
        $increment = $customerId;

        do {
            $refCode = $timestamp.'-'.self::formatIncrement($increment);
            $alreadyUsed = self::query()
                ->where('id', '!=', $customerId)
                ->where('ref_code', $refCode)
                ->exists();
            $increment++;
        } while ($alreadyUsed);

        return $refCode;
    }

    private static function generateUniqueEwalletId(self $customer): string
    {
        $customerId = max(1, (int) $customer->getKey());
        $date = ($customer->created_at ?? now())->format('ymd');
        $increment = $customerId;

        do {
            $ewalletId = 'EW-'.$date.'-'.self::formatIncrement($increment);
            $alreadyUsed = self::query()
                ->where('id', '!=', $customerId)
                ->where('ewallet_id', $ewalletId)
                ->exists();
            $increment++;
        } while ($alreadyUsed);

        return $ewalletId;
    }

    private static function formatIncrement(int $increment): string
    {
        return str_pad((string) $increment, 4, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────────────
    //  Relasi Binary Tree & Sponsor
    // ──────────────────────────────────────────────

    /**
     * Sponsor yang merekrut customer ini.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    /**
     * Paket member berdasarkan total omset.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(CustomerPackage::class, 'package_id');
    }

    /**
     * Semua member yang disponsori oleh customer ini.
     */
    public function downlines(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_id');
    }

    // ──────────────────────────────────────────────
    //  Relasi E-Commerce
    // ──────────────────────────────────────────────

    /**
     * Alamat-alamat pengiriman customer.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Keranjang belanja aktif.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Semua order yang dibuat customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Review produk yang ditulis customer.
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Wishlist customer.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // ──────────────────────────────────────────────
    //  Relasi Bonus & Wallet
    // ──────────────────────────────────────────────

    /**
     * Transaksi e-wallet customer.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(CustomerWalletTransaction::class);
    }

    /**
     * Bonus gabungan (summary) customer.
     */
    public function bonuses(): HasMany
    {
        return $this->hasMany(CustomerBonus::class, 'member_id');
    }

    /**
     * Bonus sponsor dari downline yang direkrut.
     */
    public function bonusSponsors(): HasMany
    {
        return $this->hasMany(CustomerBonusSponsor::class, 'member_id');
    }

    /**
     * Bonus royalty dari jaringan.
     */
    public function bonusRoyalties(): HasMany
    {
        return $this->hasMany(CustomerBonusRoyalty::class, 'member_id');
    }

    /**
     * Bonus cashback dari pembelian.
     */
    public function bonusCashbacks(): HasMany
    {
        return $this->hasMany(CustomerBonusCashback::class, 'member_id');
    }

    /**
     * Bonus reward (promotion/lifetime).
     */
    public function bonusRewards(): HasMany
    {
        return $this->hasMany(CustomerBonusReward::class, 'member_id');
    }

    // ──────────────────────────────────────────────
    //  Relasi Network & Rewards
    // ──────────────────────────────────────────────

    /**
     * Matrix jaringan sponsor.
     */
    public function networkMatrixes(): HasMany
    {
        return $this->hasMany(CustomerNetworkMatrix::class, 'member_id');
    }

    /**
     * Reward yang diraih customer.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(CustomerReward::class, 'member_id');
    }

    /**
     * BV Reward tracking.
     */
    public function bvRewards(): HasMany
    {
        return $this->hasMany(CustomerBvReward::class, 'member_id');
    }

    /**
     * Data NPWP customer.
     */
    public function npwp(): HasOne
    {
        return $this->hasOne(CustomerNpwp::class, 'member_id');
    }

    public function addBalance(float $amount, ?string $description = null): bool
    {
        if (! $this->exists || $amount <= 0) {
            return false;
        }

        /** @var bool $saved */
        $saved = DB::transaction(function () use ($amount, $description): bool {
            /** @var self|null $lockedCustomer */
            $lockedCustomer = self::query()
                ->whereKey($this->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedCustomer) {
                return false;
            }

            $balanceBefore = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            $lockedCustomer->increment('ewallet_saldo', $amount);
            $lockedCustomer->refresh();

            $balanceAfter = (float) ($lockedCustomer->ewallet_saldo ?? 0);

            $lockedCustomer->walletTransactions()->create([
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'payment_method' => 'admin_inject',
                'transaction_ref' => 'INJECT-'.strtoupper(Str::random(10)),
                'notes' => $description ?? 'Top up ewallet',
                'completed_at' => now(),
            ]);

            $this->setRawAttributes($lockedCustomer->getAttributes(), sync: true);

            return true;
        });

        return $saved;
    }

    public function pointAccount()
    {
        return $this->hasOne(CustomerPointAccount::class, 'customer_id');
    }

    public function pointLedgers()
    {
        return $this->hasMany(CustomerPointLedger::class, 'customer_id');
    }

    public function gachaDraws()
    {
        return $this->hasMany(GachaDraw::class, 'customer_id');
    }

    public function rewardInstances()
    {
        return $this->hasMany(CustomerRewardInstance::class, 'customer_id');
    }

    public function pointRedemptions()
    {
        return $this->hasMany(PointRedemption::class, 'customer_id');
    }
}
