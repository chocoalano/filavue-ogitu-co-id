<?php

namespace App\Http\Requests\Auth;

use App\Models\Customer;
use App\Services\Auth\ReferralContextService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    private bool $hasResolvedSponsorFromCode = false;

    private bool $hasResolvedSponsorFromUsername = false;

    private ?Customer $resolvedSponsorFromCode = null;

    private ?Customer $resolvedSponsorFromUsername = null;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Rule>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/', 'unique:customers,username'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'telp' => ['required', 'string', 'min:8', 'max:16'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'gender' => ['required', 'string', 'in:L,P'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'referral_username' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/'],
            'referral_code' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'terms' => ['required', 'accepted'],
        ];
    }

    /** @return array<int, \Closure(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->filled('email')
                    && ! $validator->errors()->has('email')
                    && Customer::query()->where('email', $this->input('email'))->count() >= 7
                ) {
                    $validator->errors()->add('email', 'Email ini sudah digunakan oleh maksimal 7 akun. Gunakan email lain.');
                }

                if (
                    $this->filled('telp')
                    && ! $validator->errors()->has('telp')
                    && Customer::query()->where('phone', $this->input('telp'))->count() >= 7
                ) {
                    $validator->errors()->add('telp', 'Nomor WhatsApp ini sudah digunakan oleh maksimal 7 akun. Gunakan nomor lain.');
                }

                if (
                    $this->filled('nik')
                    && ! $validator->errors()->has('nik')
                    && Customer::query()->where('nik', $this->input('nik'))->count() >= 7
                ) {
                    $validator->errors()->add('nik', 'NIK ini sudah digunakan oleh maksimal 7 akun.');
                }

                if (
                    $this->filled('referral_username')
                    && ! $validator->errors()->has('referral_username')
                    && $this->resolveSponsorFromUsername() === null
                ) {
                    $validator->errors()->add('referral_username', 'Username referral tidak ditemukan.');
                }

                if (
                    $this->filled('referral_code')
                    && ! $validator->errors()->has('referral_code')
                    && $this->resolveSponsorFromCode() === null
                ) {
                    $validator->errors()->add('referral_code', 'Kode referral tidak ditemukan.');
                }

                if (
                    ! $validator->errors()->has('referral_username')
                    && ! $validator->errors()->has('referral_code')
                    && $this->filled('referral_username')
                    && $this->filled('referral_code')
                ) {
                    $sponsorFromUsername = $this->resolveSponsorFromUsername();
                    $sponsorFromCode = $this->resolveSponsorFromCode();

                    if (
                        $sponsorFromUsername !== null
                        && $sponsorFromCode !== null
                        && (int) $sponsorFromUsername->getKey() !== (int) $sponsorFromCode->getKey()
                    ) {
                        $validator->errors()->add('referral_username', 'Username referral tidak sesuai dengan kode referral.');
                    }
                }
            },
        ];
    }

    public function sponsor(): ?Customer
    {
        return $this->resolveSponsorFromUsername() ?? $this->resolveSponsorFromCode();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, underscore, dan titik.',
            'username.unique' => 'Username sudah digunakan, pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang, maksimal 255 karakter.',
            'telp.required' => 'Nomor WhatsApp wajib diisi.',
            'telp.min' => 'Nomor WhatsApp minimal 8 digit.',
            'telp.max' => 'Nomor WhatsApp maksimal 16 digit.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'referral_username.min' => 'Username referral minimal 3 karakter.',
            'referral_username.max' => 'Username referral maksimal 30 karakter.',
            'referral_username.regex' => 'Username referral hanya boleh berisi huruf, angka, underscore, dan titik.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'terms.required' => 'Anda harus menyetujui Syarat & Ketentuan.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedNik = preg_replace('/\D+/', '', (string) $this->input('nik', '')) ?? '';
        $normalizedAlamat = trim((string) $this->input('alamat', ''));
        $normalizedReferralCode = trim((string) $this->input('referral_code', ''));
        $normalizedReferralUsername = preg_replace('/^@+/', '', trim((string) $this->input('referral_username', ''))) ?? '';

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'username' => strtolower(trim((string) $this->input('username', ''))),
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'telp' => preg_replace('/\s+/', '', trim((string) $this->input('telp', ''))) ?? '',
            'nik' => $normalizedNik === '' ? null : $normalizedNik,
            'gender' => strtoupper(trim((string) $this->input('gender', ''))),
            'alamat' => $normalizedAlamat === '' ? null : $normalizedAlamat,
            'referral_code' => $normalizedReferralCode === '' ? null : $normalizedReferralCode,
            'referral_username' => $normalizedReferralUsername === '' ? null : strtolower($normalizedReferralUsername),
        ]);
    }

    private function resolveSponsorFromCode(): ?Customer
    {
        if ($this->hasResolvedSponsorFromCode) {
            return $this->resolvedSponsorFromCode;
        }

        $this->hasResolvedSponsorFromCode = true;

        $this->resolvedSponsorFromCode = app(ReferralContextService::class)
            ->resolveCustomerFromReferralCode($this->string('referral_code')->toString());

        return $this->resolvedSponsorFromCode;
    }

    private function resolveSponsorFromUsername(): ?Customer
    {
        if ($this->hasResolvedSponsorFromUsername) {
            return $this->resolvedSponsorFromUsername;
        }

        $this->hasResolvedSponsorFromUsername = true;

        $this->resolvedSponsorFromUsername = app(ReferralContextService::class)
            ->resolveCustomerFromUsername($this->string('referral_username')->toString());

        return $this->resolvedSponsorFromUsername;
    }
}
