<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class MidtransCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'max:100'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'signature_key' => ['required', 'string', 'max:255'],
            'transaction_status' => ['nullable', 'string', 'max:50'],
            'fraud_status' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->all();
    }
}

