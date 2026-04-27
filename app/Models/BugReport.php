<?php

namespace App\Models;

use App\Enums\BugErrorCategory;
use App\Enums\BugPriority;
use App\Enums\BugReporterType;
use App\Enums\BugSeverity;
use App\Enums\BugSource;
use App\Enums\BugStatus;
use App\Enums\MobileType;
use App\Enums\Platform;
use App\Enums\WebScreen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Model BugReport (Laporan Bug).
 *
 * Merepresentasikan satu laporan bug yang dikirimkan oleh customer,
 * user internal, atau pengguna anonymous. Mendukung platform web dan mobile,
 * dilengkapi informasi environment, alur triase (severity, priority, status),
 * kategorisasi akar masalah (error_category), dan log komentar penanganan.
 *
 * @property int $id
 * @property BugReporterType $reporter_type Tipe pelapor (customer|user|anonymous)
 * @property int|null $reporter_id ID customer atau user
 * @property string|null $reporter_name Nama pelapor jika anonymous
 * @property string|null $reporter_email Email pelapor jika anonymous
 * @property string $title Judul singkat bug
 * @property string $description Deskripsi lengkap bug
 * @property string|null $steps_to_reproduce Langkah reproduksi
 * @property string|null $expected_behavior Perilaku yang diharapkan
 * @property string|null $actual_behavior Perilaku yang sebenarnya terjadi
 * @property Platform $platform Platform: web|mobile
 * @property BugSource $source Sumber: storefront|admin_console
 * @property WebScreen|null $web_screen Ukuran layar web: desktop|tablet|smartphone
 * @property MobileType|null $mobile_type Tipe mobile OS: android|ios
 * @property string|null $page_url URL halaman tempat bug ditemukan
 * @property string|null $browser Nama browser
 * @property string|null $browser_version Versi browser
 * @property string|null $os Sistem operasi
 * @property string|null $os_version Versi sistem operasi
 * @property string|null $device_model Model perangkat mobile
 * @property string|null $app_version Versi aplikasi
 * @property string|null $screen_resolution Resolusi layar
 * @property BugErrorCategory|null $error_category Kategori akar masalah setelah dianalisis
 * @property BugSeverity $severity Keparahan: critical|high|medium|low
 * @property BugPriority $priority Prioritas: urgent|high|medium|low
 * @property BugStatus $status Status penanganan
 * @property int|null $duplicate_of_id ID bug yang ini duplikatnya
 * @property int|null $assigned_to ID user yang ditugaskan
 * @property string|null $resolution_note Catatan resolusi/penolakan
 * @property Carbon|null $resolved_at Waktu selesai diperbaiki
 * @property Carbon|null $closed_at Waktu laporan ditutup
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BugReport extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (self $bugReport): void {
            $bugReport->attachments()->get()->each->delete();
        });
    }

    /** @var list<string> */
    protected $fillable = [
        'reporter_type',
        'reporter_id',
        'reporter_name',
        'reporter_email',
        'title',
        'description',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'platform',
        'source',
        'web_screen',
        'mobile_type',
        'page_url',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'device_model',
        'app_version',
        'screen_resolution',
        'error_category',
        'severity',
        'priority',
        'status',
        'duplicate_of_id',
        'assigned_to',
        'resolution_note',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'reporter_type' => BugReporterType::class,
            'platform' => Platform::class,
            'source' => BugSource::class,
            'web_screen' => WebScreen::class,
            'mobile_type' => MobileType::class,
            'error_category' => BugErrorCategory::class,
            'severity' => BugSeverity::class,
            'priority' => BugPriority::class,
            'status' => BugStatus::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Bug yang ini merupakan duplikat dari.
     *
     * @return BelongsTo<BugReport, $this>
     */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(BugReport::class, 'duplicate_of_id');
    }

    /**
     * Bug-bug lain yang merupakan duplikat dari laporan ini.
     *
     * @return HasMany<BugReport, $this>
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(BugReport::class, 'duplicate_of_id');
    }

    /**
     * User admin/developer yang ditugaskan menangani bug ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * File lampiran (screenshot, log, dll) untuk bug ini.
     *
     * @return HasMany<BugReportAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(BugReportAttachment::class);
    }

    /**
     * Seluruh komentar dan log penanganan bug ini (urut kronologis).
     *
     * @return HasMany<BugReportComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BugReportComment::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Hanya komentar yang terlihat publik (bukan internal note).
     *
     * @return HasMany<BugReportComment, $this>
     */
    public function publicComments(): HasMany
    {
        return $this->hasMany(BugReportComment::class)
            ->whereNotIn('type', ['internal_note'])
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Hanya langkah penanganan terdokumentasi (handling_step + resolution).
     *
     * @return HasMany<BugReportComment, $this>
     */
    public function handlingSteps(): HasMany
    {
        return $this->hasMany(BugReportComment::class)
            ->whereIn('type', ['handling_step', 'resolution'])
            ->orderBy('step_number')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Customer yang melaporkan bug (jika reporter_type = customer).
     *
     * @return BelongsTo<Customer, $this>
     */
    public function reporterCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'reporter_id');
    }

    /**
     * User internal yang melaporkan bug (jika reporter_type = user).
     *
     * @return BelongsTo<User, $this>
     */
    public function reporterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Cek apakah laporan ini masih aktif (belum selesai/ditutup).
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Cek apakah bug ini dilaporkan dari platform mobile.
     */
    public function isMobile(): bool
    {
        return $this->platform === Platform::Mobile;
    }

    /**
     * Cek apakah bug ini dilaporkan dari platform web.
     */
    public function isWeb(): bool
    {
        return $this->platform === Platform::Web;
    }

    /**
     * Cek apakah bug ini dikategorikan sebagai human error.
     */
    public function isHumanError(): bool
    {
        return $this->error_category?->isHumanError() ?? false;
    }

    /**
     * Cek apakah bug ini membutuhkan tindakan engineering.
     */
    public function requiresEngineeringAction(): bool
    {
        return $this->error_category?->requiresEngineeringAction() ?? true;
    }
}
