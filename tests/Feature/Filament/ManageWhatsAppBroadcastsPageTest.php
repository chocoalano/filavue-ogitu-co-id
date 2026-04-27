<?php

use App\Filament\Resources\WhatsAppBroadcasts\Pages\ManageWhatsAppBroadcasts;
use App\Jobs\SendWhatsAppTestMessageJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Queue;

it('registers test whatsapp header action with required schema fields', function (): void {
    $page = app(ManageWhatsAppBroadcasts::class);
    $actions = invokeProtected($page, 'getHeaderActions');

    $testAction = collect($actions)
        ->first(fn (mixed $action): bool => $action instanceof Action && $action->getName() === 'test_wa');

    expect($testAction)->toBeInstanceOf(Action::class)
        ->and($testAction->getLabel())->toBe('Test Kirim Pesan Whatsapp');

    /** @var Action $testAction */
    $schema = $testAction->getSchema(Schema::make());

    expect($schema)->not->toBeNull();

    $components = $schema?->getComponents() ?? [];

    expect($components)->toHaveCount(4)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[1])->toBeInstanceOf(TextInput::class)
        ->and($components[2])->toBeInstanceOf(TextInput::class)
        ->and($components[3])->toBeInstanceOf(Textarea::class)
        ->and($components[0]->getName())->toBe('to_name')
        ->and($components[1]->getName())->toBe('phone')
        ->and($components[2]->getName())->toBe('template_id')
        ->and($components[3]->getName())->toBe('message');
});

it('dispatches test whatsapp job to whatsapp queue from helper', function (): void {
    Queue::fake();

    $normalizedPhone = invokePrivateStatic(ManageWhatsAppBroadcasts::class, 'dispatchTestWhatsAppMessage', [[
        'to_name' => 'Tester Admin',
        'phone' => '081234567890',
        'template_id' => 'tmpl-001',
        'message' => 'Pesan test queue',
    ]]);

    expect($normalizedPhone)->toBe('6281234567890');

    Queue::assertPushed(SendWhatsAppTestMessageJob::class, function (SendWhatsAppTestMessageJob $job): bool {
        return $job->recipientName === 'Tester Admin'
            && $job->phoneNumber === '6281234567890'
            && $job->templateId === 'tmpl-001'
            && $job->message === 'Pesan test queue'
            && $job->queue === 'whatsapp';
    });
});

it('throws validation exception when helper receives invalid phone', function (): void {
    expect(function (): void {
        invokePrivateStatic(ManageWhatsAppBroadcasts::class, 'dispatchTestWhatsAppMessage', [[
            'to_name' => 'Tester Admin',
            'phone' => '08',
            'template_id' => 'tmpl-001',
            'message' => 'Pesan test queue',
        ]]);
    })->toThrow(InvalidArgumentException::class);
});

function invokeProtected(object $instance, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($instance, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($instance, $arguments);
}

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
