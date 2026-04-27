<?php

use App\Filament\Resources\WhatsAppBroadcasts\Tables\WhatsAppBroadcastTable;
use App\Jobs\ProcessWhatsAppBroadcastJob;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('whatsapp_broadcast_recipients');
    Schema::dropIfExists('whatsapp_broadcasts');

    Schema::create('whatsapp_broadcasts', function (Blueprint $table): void {
        $table->id();
        $table->string('title')->nullable();
        $table->text('message')->nullable();
        $table->string('template_id')->nullable();
        $table->string('status')->default('draft');
        $table->unsignedInteger('total_recipients')->default(0);
        $table->unsignedInteger('success_recipients')->default(0);
        $table->unsignedInteger('failed_recipients')->default(0);
        $table->dateTime('sent_at')->nullable();
        $table->text('last_error')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('whatsapp_broadcast_recipients', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('broadcast_id');
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('customer_name')->nullable();
        $table->string('phone')->nullable();
        $table->string('normalized_phone')->nullable();
        $table->string('status')->default('queued');
        $table->text('response_message')->nullable();
        $table->dateTime('sent_at')->nullable();
        $table->timestamps();
    });
});

it('resets broadcast and recipients when resend is scheduled', function (): void {
    Queue::fake();

    $broadcast = WhatsAppBroadcast::query()->create([
        'title' => 'Promo Weekend',
        'message' => 'Diskon akhir pekan',
        'template_id' => 'tmpl-123',
        'status' => 'sent',
        'total_recipients' => 12,
        'success_recipients' => 10,
        'failed_recipients' => 2,
        'sent_at' => now()->subHour(),
        'last_error' => 'timeout',
        'created_by' => 1,
    ]);

    WhatsAppBroadcastRecipient::query()->create([
        'broadcast_id' => $broadcast->id,
        'customer_id' => 10,
        'customer_name' => 'Ayu',
        'phone' => '081200000001',
        'normalized_phone' => '6281200000001',
        'status' => 'sent',
        'response_message' => 'sent ok',
        'sent_at' => now()->subHour(),
    ]);

    WhatsAppBroadcastRecipient::query()->create([
        'broadcast_id' => $broadcast->id,
        'customer_id' => 11,
        'customer_name' => 'Budi',
        'phone' => '081200000002',
        'normalized_phone' => '6281200000002',
        'status' => 'failed',
        'response_message' => 'gateway error',
        'sent_at' => now()->subHour(),
    ]);

    invokePrivateStatic(WhatsAppBroadcastTable::class, 'scheduleResend', [$broadcast]);

    $broadcast->refresh();
    $recipients = WhatsAppBroadcastRecipient::query()
        ->where('broadcast_id', $broadcast->id)
        ->orderBy('id')
        ->get();

    expect($broadcast->status)->toBe('processing')
        ->and((int) $broadcast->total_recipients)->toBe(0)
        ->and((int) $broadcast->success_recipients)->toBe(0)
        ->and((int) $broadcast->failed_recipients)->toBe(0)
        ->and($broadcast->last_error)->toBeNull()
        ->and($broadcast->sent_at)->toBeNull()
        ->and($recipients)->toHaveCount(2)
        ->and($recipients[0]->status)->toBe('queued')
        ->and($recipients[0]->response_message)->toBeNull()
        ->and($recipients[0]->sent_at)->toBeNull()
        ->and($recipients[1]->status)->toBe('queued')
        ->and($recipients[1]->response_message)->toBeNull()
        ->and($recipients[1]->sent_at)->toBeNull();

    Queue::assertPushed(ProcessWhatsAppBroadcastJob::class, function (ProcessWhatsAppBroadcastJob $job) use ($broadcast): bool {
        return $job->broadcastId === (int) $broadcast->id;
    });
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
