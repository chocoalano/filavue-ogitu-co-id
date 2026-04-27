<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->resolveAuthenticatedCustomer() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'upline_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],
            'position' => ['required', 'string', Rule::in(['left', 'right'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.integer' => 'Member tidak valid.',
            'member_id.exists' => 'Member tidak ditemukan.',
            'upline_id.required' => 'Upline wajib diisi.',
            'upline_id.integer' => 'Upline tidak valid.',
            'upline_id.exists' => 'Upline tidak ditemukan.',
            'position.required' => 'Posisi wajib dipilih.',
            'position.in' => 'Posisi harus kiri atau kanan.',
        ];
    }

    /**
     * @return array{member_id:int,upline_id:int,position:'left'|'right'}
     */
    public function payload(): array
    {
        /** @var 'left'|'right' $position */
        $position = (string) $this->string('position');

        return [
            'member_id' => (int) $this->integer('member_id'),
            'upline_id' => (int) $this->integer('upline_id'),
            'position' => $position,
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
