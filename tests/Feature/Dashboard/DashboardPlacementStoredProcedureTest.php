<?php

use App\Models\Customer;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Services\Dashboard\DashboardService;
use App\Services\Payment\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery as M;

/**
 * @param  array<string, mixed>  $overrides
 */
function makePlacementCustomer(int $id, array $overrides = []): Customer
{
    $customer = new Customer;
    $customer->forceFill(array_merge([
        'id' => $id,
        'name' => "Member {$id}",
        'username' => "member{$id}",
        'email' => "member{$id}@example.test",
        'status' => 3,
        'sponsor_id' => null,
        'upline_id' => null,
        'position' => null,
        'foot_left' => null,
        'foot_right' => null,
    ], $overrides));
    $customer->exists = true;

    return $customer;
}

it('runs registration procedure after placement and returns placement payload on success', function (): void {
    $authenticatedCustomer = makePlacementCustomer(1001);
    $upline = makePlacementCustomer(1001, [
        'name' => 'Upline Utama',
    ]);
    $member = makePlacementCustomer(2001, [
        'name' => 'Member Baru',
        'status' => 2,
        'sponsor_id' => 1001,
        'upline_id' => null,
        'position' => null,
    ]);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(static fn (callable $callback): mixed => $callback());

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1001, 1001)
        ->andReturnFalse();
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(1001)
        ->andReturn($upline);
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(2001)
        ->andReturn($member);
    $dashboardRepository->shouldReceive('updateMemberPlacement')
        ->once()
        ->with($member, 1001, 'left');
    $dashboardRepository->shouldReceive('updateUplineFoot')
        ->once()
        ->with($upline, 'left', 2001);
    $dashboardRepository->shouldReceive('callRegistrationProcedure')
        ->once()
        ->with(2001)
        ->andReturn((object) [
            'success' => 1,
            'code' => 200,
            'message' => 'Register berhasil.',
        ]);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    $result = $service->placeMember($authenticatedCustomer, [
        'member_id' => 2001,
        'upline_id' => 1001,
        'position' => 'left',
    ]);

    expect($result)->toBe([
        'name' => 'Member Baru',
        'position' => 'left',
    ]);
});

it('fails placement when registration procedure does not return output', function (): void {
    Log::spy();

    $authenticatedCustomer = makePlacementCustomer(1002);
    $upline = makePlacementCustomer(1002);
    $member = makePlacementCustomer(2002, [
        'status' => 2,
        'sponsor_id' => 1002,
    ]);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(static fn (callable $callback): mixed => $callback());

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1002, 1002)
        ->andReturnFalse();
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(1002)
        ->andReturn($upline);
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(2002)
        ->andReturn($member);
    $dashboardRepository->shouldReceive('updateMemberPlacement')
        ->once()
        ->with($member, 1002, 'right');
    $dashboardRepository->shouldReceive('updateUplineFoot')
        ->once()
        ->with($upline, 'right', 2002);
    $dashboardRepository->shouldReceive('callRegistrationProcedure')
        ->once()
        ->with(2002)
        ->andReturn(null);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    try {
        $service->placeMember($authenticatedCustomer, [
            'member_id' => 2002,
            'upline_id' => 1002,
            'position' => 'right',
        ]);
    } catch (ValidationException $exception) {
        expect($exception->errors()['error'][0] ?? null)->toBe('Stored procedure tidak mengembalikan output.');
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(static function (string $message, array $context): bool {
                return $message === 'Placement failed with validation error.'
                    && ($context['authenticated_customer_id'] ?? null) === 1002
                    && ($context['member_id'] ?? null) === 2002
                    && ($context['upline_id'] ?? null) === 1002
                    && ($context['position'] ?? null) === 'right'
                    && is_array($context['errors'] ?? null);
            });

        return;
    }

    $this->fail('Expected ValidationException was not thrown.');
});

