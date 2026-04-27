<?php

use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticBonusPlanATableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticBonusPlanBTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticOmzetSummaryTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticOrderSummaryTableWidget;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticStatsOverview;
use App\Filament\Resources\ReportAnalytics\Widgets\ReportAnalyticWorkbookData;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customer_bonus_lifetime_cash_rewards');
    Schema::dropIfExists('customer_bonus_rewards');
    Schema::dropIfExists('customer_bonus_cashbacks');
    Schema::dropIfExists('customer_bonus_retails');
    Schema::dropIfExists('customer_bonus_matchings');
    Schema::dropIfExists('customer_bonus_pairings');
    Schema::dropIfExists('customer_bonus_sponsors');
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');

    Schema::create('orders', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('status')->nullable();
        $table->decimal('grand_total', 16, 2)->default(0);
        $table->string('type')->nullable();
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
    });

    Schema::create('order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->string('sku')->nullable();
        $table->string('name')->nullable();
        $table->unsignedInteger('qty')->default(0);
        $table->decimal('row_total', 16, 2)->default(0);
        $table->timestamps();
    });

    foreach ([
        'customer_bonus_sponsors',
        'customer_bonus_pairings',
        'customer_bonus_matchings',
        'customer_bonus_retails',
        'customer_bonus_cashbacks',
        'customer_bonus_lifetime_cash_rewards',
    ] as $tableName) {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->decimal('amount', 16, 2)->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    Schema::create('customer_bonus_rewards', function (Blueprint $table): void {
        $table->id();
        $table->string('reward_type')->nullable();
        $table->decimal('amount', 16, 2)->default(0);
        $table->unsignedTinyInteger('status')->default(0);
        $table->timestamps();
    });
});

