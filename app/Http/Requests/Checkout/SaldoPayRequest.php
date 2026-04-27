<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class SaldoPayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'address_mode' => ['required', 'in:saved,manual,pickup'],
            'order_type' => ['required', 'in:planA,planB'],
            'address_id' => ['required_if:address_mode,saved', 'nullable', 'integer', 'exists:customer_addresses,id'],
            'recipient_name' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:255'],
            'phone' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:30'],
            'address_line' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:500'],
            'province' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'integer', 'min:0'],
            'city_id' => ['nullable', 'integer', 'min:0'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shipping_service_code' => ['nullable', 'string', 'max:50'],
            'shipping_cost' => ['required_if:address_mode,saved,manual', 'nullable', 'numeric', 'min:0'],
            'shipping_etd' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'address_mode.required' => 'Mode alamat wajib dipilih.',
            'address_mode.in' => 'Mode alamat tidak valid.',
            'order_type.required' => 'Tipe plan wajib dipilih.',
            'order_type.in' => 'Tipe plan tidak valid.',
            'address_id.required_if' => 'Pilih salah satu alamat tersimpan.',
            'address_id.exists' => 'Alamat tidak ditemukan.',
            'recipient_name.required_if' => 'Nama penerima wajib diisi.',
            'phone.required_if' => 'Nomor HP wajib diisi.',
            'address_line.required_if' => 'Alamat lengkap wajib diisi.',
            'province.required_if' => 'Provinsi wajib dipilih.',
            'city.required_if' => 'Kota/Kabupaten wajib dipilih.',
            'province_id.integer' => 'ID provinsi harus berupa angka.',
            'city_id.integer' => 'ID kota harus berupa angka.',
            'postal_code.required_if' => 'Kode pos wajib diisi.',
            'shipping_cost.required_if' => 'Biaya ongkir wajib diisi.',
            'shipping_cost.numeric' => 'Biaya ongkir harus berupa angka.',
        ];
    }

    /** @return array<string, mixed> */
    public function addressData(): array
    {
        return $this->safe()->only([
            'address_mode', 'order_type', 'address_id', 'recipient_name', 'phone',
            'address_line', 'province', 'city', 'province_id', 'city_id', 'district', 'postal_code', 'notes',
            'shipping_service_code', 'shipping_cost', 'shipping_etd',
        ]);
    }
}
