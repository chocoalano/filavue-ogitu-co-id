<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Kategori akar penyebab bug setelah dianalisis oleh tim engineering.
 *
 * Digunakan untuk membedakan apakah bug disebabkan oleh kesalahan pengguna
 * (human error) atau memang terdapat kerusakan pada sistem (system error),
 * serta kategori teknis lainnya.
 */
enum BugErrorCategory: string implements HasColor, HasDescription, HasLabel
{
    /**
     * Kesalahan yang dilakukan pengguna: input salah, prosedur tidak diikuti,
     * atau ekspektasi pengguna tidak sesuai desain sistem.
     */
    case HumanError = 'human_error';

    /**
     * Bug pada logika kode, kalkulasi salah, atau fitur tidak berjalan sesuai spec.
     */
    case SystemError = 'system_error';

    /**
     * Masalah tampilan, layout rusak, komponen tidak responsif, atau UX membingungkan.
     */
    case UiUxError = 'ui_ux_error';

    /**
     * Sistem lambat, timeout, memory leak, atau masalah skalabilitas.
     */
    case PerformanceIssue = 'performance_issue';

    /**
     * Data tidak konsisten, kalkulasi bonus/stok salah, atau data hilang/corrupt.
     */
    case DataError = 'data_error';

    /**
     * Celah keamanan, akses tidak terotorisasi, atau kebocoran data sensitif.
     */
    case SecurityIssue = 'security_issue';

    /**
     * Salah konfigurasi environment, server, third-party service, atau integrasi API.
     */
    case ConfigurationError = 'configuration_error';

    /**
     * Belum dapat ditentukan kategorinya, perlu investigasi lebih lanjut.
     */
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            BugErrorCategory::HumanError => 'Human Error',
            BugErrorCategory::SystemError => 'System Error',
            BugErrorCategory::UiUxError => 'UI/UX Error',
            BugErrorCategory::PerformanceIssue => 'Performance Issue',
            BugErrorCategory::DataError => 'Data Error',
            BugErrorCategory::SecurityIssue => 'Security Issue',
            BugErrorCategory::ConfigurationError => 'Configuration Error',
            BugErrorCategory::Unknown => 'Unknown',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            BugErrorCategory::HumanError => 'info',
            BugErrorCategory::SystemError => 'danger',
            BugErrorCategory::UiUxError => 'warning',
            BugErrorCategory::PerformanceIssue => 'warning',
            BugErrorCategory::DataError => 'danger',
            BugErrorCategory::SecurityIssue => 'danger',
            BugErrorCategory::ConfigurationError => 'warning',
            BugErrorCategory::Unknown => 'gray',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            BugErrorCategory::HumanError => 'Disebabkan oleh kesalahan pengguna, bukan bug sistem',
            BugErrorCategory::SystemError => 'Terdapat cacat pada logika atau kode sistem',
            BugErrorCategory::UiUxError => 'Masalah pada tampilan atau pengalaman pengguna',
            BugErrorCategory::PerformanceIssue => 'Sistem lambat atau tidak efisien',
            BugErrorCategory::DataError => 'Data tidak valid, hilang, atau tidak konsisten',
            BugErrorCategory::SecurityIssue => 'Potensi celah keamanan atau pelanggaran akses',
            BugErrorCategory::ConfigurationError => 'Kesalahan konfigurasi sistem atau layanan eksternal',
            BugErrorCategory::Unknown => 'Penyebab belum dapat ditentukan',
        };
    }

    /**
     * Apakah kategori ini merupakan kesalahan pengguna (bukan bug sistem).
     */
    public function isHumanError(): bool
    {
        return $this === BugErrorCategory::HumanError;
    }

    /**
     * Apakah kategori ini membutuhkan tindakan dari tim engineering.
     */
    public function requiresEngineeringAction(): bool
    {
        return in_array($this, [
            BugErrorCategory::SystemError,
            BugErrorCategory::DataError,
            BugErrorCategory::SecurityIssue,
            BugErrorCategory::PerformanceIssue,
            BugErrorCategory::ConfigurationError,
        ]);
    }
}