it('builds analytic workbook data with the expected order, omzet, and bonus criteria', function (): void {
    DB::table('orders')->insert([
        [
            'id' => 1,
            'customer_id' => 10,
            'status' => 'processing',
            'grand_total' => 1000000,
            'type' => 'planA',
            'paid_at' => null,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ],
        [
            'id' => 2,
            'customer_id' => 10,
            'status' => 'delivered',
            'grand_total' => 500000,
            'type' => 'planA',
            'paid_at' => now()->subDays(3),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'id' => 3,
            'customer_id' => 20,
            'status' => 'paid',
            'grand_total' => 750000,
            'type' => 'planA',
            'paid_at' => now()->subDays(2),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'id' => 4,
            'customer_id' => 30,
            'status' => 'delivered',
            'grand_total' => 300000,
            'type' => 'planB',
            'paid_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'id' => 5,
            'customer_id' => 40,
            'status' => 'cancelled',
            'grand_total' => 450000,
            'type' => 'planB',
            'paid_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'id' => 6,
            'customer_id' => 50,
            'status' => 'pending',
            'grand_total' => 250000,
            'type' => 'planA',
            'paid_at' => now()->subHours(12),
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ],
    ]);

    DB::table('order_items')->insert([
        ['order_id' => 1, 'sku' => 'SKU-A', 'name' => 'Alpha Package', 'qty' => 2, 'row_total' => 200000, 'created_at' => now(), 'updated_at' => now()],
        ['order_id' => 2, 'sku' => 'SKU-A', 'name' => 'Alpha Package', 'qty' => 1, 'row_total' => 100000, 'created_at' => now(), 'updated_at' => now()],
        ['order_id' => 3, 'sku' => 'SKU-B', 'name' => 'Bioverse', 'qty' => 3, 'row_total' => 300000, 'created_at' => now(), 'updated_at' => now()],
        ['order_id' => 4, 'sku' => 'SKU-C', 'name' => 'Retail Pack', 'qty' => 1, 'row_total' => 300000, 'created_at' => now(), 'updated_at' => now()],
        ['order_id' => 5, 'sku' => 'SKU-X', 'name' => 'Cancelled Pack', 'qty' => 9, 'row_total' => 999999, 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('customer_bonus_sponsors')->insert([
        ['amount' => 100000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['amount' => 999999, 'status' => 0, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('customer_bonus_pairings')->insert([['amount' => 50000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]]);
    DB::table('customer_bonus_matchings')->insert([['amount' => 25000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]]);
    DB::table('customer_bonus_rewards')->insert([
        ['reward_type' => 'promotion', 'amount' => 25000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['reward_type' => 'lifetime', 'amount' => 777777, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('customer_bonus_retails')->insert([['amount' => 40000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]]);
    DB::table('customer_bonus_cashbacks')->insert([['amount' => 10000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]]);
    DB::table('customer_bonus_lifetime_cash_rewards')->insert([['amount' => 5000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]]);

    $summary = ReportAnalyticWorkbookData::build();

    expect($summary['order_summary']['total_qty'])->toBe(7)
        ->and($summary['order_summary']['total_amount'])->toBe(900000.0)
        ->and($summary['order_summary']['rows'][0]['sku'])->toBe('SKU-A')
        ->and($summary['omzet_summary']['register_member'])->toBe(2000000.0)
        ->and($summary['omzet_summary']['upgrade_member'])->toBe(500000.0)
        ->and($summary['omzet_summary']['retail_order'])->toBe(300000.0)
        ->and($summary['omzet_summary']['total'])->toBe(2800000.0)
        ->and($summary['bonus_summary']['planA']['bonus_total'])->toBe(200000.0)
        ->and($summary['bonus_summary']['planA']['payout_total'])->toBe(8.0)
        ->and($summary['bonus_summary']['planB']['bonus_total'])->toBe(55000.0)
        ->and($summary['bonus_summary']['planB']['payout_total'])->toBe(18.33);
});

it('uses default filament table widgets for report analytic summaries', function (): void {
    $pageSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Pages/ManageReportAnalytics.php'));
    $dataSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticWorkbookData.php'));
    $statsWidgetSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticStatsOverview.php'));
    $orderWidgetSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticOrderSummaryTableWidget.php'));
    $omzetWidgetSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticOmzetSummaryTableWidget.php'));
    $bonusPlanAWidgetSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticBonusPlanATableWidget.php'));
    $bonusPlanBWidgetSource = file_get_contents(app_path('Filament/Resources/ReportAnalytics/Widgets/ReportAnalyticBonusPlanBTableWidget.php'));

    expect($pageSource)->toBeString()
        ->and($pageSource)->toContain('ReportAnalyticStatsOverview::class')
        ->and($pageSource)->toContain('ReportAnalyticOrderSummaryTableWidget::class')
        ->and($pageSource)->toContain('ReportAnalyticOmzetSummaryTableWidget::class')
        ->and($pageSource)->toContain('ReportAnalyticBonusPlanATableWidget::class')
        ->and($pageSource)->toContain('ReportAnalyticBonusPlanBTableWidget::class')
        ->and($pageSource)->toContain("'default' => 1")
        ->and($pageSource)->toContain("'md' => 2")
        ->and($pageSource)->not->toContain('ReportAnalyticSalesSummaryWidget::class')
        ->and($pageSource)->not->toContain('ReportAnalyticBonusSummaryWidget::class')
        ->and($dataSource)->toContain('return once(function (): array {')
        ->and($dataSource)->toContain('buildOrderSummary')
        ->and($dataSource)->toContain('buildOmzetSummary')
        ->and($dataSource)->toContain('buildBonusSummary')
        ->and($statsWidgetSource)->toContain('Insight Laporan Analitik')
        ->and($statsWidgetSource)->toContain("color('primary')")
        ->and($orderWidgetSource)->toContain('extends ReportAnalyticTableWidget')
        ->and($orderWidgetSource)->toContain('item terjual dari order valid')
        ->and($orderWidgetSource)->toContain('Summary Order All')
        ->and($omzetWidgetSource)->toContain('Plan A')
        ->and($omzetWidgetSource)->toContain('Summary Omzet Company')
        ->and($bonusPlanAWidgetSource)->toContain('Summary Bonus Plan A')
        ->and($bonusPlanAWidgetSource)->toContain('Network Builder Plan')
        ->and($bonusPlanBWidgetSource)->toContain('Summary Bonus Plan B')
        ->and($bonusPlanBWidgetSource)->toContain('Retail Plan')
        ->and(file_exists(resource_path('views/filament/resources/report-analytics/widgets/report-analytic-sales-summary.blade.php')))->toBeFalse()
        ->and(file_exists(resource_path('views/filament/resources/report-analytics/widgets/report-analytic-bonus-summary.blade.php')))->toBeFalse()
        ->and(is_subclass_of(ReportAnalyticStatsOverview::class, StatsOverviewWidget::class))->toBeTrue()
        ->and(is_subclass_of(ReportAnalyticOrderSummaryTableWidget::class, TableWidget::class))->toBeTrue()
        ->and(is_subclass_of(ReportAnalyticOmzetSummaryTableWidget::class, TableWidget::class))->toBeTrue()
        ->and(is_subclass_of(ReportAnalyticBonusPlanATableWidget::class, TableWidget::class))->toBeTrue()
        ->and(is_subclass_of(ReportAnalyticBonusPlanBTableWidget::class, TableWidget::class))->toBeTrue();
});
