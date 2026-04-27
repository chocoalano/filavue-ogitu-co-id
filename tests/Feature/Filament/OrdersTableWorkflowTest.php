<?php

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Shipment;
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

    Schema::dropIfExists('shipment_items');
    Schema::dropIfExists('shipments');
    Schema::dropIfExists('payments');
    Schema::dropIfExists('payment_methods');
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');

    Schema::create('orders', function (Blueprint $table): void {
        $table->id();
        $table->string('order_no')->nullable();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('currency')->nullable();
        $table->string('status')->nullable();
        $table->string('payment_type')->nullable();
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

    Schema::create('order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('name')->nullable();
        $table->string('sku')->nullable();
        $table->unsignedInteger('qty')->default(1);
        $table->decimal('unit_price', 16, 2)->default(0);
        $table->decimal('discount_amount', 16, 2)->default(0);
        $table->decimal('row_total', 16, 2)->default(0);
        $table->integer('weight_gram')->nullable();
        $table->integer('length_mm')->nullable();
        $table->integer('width_mm')->nullable();
        $table->integer('height_mm')->nullable();
        $table->text('meta_json')->nullable();
        $table->timestamps();
    });

    Schema::create('shipments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->string('courier_id')->nullable();
        $table->string('tracking_no')->nullable();
        $table->string('status')->nullable();
        $table->dateTime('shipped_at')->nullable();
        $table->dateTime('delivered_at')->nullable();
        $table->decimal('shipping_fee', 16, 2)->default(0);
        $table->timestamps();
    });

    Schema::create('payment_methods', function (Blueprint $table): void {
        $table->id();
        $table->string('code')->nullable();
        $table->string('name')->nullable();
        $table->boolean('is_active')->default(true);
    });

    Schema::create('payments', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->unsignedBigInteger('method_id')->nullable();
        $table->string('status')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->string('currency')->nullable();
        $table->string('provider_txn_id')->nullable();
        $table->text('metadata_json')->nullable();
        $table->string('transaction_id')->nullable();
        $table->string('signature_key')->nullable();
        $table->timestamps();
    });

    Schema::create('shipment_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('shipment_id');
        $table->unsignedBigInteger('order_item_id');
        $table->unsignedInteger('qty')->default(1);
        $table->timestamps();
    });
});

it('marks paid orders as shipped and creates shipment items', function (): void {
    $order = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => 'online_payment',
        'shipping_amount' => 15000,
        'paid_at' => now()->subHour(),
    ]);

    createOrderItemForOrder($order, quantity: 2);
    createOrderItemForOrder($order, quantity: 1);

    $shipmentId = invokePrivateStatic(OrdersTable::class, 'markOrderAsShipped', [
        (int) $order->id,
        [
            'courier_id' => 'lion',
            'shipping_fee' => 15000,
            'shipped_at' => now()->subMinutes(30),
        ],
        'LP-ORDER-001',
    ]);

    $freshOrder = Order::query()->findOrFail($order->id);
    $shipment = Shipment::query()->findOrFail($shipmentId);

    expect((string) $freshOrder->status)->toBe('shipped')
        ->and($freshOrder->processed_at)->not->toBeNull()
        ->and((string) $shipment->status)->toBe('shipped')
        ->and((string) $shipment->tracking_no)->toBe('LP-ORDER-001')
        ->and((float) $shipment->shipping_fee)->toBe(15000.0)
        ->and((int) $shipment->items()->count())->toBe(2);
});

it('marks shipped orders as delivered and stores delivered as the final workflow state', function (): void {
    $order = createWorkflowOrder([
        'status' => 'shipped',
        'payment_type' => 'wallet',
        'processed_at' => now()->subHours(3),
    ]);

    $shipment = Shipment::query()->create([
        'order_id' => $order->id,
        'courier_id' => 'lion',
        'tracking_no' => 'LP-ORDER-002',
        'status' => 'shipped',
        'shipped_at' => now()->subHours(2),
        'delivered_at' => null,
        'shipping_fee' => 12000,
    ]);

    $deliveredAt = now()->subHour();

    $updatedShipmentId = invokePrivateStatic(OrdersTable::class, 'markOrderAsDelivered', [
        (int) $order->id,
        [
            'delivered_at' => $deliveredAt,
        ],
    ]);

    $freshOrder = Order::query()->findOrFail($order->id);
    $freshShipment = Shipment::query()->findOrFail($updatedShipmentId);

    expect($updatedShipmentId)->toBe((int) $shipment->id)
        ->and((string) $freshOrder->status)->toBe('delivered')
        ->and((string) $freshShipment->status)->toBe('delivered')
        ->and($freshShipment->delivered_at?->toDateTimeString())->toBe($deliveredAt->toDateTimeString());
});