it('fails placement when registration procedure returns unsuccessful status code', function (): void {
    Log::spy();

    $authenticatedCustomer = makePlacementCustomer(1003);
    $upline = makePlacementCustomer(1003);
    $member = makePlacementCustomer(2003, [
        'status' => 2,
        'sponsor_id' => 1003,
    ]);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(static fn (callable $callback): mixed => $callback());

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1003, 1003)
        ->andReturnFalse();
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(1003)
        ->andReturn($upline);
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(2003)
        ->andReturn($member);
    $dashboardRepository->shouldReceive('updateMemberPlacement')
        ->once()
        ->with($member, 1003, 'left');
    $dashboardRepository->shouldReceive('updateUplineFoot')
        ->once()
        ->with($upline, 'left', 2003);
    $dashboardRepository->shouldReceive('callRegistrationProcedure')
        ->once()
        ->with(2003)
        ->andReturn((object) [
            'success' => 1,
            'code' => 500,
            'message' => 'Integrity error.',
        ]);

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    try {
        $service->placeMember($authenticatedCustomer, [
            'member_id' => 2003,
            'upline_id' => 1003,
            'position' => 'left',
        ]);
    } catch (ValidationException $exception) {
        expect($exception->errors()['error'][0] ?? null)->toBe('500 - Integrity error.');
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(static function (string $message, array $context): bool {
                return $message === 'Placement failed with validation error.'
                    && ($context['authenticated_customer_id'] ?? null) === 1003
                    && ($context['member_id'] ?? null) === 2003
                    && ($context['upline_id'] ?? null) === 1003
                    && ($context['position'] ?? null) === 'left'
                    && is_array($context['errors'] ?? null);
            });

        return;
    }

    $this->fail('Expected ValidationException was not thrown.');
});

it('logs unexpected placement errors and returns generic validation error', function (): void {
    Log::spy();

    $authenticatedCustomer = makePlacementCustomer(1004);
    $upline = makePlacementCustomer(1004);
    $member = makePlacementCustomer(2004, [
        'status' => 2,
        'sponsor_id' => 1004,
    ]);

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(static fn (callable $callback): mixed => $callback());

    $dashboardRepository = M::mock(DashboardRepositoryInterface::class);
    $dashboardRepository->shouldReceive('isMemberInCustomerNetwork')
        ->once()
        ->with(1004, 1004)
        ->andReturnFalse();
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(1004)
        ->andReturn($upline);
    $dashboardRepository->shouldReceive('findCustomerByIdForUpdate')
        ->once()
        ->with(2004)
        ->andReturn($member);
    $dashboardRepository->shouldReceive('updateMemberPlacement')
        ->once()
        ->with($member, 1004, 'left')
        ->andThrow(new RuntimeException('DB timeout while placing member'));

    $service = new DashboardService(
        $dashboardRepository,
        M::mock(CustomerAddressRepositoryInterface::class),
        M::mock(MidtransService::class),
    );

    try {
        $service->placeMember($authenticatedCustomer, [
            'member_id' => 2004,
            'upline_id' => 1004,
            'position' => 'left',
        ]);
    } catch (ValidationException $exception) {
        expect($exception->errors()['error'][0] ?? null)->toBe('Gagal memproses placement member. Silakan coba lagi.');
        Log::shouldHaveReceived('error')
            ->atLeast()
            ->times(1)
            ->withArgs(static function (string $message, array $context): bool {
                return $message === 'Placement failed with unexpected error.'
                    && ($context['authenticated_customer_id'] ?? null) === 1004
                    && ($context['member_id'] ?? null) === 2004
                    && ($context['upline_id'] ?? null) === 1004
                    && ($context['position'] ?? null) === 'left'
                    && ($context['exception'] ?? null) === RuntimeException::class
                    && ($context['message'] ?? null) === 'DB timeout while placing member';
            });

        return;
    }

    $this->fail('Expected ValidationException was not thrown.');
});
