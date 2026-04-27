<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Mockery as M;

/**
 * @param  array<string, mixed>  $overrides
 */
function makeBinaryTreeMember(int $id, ?int $footLeft = null, ?int $footRight = null, array $overrides = []): Customer
{
    $member = new Customer;
    $member->forceFill(array_merge([
        'id' => $id,
        'name' => "Member {$id}",
        'username' => "member{$id}",
        'email' => "member{$id}@example.test",
        'phone' => '081234567890',
        'foot_left' => $footLeft,
        'foot_right' => $footRight,
        'total_left' => 0,
        'total_right' => 0,
        'position' => $id === 1 ? null : 'left',
        'status' => 3,
        'created_at' => now(),
    ], $overrides));
    $member->setRelation('package', null);

    return $member;
}

it('limits default dashboard binary tree payload to five levels', function (): void {
    $root = makeBinaryTreeMember(1, 2, null, [
        'total_left' => 10,
        'total_right' => 0,
        'position' => null,
    ]);
    $secondLevel = makeBinaryTreeMember(2, 3);
    $thirdLevel = makeBinaryTreeMember(3, 4);
    $fourthLevel = makeBinaryTreeMember(4, 5);
    $fifthLevel = makeBinaryTreeMember(5, 6);
    $sixthLevel = makeBinaryTreeMember(6, 7);
    $seventhLevel = makeBinaryTreeMember(7);

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('getBinaryTreeMembers')
        ->once()
        ->with(1, 5)
        ->andReturn(collect([
            $root,
            $secondLevel,
            $thirdLevel,
            $fourthLevel,
            $fifthLevel,
            $sixthLevel,
            $seventhLevel,
        ]));

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $method = new ReflectionMethod(DashboardService::class, 'formatBinaryTreeData');
    $method->setAccessible(true);

    /** @var array{tree:array<string,mixed>|null,stats:array<string,int>} $payload */
    $payload = $method->invoke($service, $root);

    $tree = $payload['tree'];
    $fifthLevelNode = $tree['left']['left']['left']['left'] ?? null;
    $sixthLevelNode = $fifthLevelNode['left'] ?? null;

    expect($tree)->not->toBeNull()
        ->and($tree['level'])->toBe(1)
        ->and($fifthLevelNode)->not->toBeNull()
        ->and($fifthLevelNode['id'])->toBe(5)
        ->and($fifthLevelNode['level'])->toBe(5)
        ->and($sixthLevelNode)->toBeNull()
        ->and($payload['stats']['total_downlines'])->toBe(10);
});

it('loads requested network tree root when member belongs to authenticated customer network', function (): void {
    $root = makeBinaryTreeMember(1, 2, null, [
        'total_left' => 12,
        'total_right' => 0,
        'position' => null,
    ]);
    $selectedRoot = makeBinaryTreeMember(2, 3, null, [
        'position' => 'left',
        'total_left' => 8,
        'total_right' => 0,
    ]);
    $selectedChild = makeBinaryTreeMember(3, null, null, [
        'position' => 'left',
    ]);

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1, 2)
        ->andReturnTrue();
    $dashboardRepository->shouldReceive('findCustomerById')
        ->once()
        ->with(2)
        ->andReturn($selectedRoot);
    $dashboardRepository->shouldReceive('getBinaryTreeMembers')
        ->once()
        ->with(2, 5)
        ->andReturn(collect([
            $selectedRoot,
            $selectedChild,
        ]));
    $dashboardRepository->shouldReceive('hasBinaryChildAtPosition')
        ->once()
        ->with(1, 'left')
        ->andReturnTrue();
    $dashboardRepository->shouldReceive('hasBinaryChildAtPosition')
        ->once()
        ->with(1, 'right')
        ->andReturnFalse();

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $method = new ReflectionMethod(DashboardService::class, 'loadNetworkData');
    $method->setAccessible(true);

    /** @var array{tree:?array<string,mixed>,tree_root_id:int,has_left:bool,has_right:bool} $payload */
    $payload = $method->invoke($service, $root, 2);

    expect($payload['tree_root_id'])->toBe(2)
        ->and($payload['tree'])->not->toBeNull()
        ->and($payload['tree']['id'])->toBe(2)
        ->and($payload['tree']['left']['id'] ?? null)->toBe(3)
        ->and($payload['has_left'])->toBeTrue()
        ->and($payload['has_right'])->toBeFalse();
});

it('falls back to authenticated customer tree when requested member is outside network', function (): void {
    $root = makeBinaryTreeMember(1, 2, null, [
        'total_left' => 12,
        'total_right' => 0,
        'position' => null,
    ]);
    $rootChild = makeBinaryTreeMember(2, null, null, [
        'position' => 'left',
    ]);

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1, 999)
        ->andReturnFalse();
    $dashboardRepository->shouldReceive('findCustomerById')
        ->never();
    $dashboardRepository->shouldReceive('getBinaryTreeMembers')
        ->once()
        ->with(1, 5)
        ->andReturn(collect([
            $root,
            $rootChild,
        ]));
    $dashboardRepository->shouldReceive('hasBinaryChildAtPosition')
        ->once()
        ->with(1, 'left')
        ->andReturnTrue();
    $dashboardRepository->shouldReceive('hasBinaryChildAtPosition')
        ->once()
        ->with(1, 'right')
        ->andReturnFalse();

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $method = new ReflectionMethod(DashboardService::class, 'loadNetworkData');
    $method->setAccessible(true);

    /** @var array{tree:?array<string,mixed>,tree_root_id:int} $payload */
    $payload = $method->invoke($service, $root, 999);

    expect($payload['tree_root_id'])->toBe(1)
        ->and($payload['tree'])->not->toBeNull()
        ->and($payload['tree']['id'])->toBe(1)
        ->and($payload['tree']['left']['id'] ?? null)->toBe(2);
});
