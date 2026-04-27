<?php

use Illuminate\Foundation\Auth\User as AuthenticatableUser;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    config()->set('session.driver', 'array');
});

it('requires web authentication for swagger documentation page', function (): void {
    $response = $this->get('/api/documentation');

    $response->assertStatus(302);
    expect((string) $response->headers->get('Location'))->toContain('login');
});

it('forbids non developer user from swagger documentation page', function (): void {
    $user = new class extends AuthenticatableUser
    {
        public int $id = 10;

        public string $role = 'admin';
    };

    $this->actingAs($user, 'web')
        ->get('/api/documentation')
        ->assertForbidden();
});

it('allows developer user to open swagger documentation page', function (): void {
    $user = new class extends AuthenticatableUser
    {
        public int $id = 11;

        public string $role = 'developer';
    };

    $this->actingAs($user, 'web')
        ->get('/api/documentation')
        ->assertOk();
});
