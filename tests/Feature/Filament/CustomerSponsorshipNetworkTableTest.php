<?php

use App\Filament\Pages\CustomerSponsorshipNetworks;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Schema::dropIfExists('customers');
    Schema::dropIfExists('customer_package');
    Schema::dropIfExists('customer_network_matrixes');

    Schema::create('customer_package', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
    });

    Schema::create('customers', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('package_id')->nullable();
        $table->string('username')->nullable();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->decimal('omzet_group', 16, 2)->default(0);
        $table->string('level')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_network_matrixes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('member_id')->nullable();
        $table->unsignedBigInteger('sponsor_id')->nullable();
        $table->unsignedInteger('level')->nullable();
        $table->string('description')->nullable();
        $table->timestamps();
    });
});

it('builds generation filter options from available sponsorship levels', function (): void {
    DB::table('customer_network_matrixes')->insert([
        [
            'member_id' => 11,
            'sponsor_id' => 1,
            'level' => 3,
            'description' => 'Level 3',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'member_id' => 12,
            'sponsor_id' => 1,
            'level' => 1,
            'description' => 'Level 1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'member_id' => 13,
            'sponsor_id' => 1,
            'level' => 2,
            'description' => 'Level 2',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'member_id' => 14,
            'sponsor_id' => 2,
            'level' => 2,
            'description' => 'Level 2 duplicate',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = invokeProtectedStaticMethod(CustomerSponsorshipNetworks::class, 'generationOptions');

    expect($options)->toBe([
        1 => 'Generasi 1',
        2 => 'Generasi 2',
        3 => 'Generasi 3',
    ]);
});

it('builds package and rank filter options from related customer data', function (): void {
    DB::table('customer_package')->insert([
        ['id' => 2, 'name' => 'Platinum'],
        ['id' => 1, 'name' => 'Silver'],
    ]);

    DB::table('customers')->insert([
        [
            'id' => 21,
            'package_id' => 1,
            'username' => 'silver-a',
            'name' => 'Silver A',
            'email' => 'silver-a@example.test',
            'password' => 'hashed',
            'omzet_group' => 100000,
            'level' => 'Associate',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 22,
            'package_id' => 2,
            'username' => 'platinum-a',
            'name' => 'Platinum A',
            'email' => 'platinum-a@example.test',
            'password' => 'hashed',
            'omzet_group' => 250000,
            'level' => 'Director',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 23,
            'package_id' => 2,
            'username' => 'platinum-b',
            'name' => 'Platinum B',
            'email' => 'platinum-b@example.test',
            'password' => 'hashed',
            'omzet_group' => 175000,
            'level' => 'Associate',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $packageOptions = invokeProtectedStaticMethod(CustomerSponsorshipNetworks::class, 'packageOptions');
    $rankOptions = invokeProtectedStaticMethod(CustomerSponsorshipNetworks::class, 'rankOptions');

    expect($packageOptions)->toBe([
        2 => 'Platinum',
        1 => 'Silver',
    ])->and($rankOptions)->toBe([
        'Associate' => 'Associate',
        'Director' => 'Director',
    ]);
});

it('defines the affiliate custom page with the requested sponsorship table columns and generation filter', function (): void {
    $pageSource = file_get_contents(app_path('Filament/Pages/CustomerSponsorshipNetworks.php'));
    $viewSource = file_get_contents(resource_path('views/filament/pages/customer-sponsorship-networks.blade.php'));

    expect($pageSource)->toBeString()
        ->and($pageSource)->toContain("protected static ?string \$title = 'Melihat Jaringan Sponsorship';")
        ->and($pageSource)->toContain("protected static ?string \$navigationLabel = 'Melihat Jaringan Sponsorship';")
        ->and($pageSource)->toContain("protected static ?string \$slug = 'affiliate/jaringan-sponsorship';")
        ->and($pageSource)->toContain("protected static string|UnitEnum|null \$navigationGroup = 'Affiliate';")
        ->and($pageSource)->toContain("TextColumn::make('member.name')")
        ->and($pageSource)->toContain("->label('Nama')")
        ->and($pageSource)->toContain("TextColumn::make('member.username')")
        ->and($pageSource)->toContain("->label('Username')")
        ->and($pageSource)->toContain("TextColumn::make('member.package.name')")
        ->and($pageSource)->toContain("->label('Paket')")
        ->and($pageSource)->toContain("TextColumn::make('member.level')")
        ->and($pageSource)->toContain("->label('Peringkat')")
        ->and($pageSource)->toContain("TextColumn::make('member.omzet_group')")
        ->and($pageSource)->toContain("->label('Omset Group')")
        ->and($pageSource)->toContain("SelectFilter::make('generation')")
        ->and($pageSource)->toContain("->label('Generasi')")
        ->and($pageSource)->toContain("SelectFilter::make('package_id')")
        ->and($pageSource)->toContain("->label('Paket')")
        ->and($pageSource)->toContain("SelectFilter::make('member_level')")
        ->and($pageSource)->toContain("->label('Peringkat')")
        ->and($pageSource)->toContain("->where('package_id', (int) \$packageId)")
        ->and($pageSource)->toContain("->where('level', \$rank)")
        ->and($viewSource)->toBeString()
        ->and($viewSource)->toContain('{{ $this->table }}');
});

it('uses customer network matrix permission to protect access to the custom page', function (): void {
    $pageSource = file_get_contents(app_path('Filament/Pages/CustomerSponsorshipNetworks.php'));

    expect($pageSource)->toBeString()
        ->and($pageSource)->toContain('public static function canAccess(): bool')
        ->and($pageSource)->toContain('ViewAny:CustomerNetworkMatrix');
});

function invokeProtectedStaticMethod(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
