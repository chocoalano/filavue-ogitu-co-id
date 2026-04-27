<?php

use App\Models\Customer;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    config()->set('session.driver', 'array');
});

it('allows every ability when user role is developer', function (): void {
    $user = new class extends AuthenticatableUser
    {
        public string $role = 'developer';
    };

    expect(Gate::forUser($user)->allows('any-ability'))->toBeTrue();
    expect(Gate::forUser($user)->allows('viewAny', Customer::class))->toBeTrue();
});

it('does not auto allow when user role is not developer', function (): void {
    $user = new class extends AuthenticatableUser
    {
        public string $role = 'admin';
    };

    expect(Gate::forUser($user)->allows('any-ability'))->toBeFalse();
});
