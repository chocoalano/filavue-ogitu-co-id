<?php

namespace App\Http\Requests\CustomerAddress;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class SaveCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'address_line1' => ['required', 'string', 'max:500'],
            'address_line2' => ['nullable', 'string', 'max:500'],
            'province_label' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'integer', 'min:1'],
            'city_label' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'min:1'],
            'district' => ['required', 'string', 'max:255'],
            'district_lion' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_phone.required' => 'Nomor HP penerima wajib diisi.',
            'address_line1.required' => 'Alamat utama wajib diisi.',
            'province_label.required' => 'Provinsi wajib dipilih.',
            'province_id.required' => 'ID provinsi wajib diisi.',
            'province_id.integer' => 'ID provinsi harus berupa angka.',
            'province_id.min' => 'ID provinsi tidak valid.',
            'city_label.required' => 'Kota/Kab wajib dipilih.',
            'city_id.required' => 'ID kota/kabupaten wajib diisi.',
            'city_id.integer' => 'ID kota/kabupaten harus berupa angka.',
            'city_id.min' => 'ID kota/kabupaten tidak valid.',
            'district.required' => 'Kecamatan wajib dipilih.',
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            'label' => $this->filled('label') ? (string) $this->string('label') : null,
            'is_default' => $this->boolean('is_default'),
            'recipient_name' => (string) $this->string('recipient_name'),
            'recipient_phone' => (string) $this->string('recipient_phone'),
            'address_line1' => (string) $this->string('address_line1'),
            'address_line2' => $this->filled('address_line2') ? (string) $this->string('address_line2') : null,
            'province_label' => $this->filled('province_label') ? (string) $this->string('province_label') : null,
            'province_id' => (int) $this->integer('province_id'),
            'city_label' => $this->filled('city_label') ? (string) $this->string('city_label') : null,
            'city_id' => (int) $this->integer('city_id'),
            'district' => (string) $this->string('district'),
            'district_lion' => $this->filled('district_lion') ? (string) $this->string('district_lion') : null,
            'postal_code' => $this->filled('postal_code') ? (string) $this->string('postal_code') : null,
            'country' => $this->filled('country') ? (string) $this->string('country') : 'Indonesia',
            'description' => $this->filled('description') ? (string) $this->string('description') : null,
        ];
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
