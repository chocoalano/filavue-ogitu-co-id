<?php

use App\Filament\Resources\CustomerWithdrawals\Schemas\CustomerWithdrawalInfolist;
use App\Models\CustomerWalletTransaction;

it('applies admin fee, received amount, and derived balance formulas for withdrawal infolist', function (): void {
    $record = new CustomerWalletTransaction;
    $record->amount = 10000;
    $record->balance_before = 100000;

    $adminFee = invokePrivateStatic(CustomerWithdrawalInfolist::class, 'adminFee');
    $totalReceived = invokePrivateStatic(CustomerWithdrawalInfolist::class, 'totalReceived', [$record]);
    $totalDeducted = invokePrivateStatic(CustomerWithdrawalInfolist::class, 'totalDeducted', [$record]);
    $balanceAfterByFormula = invokePrivateStatic(CustomerWithdrawalInfolist::class, 'balanceAfterByFormula', [$record]);

    expect($adminFee)->toBe(6500)
        ->and($totalReceived)->toBe(10000)
        ->and($totalDeducted)->toBe(16500)
        ->and($balanceAfterByFormula)->toBe(83500);
});

function invokePrivateStatic(string $className, string $methodName, array $arguments = []): mixed
{
    $reflection = new ReflectionMethod($className, $methodName);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs(null, $arguments);
}
