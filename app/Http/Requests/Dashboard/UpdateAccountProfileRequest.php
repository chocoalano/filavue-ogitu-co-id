<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $authenticatedCustomer = $this->resolveAuthenticatedCustomer();

        return self::profileRules(
            $authenticatedCustomer?->id,
            $authenticatedCustomer?->phone,
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function profileRules(
        ?int $customerId,
        ?string $currentPhone = null,
        bool $enforceUnique = true,
    ): array {
        $usernameRules = [
            'required',
            'string',
            'min:3',
            'max:30',
            'regex:/^[a-zA-Z0-9_.]+$/',
        ];
        $nikRules = [
            'required',
            'string',
            'digits:16',
        ];
        $emailRules = [
            'required',
            'string',
            'email',
            'max:255',
        ];
        $phoneRules = [
            'required',
            'string',
            'min:8',
            'max:20',
            'regex:/^[0-9]+$/',
        ];

        if ($enforceUnique) {
            // Username tetap unique — satu username hanya boleh dimiliki satu akun
            $usernameRules[] = Rule::unique('customers', 'username')->ignore($customerId);

            // NIK tidak unique, tapi maksimal 7 akun boleh menggunakan NIK yang sama
            $nikRules[] = function (string $attribute, mixed $value, \Closure $fail) use ($customerId): void {
                $nik = trim((string) $value);

                if ($nik === '') {
                    return;
                }

                $usageCount = Customer::query()
                    ->where('nik', $nik)
                    ->when($customerId !== null, fn ($query) => $query->where('id', '!=', $customerId))
                    ->count();

                if ($usageCount >= 7) {
                    $fail(self::sharedIdentityLimitMessage('NIK'));
                }
            };

            // Email tidak unique, tapi maksimal 7 akun boleh menggunakan email yang sama
            $emailRules[] = function (string $attribute, mixed $value, \Closure $fail) use ($customerId): void {
                $email = trim((string) $value);

                if ($email === '') {
                    return;
                }

                $usageCount = Customer::query()
                    ->where('email', $email)
                    ->when($customerId !== null, fn ($query) => $query->where('id', '!=', $customerId))
                    ->count();

                if ($usageCount >= 7) {
                    $fail(self::sharedIdentityLimitMessage('Email'));
                }
            };

            // Telepon tidak unique, tapi maksimal 7 akun boleh menggunakan nomor yang sama
            $currentPhoneNormalized = self::normalizePhone((string) ($currentPhone ?? ''));

            $phoneRules[] = function (string $attribute, mixed $value, \Closure $fail) use ($customerId, $currentPhoneNormalized): void {
                $phone = self::normalizePhone((string) $value);

                if ($phone === '' || $currentPhoneNormalized === $phone) {
                    return;
                }

                $usageCount = Customer::query()
                    ->where('phone', $phone)
                    ->when($customerId !== null, fn ($query) => $query->where('id', '!=', $customerId))
                    ->count();

                if ($usageCount >= 7) {
                    $fail(self::sharedIdentityLimitMessage('Nomor HP/WhatsApp'));
                }
            };
        }

        return [
            'username' => $usernameRules,
            'name' => ['required', 'string', 'max:255'],
            'nik' => $nikRules,
            'gender' => ['required', 'string', 'in:L,P'],
            'email' => $emailRules,
            'phone' => $phoneRules,
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account' => ['required', 'string', 'min:5', 'max:50', 'regex:/^[0-9]+$/'],
            'npwp_nama' => ['nullable', 'string', 'max:255'],
            'npwp_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9.\-]+$/'],
            'npwp_jk' => ['nullable', 'integer', 'in:1,2'],
            'npwp_date' => ['nullable', 'date'],
            'npwp_alamat' => ['nullable', 'string', 'max:1000'],
            'npwp_menikah' => ['nullable', 'string', 'in:Y,N'],
            'npwp_anak' => ['nullable', 'string', 'max:2', 'regex:/^\d+$/'],
            'npwp_kerja' => ['nullable', 'string', 'in:Y,N'],
            'npwp_office' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, underscore, dan titik.',
            'username.unique' => 'Username sudah digunakan oleh akun lain. Pilih username yang berbeda.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka sesuai KTP.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid. Contoh: nama@email.com.',
            'email.max' => 'Email maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.min' => 'Nomor telepon/WhatsApp minimal 8 karakter.',
            'phone.max' => 'Nomor telepon/WhatsApp maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon/WhatsApp hanya boleh berisi angka.',
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_name.max' => 'Nama bank maksimal 100 karakter.',
            'bank_account.required' => 'Nomor rekening wajib diisi.',
            'bank_account.min' => 'Nomor rekening minimal 5 digit.',
            'bank_account.max' => 'Nomor rekening maksimal 50 digit.',
            'bank_account.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'npwp_nama.max' => 'Nama NPWP maksimal 255 karakter.',
            'npwp_number.max' => 'Nomor NPWP maksimal 30 karakter.',
            'npwp_number.regex' => 'Nomor NPWP hanya boleh berisi angka, titik, atau tanda hubung.',
            'npwp_jk.in' => 'Jenis kelamin NPWP tidak valid.',
            'npwp_date.date' => 'Tanggal NPWP tidak valid.',
            'npwp_alamat.max' => 'Alamat NPWP maksimal 1000 karakter.',
            'npwp_menikah.in' => 'Status menikah NPWP tidak valid.',
            'npwp_anak.max' => 'Jumlah anak NPWP maksimal 2 digit.',
            'npwp_anak.regex' => 'Jumlah anak NPWP harus berupa angka.',
            'npwp_kerja.in' => 'Status kerja NPWP tidak valid.',
            'npwp_office.max' => 'Nama kantor NPWP maksimal 255 karakter.',
        ];
    }

    public static function sharedIdentityLimitMessage(string $fieldLabel): string
    {
        return "{$fieldLabel} ini sudah dipakai oleh 7 akun. Setiap {$fieldLabel} hanya bisa digunakan maksimal 7 akun. Jika data ini memang milik Anda, silakan hubungi admin.";
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *   username:string,
     *   name:string,
     *   nik:string,
     *   gender:string,
     *   email:string,
     *   phone:string,
     *   bank_name:string,
     *   bank_account:string,
     *   npwp_nama:string,
     *   npwp_number:string,
     *   npwp_jk:?int,
     *   npwp_date:string,
     *   npwp_alamat:string,
     *   npwp_menikah:?string,
     *   npwp_anak:string,
     *   npwp_kerja:?string,
     *   npwp_office:string
     * }
     */
    public static function normalizeForValidation(array $input): array
    {
        return [
            'username' => strtolower(trim((string) ($input['username'] ?? ''))),
            'name' => trim((string) ($input['name'] ?? '')),
            'nik' => preg_replace('/\D+/', '', (string) ($input['nik'] ?? '')) ?? '',
            'gender' => self::normalizeGender((string) ($input['gender'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'phone' => self::normalizePhone((string) ($input['phone'] ?? '')),
            'bank_name' => trim((string) ($input['bank_name'] ?? '')),
            'bank_account' => preg_replace('/\D+/', '', (string) ($input['bank_account'] ?? '')) ?? '',
            'npwp_nama' => trim((string) ($input['npwp_nama'] ?? '')),
            'npwp_number' => trim((string) ($input['npwp_number'] ?? '')),
            'npwp_jk' => self::normalizeNpwpGender($input['npwp_jk'] ?? null),
            'npwp_date' => trim((string) ($input['npwp_date'] ?? '')),
            'npwp_alamat' => trim((string) ($input['npwp_alamat'] ?? '')),
            'npwp_menikah' => self::normalizeYn($input['npwp_menikah'] ?? null),
            'npwp_anak' => preg_replace('/\D+/', '', (string) ($input['npwp_anak'] ?? '')) ?? '',
            'npwp_kerja' => self::normalizeYn($input['npwp_kerja'] ?? null),
            'npwp_office' => trim((string) ($input['npwp_office'] ?? '')),
        ];
    }

    /**
     * @return array{
     *   username:string,
     *   name:string,
     *   nik:string,
     *   gender:string,
     *   email:string,
     *   phone:string,
     *   bank_name:string,
     *   bank_account:string,
     *   npwp:?array{
     *     nama:?string,
     *     npwp:?string,
     *     jk:?int,
     *     npwp_date:?string,
     *     alamat:?string,
     *     menikah:?string,
     *     anak:?string,
     *     kerja:?string,
     *     office:?string
     *   }
     * }
     */
    public function payload(): array
    {
        $normalized = self::normalizeForValidation($this->all());

        return [
            'username' => $normalized['username'],
            'name' => $normalized['name'],
            'nik' => $normalized['nik'],
            'gender' => $normalized['gender'],
            'email' => $normalized['email'],
            'phone' => $normalized['phone'],
            'bank_name' => $normalized['bank_name'],
            'bank_account' => $normalized['bank_account'],
            'npwp' => $this->npwpPayload($normalized),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizeForValidation($this->all()));
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', trim($phone)) ?? '';
    }

    private static function normalizeGender(string $gender): string
    {
        $normalized = strtoupper(trim($gender));

        return match ($normalized) {
            'L', 'LAKI-LAKI', 'MALE', 'M' => 'L',
            'P', 'PEREMPUAN', 'FEMALE', 'F' => 'P',
            default => $normalized,
        };
    }

    private static function normalizeNpwpGender(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        return match ($normalized) {
            '1', 'L' => 1,
            '2', 'P' => 2,
            default => null,
        };
    }

    private static function normalizeYn(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if (in_array($normalized, ['Y', 'N'], true)) {
            return $normalized;
        }

        return null;
    }

    /**
     * @return array{
     *   nama:?string,
     *   npwp:?string,
     *   jk:?int,
     *   npwp_date:?string,
     *   alamat:?string,
     *   menikah:?string,
     *   anak:?string,
     *   kerja:?string,
     *   office:?string
     * }|null
     */
    private function npwpPayload(array $normalized): ?array
    {
        $payload = [
            'nama' => self::nullableString($normalized['npwp_nama'] ?? null),
            'npwp' => self::nullableString($normalized['npwp_number'] ?? null),
            'jk' => self::normalizeNpwpGender($normalized['npwp_jk'] ?? null),
            'npwp_date' => self::nullableString($normalized['npwp_date'] ?? null),
            'alamat' => self::nullableString($normalized['npwp_alamat'] ?? null),
            'menikah' => self::normalizeYn($normalized['npwp_menikah'] ?? null),
            'anak' => self::nullableString($normalized['npwp_anak'] ?? null),
            'kerja' => self::normalizeYn($normalized['npwp_kerja'] ?? null),
            'office' => self::nullableString($normalized['npwp_office'] ?? null),
        ];

        $hasAnyValue = collect($payload)->contains(fn (mixed $value): bool => $value !== null && $value !== '');

        if (! $hasAnyValue) {
            return null;
        }

        return $payload;
    }

    private static function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function resolveAuthenticatedCustomer(): ?Customer
    {
        $customer = $this->user('customer');

        if ($customer instanceof Customer) {
            return $customer;
        }

        $tokenable = $this->user('sanctum');

        return $tokenable instanceof Customer ? $tokenable : null;
    }
}
