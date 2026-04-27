<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * Model User (Admin / Back-Office).
 *
 * Representasi pengguna back-office yang mengelola sistem.
 * Mendukung two-factor authentication via Laravel Fortify.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $role superadmin|admin
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Konten yang dibuat oleh user ini.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'created_by');
    }

    public function createdPointLedgers()
    {
        return $this->hasMany(CustomerPointLedger::class, 'created_by');
    }

    public function createdInventoryLedgers()
    {
        return $this->hasMany(RewardInventoryLedger::class, 'created_by');
    }

    public function createdGachaCampaigns()
    {
        return $this->hasMany(GachaCampaign::class, 'created_by');
    }

    public function updatedGachaCampaigns()
    {
        return $this->hasMany(GachaCampaign::class, 'updated_by');
    }

    public function generatedGachaBoards()
    {
        return $this->hasMany(GachaBoard::class, 'generated_by');
    }

    public function handledGachaDraws()
    {
        return $this->hasMany(GachaDraw::class, 'handled_by_user_id');
    }
}
