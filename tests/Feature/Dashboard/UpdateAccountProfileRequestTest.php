<?php

use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use Illuminate\Support\Facades\Validator;

it('normalizes identity, phone, and bank account fields to numeric-only values', function (): void {
    $normalized = UpdateAccountProfileRequest::normalizeForValidation([
        'username' => 'Member.Satu',
        'name' => ' Member Satu ',
        'nik' => '3276-0101-0101-0001',
        'gender' => 'laki-laki',
        'email' => ' MEMBER@EXAMPLE.TEST ',
        'phone' => '+62 812-3456-789',
        'bank_name' => 'BCA',
        'bank_account' => '123-456-789',
    ]);

    expect($normalized['username'])->toBe('member.satu')
        ->and($normalized['name'])->toBe('Member Satu')
        ->and($normalized['nik'])->toBe('3276010101010001')
        ->and($normalized['gender'])->toBe('L')
        ->and($normalized['email'])->toBe('member@example.test')
        ->and($normalized['phone'])->toBe('628123456789')
        ->and($normalized['bank_account'])->toBe('123456789');
});

it('accepts normalized numeric phone and bank account values for account profile validation', function (): void {
    $payload = UpdateAccountProfileRequest::normalizeForValidation([
        'username' => 'member_satu',
        'name' => 'Member Satu',
        'nik' => '3276010101010001',
        'gender' => 'P',
        'email' => 'member@example.test',
        'phone' => '+62 811 2233 4455',
        'bank_name' => 'Mandiri',
        'bank_account' => '987-654-321',
    ]);

    $validator = Validator::make(
        $payload,
        UpdateAccountProfileRequest::profileRules(null, null, false),
        (new UpdateAccountProfileRequest)->messages()
    );

    expect($validator->passes())->toBeTrue()
        ->and($payload['phone'])->toBe('6281122334455')
        ->and($payload['bank_account'])->toBe('987654321');
});
