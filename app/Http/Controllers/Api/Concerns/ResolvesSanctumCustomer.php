<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Customer;
use Illuminate\Http\Request;

trait ResolvesSanctumCustomer
{
    protected function resolveSanctumCustomer(Request $request): ?Customer
    {
        $tokenable = $request->user('sanctum');

        return $tokenable instanceof Customer ? $tokenable : null;
    }
}