it('defines paid-first sorting, payment type column, and delivered action in the orders table source', function (): void {
    $reflection = new ReflectionClass(OrdersTable::class);
    $filePath = $reflection->getFileName();
    $source = is_string($filePath) ? file_get_contents($filePath) : false;

    expect($source)->toBeString()
        ->and($source)->toContain('->defaultSort(fn (Builder $query): Builder => self::applyDefaultSort($query))')
        ->and($source)->toContain("SelectColumn::make('status')")
        ->and($source)->toContain('->options(fn (Order $record): array => self::statusSelectOptions($record))')
        ->and($source)->toContain('->disabled(fn (Order $record): bool => ! self::canUseInlineStatusSelect($record))')
        ->and($source)->toContain('->updateStateUsing(fn (Order $record, ?string $state): string => self::updateStatusFromTableSelect($record, $state))')
        ->and($source)->toContain("TextColumn::make('payment_type')")
        ->and($source)->toContain('->state(fn (Order $record): ?string => $record->resolvedPaymentType())')
        ->and($source)->toContain('->sortable(query: fn (Builder $query, string $direction): Builder => self::applyPaymentTypeSort($query, $direction))')
        ->and($source)->toContain("SelectFilter::make('payment_method')")
        ->and($source)->toContain("TextColumn::make('created_at')")
        ->and($source)->toContain("Action::make('tandai_diterima')");
});

it('documents order table usage in the custom list orders view', function (): void {
    $source = file_get_contents(resource_path('views/filament/resources/orders/pages/list-orders.blade.php'));

    expect($source)->toBeString()
        ->and($source)->toContain('Panduan Filter & Audit')
        ->and($source)->toContain('Workflow Status Inline')
        ->and($source)->toContain('Action Penting')
        ->and($source)->toContain('Update:Order')
        ->and($source)->toContain('PAID -&gt; SHIPPED -&gt; DELIVERED');
});

it('eager loads latest payment without ambiguous order_id selection', function (): void {
    $order = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'code' => 'p-001',
        'name' => 'Midtrans',
        'is_active' => true,
    ]);

    Payment::query()->create([
        'order_id' => $order->id,
        'method_id' => $paymentMethod->id,
        'status' => 'pending',
        'amount' => 110000,
        'currency' => 'IDR',
    ]);

    Payment::query()->create([
        'order_id' => $order->id,
        'method_id' => $paymentMethod->id,
        'status' => 'paid',
        'amount' => 110000,
        'currency' => 'IDR',
    ]);

    $records = Order::query()
        ->with([
            'latestPayment' => fn ($query) => $query->select([
                'payments.id',
                'payments.order_id',
                'payments.method_id',
                'payments.status',
            ]),
            'latestPayment.method:id,name,code',
        ])
        ->get();

    expect($records)->toHaveCount(1)
        ->and($records->first()?->resolvedPaymentType())->toBe(Order::PAYMENT_TYPE_ONLINE_PAYMENT)
        ->and($records->first()?->paymentTypeLabel())->toBe('Online Payment')
        ->and((string) $records->first()?->latestPayment?->status)->toBe('paid');
});

it('treats non-wallet payment method codes as online payment', function (): void {
    $order = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
    ]);

    $paymentMethod = PaymentMethod::query()->create([
        'code' => 'bank-transfer',
        'name' => 'Transfer Bank',
        'is_active' => true,
    ]);

    createPaymentForOrder($order, $paymentMethod, 'paid');

    $record = Order::query()
        ->with([
            'latestPayment' => fn ($query) => $query->select([
                'payments.id',
                'payments.order_id',
                'payments.method_id',
                'payments.status',
            ]),
            'latestPayment.method:id,name,code',
        ])
        ->first();

    expect($record?->resolvedPaymentType())->toBe(Order::PAYMENT_TYPE_ONLINE_PAYMENT)
        ->and($record?->paymentTypeLabel())->toBe('Online Payment');
});

it('sorts payment type column using resolved order payment type', function (): void {
    $onlineOrder = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
        'order_no' => 'ORD-ONLINE',
    ]);

    $walletOrder = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
        'order_no' => 'ORD-WALLET',
    ]);

    $onlinePaymentMethod = PaymentMethod::query()->create([
        'code' => 'p-001',
        'name' => 'Midtrans',
        'is_active' => true,
    ]);

    $walletPaymentMethod = PaymentMethod::query()->create([
        'code' => 'p-002',
        'name' => 'Saldo Wallet',
        'is_active' => true,
    ]);

    createPaymentForOrder($walletOrder, $walletPaymentMethod, 'paid');
    createPaymentForOrder($onlineOrder, $onlinePaymentMethod, 'paid');

    $sortedOrderNos = invokePrivateStatic(OrdersTable::class, 'applyPaymentTypeSort', [
        Order::query(),
        'asc',
    ])->pluck('order_no')->all();

    expect($sortedOrderNos)->toBe(['ORD-ONLINE', 'ORD-WALLET']);
});

