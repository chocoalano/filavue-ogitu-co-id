<?php

use App\Models\Order;
use App\Support\Orders\OrderTabCountsCache;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    config()->set('cache.default', 'array');
    Cache::flush();

    Schema::dropIfExists('orders');

    Schema::create('orders', function (Blueprint $table): void {
        $table->id();
        $table->string('order_no')->nullable();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('currency')->nullable();
        $table->string('status')->nullable();
        $table->decimal('subtotal_amount', 16, 2)->default(0);
        $table->decimal('discount_amount', 16, 2)->default(0);
        $table->decimal('shipping_amount', 16, 2)->default(0);
        $table->decimal('tax_amount', 16, 2)->default(0);
        $table->decimal('grand_total', 16, 2)->default(0);
        $table->unsignedBigInteger('shipping_address_id')->nullable();
        $table->unsignedBigInteger('billing_address_id')->nullable();
        $table->text('applied_promos')->nullable();
        $table->text('notes')->nullable();
        $table->decimal('bv_amount', 16, 2)->nullable();
        $table->decimal('sponsor_amount', 16, 2)->nullable();
        $table->decimal('match_amount', 16, 2)->nullable();
        $table->decimal('pairing_amount', 16, 2)->nullable();
        $table->decimal('retail_amount', 16, 2)->default(0);
        $table->decimal('cashback_amount', 16, 2)->nullable();
        $table->decimal('stockist_amount', 16, 2)->default(0);
        $table->string('type')->nullable();
        $table->boolean('bonus_generated')->default(false);
        $table->dateTime('processed_at')->nullable();
        $table->dateTime('placed_at')->nullable();
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
    });
});

it('caches aggregated order tab counts after the first query', function (): void {
    createOrder(['status' => 'pending']);
    createOrder(['status' => 'PAID']);
    createOrder(['status' => 'canceled']);

    Cache::flush();

    DB::connection()->enableQueryLog();
    DB::flushQueryLog();

    $firstCounts = OrderTabCountsCache::counts();
    $firstQueryCount = count(DB::getQueryLog());

    DB::flushQueryLog();

    $secondCounts = OrderTabCountsCache::counts();
    $secondQueryCount = count(DB::getQueryLog());

    expect($firstCounts)->toBe([
        'all' => 3,
        'pending' => 1,
        'paid' => 1,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 1,
    ])->and($secondCounts)->toBe($firstCounts)
        ->and($firstQueryCount)->toBe(1)
        ->and($secondQueryCount)->toBe(0);
});

it('refreshes cached counts when orders are created, their status changes, and they are deleted', function (): void {
    $pendingOrder = createOrder(['status' => 'pending']);
    createOrder(['status' => 'paid']);

    expect(OrderTabCountsCache::counts())->toBe([
        'all' => 2,
        'pending' => 1,
        'paid' => 1,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ]);

    $shippedOrder = createOrder(['status' => 'shipped']);

    expect(OrderTabCountsCache::counts())->toBe([
        'all' => 3,
        'pending' => 1,
        'paid' => 1,
        'shipped' => 1,
        'delivered' => 0,
        'cancelled' => 0,
    ]);

    $pendingOrder->update([
        'status' => 'delivered',
    ]);

    expect(OrderTabCountsCache::counts())->toBe([
        'all' => 3,
        'pending' => 0,
        'paid' => 1,
        'shipped' => 1,
        'delivered' => 1,
        'cancelled' => 0,
    ]);

    $shippedOrder->delete();

    expect(OrderTabCountsCache::counts())->toBe([
        'all' => 2,
        'pending' => 0,
        'paid' => 1,
        'shipped' => 0,
        'delivered' => 1,
        'cancelled' => 0,
    ]);
});

it('does not rebuild cached counts for updates outside tab status', function (): void {
    $order = createOrder([
        'status' => 'pending',
        'notes' => 'before',
    ]);

    OrderTabCountsCache::counts();

    DB::connection()->enableQueryLog();
    DB::flushQueryLog();

    $order->update([
        'notes' => 'after',
    ]);

    $counts = OrderTabCountsCache::counts();
    $queryCountAfterUnrelatedUpdate = count(DB::getQueryLog());

    expect($counts)->toBe([
        'all' => 1,
        'pending' => 1,
        'paid' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ])->and($queryCountAfterUnrelatedUpdate)->toBe(1);
});

it('counts processing orders inside the paid tab bucket for admin workflow', function (): void {
    createOrder(['status' => 'processing']);
    createOrder(['status' => 'PAID']);

    expect(OrderTabCountsCache::counts())->toBe([
        'all' => 2,
        'pending' => 0,
        'paid' => 2,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ])->and(OrderTabCountsCache::statusesForTab('paid'))->toBe(['paid', 'processing']);
});

function createOrder(array $overrides = []): Order
{
    static $sequence = 1;

    $order = Order::query()->create(array_merge([
        'order_no' => 'ORD-TEST-'.$sequence,
        'customer_id' => null,
        'currency' => 'IDR',
        'status' => 'pending',
        'subtotal_amount' => 100000,
        'discount_amount' => 0,
        'shipping_amount' => 10000,
        'tax_amount' => 0,
        'grand_total' => 110000,
        'shipping_address_id' => null,
        'billing_address_id' => null,
        'applied_promos' => null,
        'notes' => null,
        'bv_amount' => null,
        'sponsor_amount' => null,
        'match_amount' => null,
        'pairing_amount' => null,
        'retail_amount' => 0,
        'cashback_amount' => null,
        'stockist_amount' => 0,
        'type' => 'planA',
        'bonus_generated' => false,
        'processed_at' => null,
        'placed_at' => now(),
        'paid_at' => null,
    ], $overrides));

    $sequence++;

    return $order;
}
