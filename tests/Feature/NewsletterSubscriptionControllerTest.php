<?php

use App\Services\Newsletter\NewsletterSubscriptionService;
use Mockery\MockInterface;

beforeEach(function (): void {
    config()->set('session.driver', 'array');
    config()->set('cache.default', 'array');
    $this->withoutMiddleware();
});

it('subscribes new email and returns success flash message', function (): void {
    $this->mock(NewsletterSubscriptionService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('subscribe')
            ->once()
            ->with('member@example.com', '127.0.0.1')
            ->andReturn(true);
    });

    $this->from('/')
        ->post(route('newsletter.subscribe'), [
            'email' => '  Member@Example.com ',
        ])
        ->assertRedirect('/')
        ->assertSessionHas('success', 'Berhasil berlangganan promo terbaru.');
});

it('returns existing subscriber success message when email is already registered', function (): void {
    $this->mock(NewsletterSubscriptionService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('subscribe')
            ->once()
            ->with('member@example.com', '127.0.0.1')
            ->andReturn(false);
    });

    $this->from('/')
        ->post(route('newsletter.subscribe'), [
            'email' => 'member@example.com',
        ])
        ->assertRedirect('/')
        ->assertSessionHas('success', 'Email sudah terdaftar. Promo terbaru tetap akan kami kirimkan.');
});

it('fails validation when email format is invalid', function (): void {
    $this->mock(NewsletterSubscriptionService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('subscribe');
    });

    $this->from('/')
        ->post(route('newsletter.subscribe'), [
            'email' => 'bukan-email-valid',
        ])
        ->assertRedirect('/')
        ->assertSessionHasErrors(['email']);
});