it('limits inline status select options to the current and next workflow state', function (): void {
    $paidOrder = createWorkflowOrder([
        'status' => 'processing',
        'payment_type' => 'online_payment',
    ]);

    $shippedOrder = createWorkflowOrder([
        'status' => 'shipped',
        'payment_type' => 'wallet',
    ]);

    Shipment::query()->create([
        'order_id' => $shippedOrder->id,
        'courier_id' => 'lion',
        'tracking_no' => 'LP-ORDER-TEST',
        'status' => 'shipped',
        'shipped_at' => now()->subHour(),
        'delivered_at' => null,
        'shipping_fee' => 12000,
    ]);

    $deliveredOrder = createWorkflowOrder([
        'status' => 'delivered',
        'payment_type' => 'wallet',
    ]);

    $paidOptions = invokePrivateStatic(OrdersTable::class, 'statusSelectOptions', [
        $paidOrder,
    ]);

    $shippedOptions = invokePrivateStatic(OrdersTable::class, 'statusSelectOptions', [
        $shippedOrder->fresh(),
    ]);

    $deliveredOptions = invokePrivateStatic(OrdersTable::class, 'statusSelectOptions', [
        $deliveredOrder,
    ]);

    expect($paidOptions)->toBe([
        'processing' => 'PAID',
        'shipped' => 'SHIPPED',
    ])
        ->and($shippedOptions)->toBe([
            'shipped' => 'SHIPPED',
            'delivered' => 'DELIVERED',
        ])
        ->and($deliveredOptions)->toBe([
            'delivered' => 'DELIVERED',
        ]);
});

it('filters orders by payment method relation code', function (): void {
    $midtransOrder = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
        'order_no' => 'ORD-ONLINE-MIDTRANS',
    ]);

    $bankTransferOrder = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => null,
        'order_no' => 'ORD-ONLINE-BANK',
    ]);

    $walletOrder = createWorkflowOrder([
        'status' => 'PAID',
        'payment_type' => 'wallet',
        'order_no' => 'ORD-WALLET',
    ]);

    $onlinePaymentMethod = PaymentMethod::query()->create([
        'code' => 'p-001',
        'name' => 'Midtrans',
        'is_active' => true,
    ]);

    $walletPaymentMethod = PaymentMethod::query()->create([
        'code' => 'p-002',
        'name' => 'Saldo Wallet',
        'is_active' => true,
    ]);

    $bankTransferPaymentMethod = PaymentMethod::query()->create([
        'code' => 'bank-transfer',
        'name' => 'Transfer Bank',
        'is_active' => true,
    ]);

    createPaymentForOrder($midtransOrder, $onlinePaymentMethod, 'paid');
    createPaymentForOrder($bankTransferOrder, $bankTransferPaymentMethod, 'paid');
    createPaymentForOrder($walletOrder, $walletPaymentMethod, 'paid');

    $onlineFilteredOrderNos = invokePrivateStatic(OrdersTable::class, 'applyPaymentMethodFilter', [
        Order::query(),
        'p-001',
    ])->pluck('order_no')->all();

    $bankTransferFilteredOrderNos = invokePrivateStatic(OrdersTable::class, 'applyPaymentMethodFilter', [
        Order::query(),
        'bank-transfer',
    ])->pluck('order_no')->all();

    $walletFilteredOrderNos = invokePrivateStatic(OrdersTable::class, 'applyPaymentMethodFilter', [
        Order::query(),
        'p-002',
    ])->pluck('order_no')->all();

    expect($onlineFilteredOrderNos)->toBe(['ORD-ONLINE-MIDTRANS', 'ORD-ONLINE-BANK'])
        ->and($bankTransferFilteredOrderNos)->toBe(['ORD-ONLINE-MIDTRANS', 'ORD-ONLINE-BANK'])
        ->and($walletFilteredOrderNos)->toBe(['ORD-WALLET']);
});

function createWorkflowOrder(array $overrides = []): Order
{
    static $sequence = 1;

    $order = Order::query()->create(array_merge([
        'order_no' => 'ORD-WORKFLOW-'.$sequence,
        'customer_id' => null,
        'currency' => 'IDR',
        'status' => 'pending',
        'payment_type' => 'online_payment',
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
        'placed_at' => now()->subHours(2),
        'paid_at' => null,
    ], $overrides));

    $sequence++;

    return $order;
}

function createOrderItemForOrder(Order $order, int $quantity = 1): OrderItem
{
    return OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => null,
        'name' => 'Produk Tes',
        'sku' => 'SKU-TES',
        'qty' => $quantity,
        'unit_price' => 50000,
        'discount_amount' => 0,
        'row_total' => 50000 * $quantity,
        'weight_gram' => 200,
        'length_mm' => 100,
        'width_mm' => 100,
        'height_mm' => 100,
        'meta_json' => null,
    ]);
}

function createPaymentForOrder(Order $order, PaymentMethod $paymentMethod, string $status = 'paid'): Payment
{
    return Payment::query()->create([
        'order_id' => $order->id,
        'method_id' => $paymentMethod->id,
        'status' => $status,
        'amount' => (float) $order->grand_total,
        'currency' => (string) $order->currency,
    ]);
}

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
