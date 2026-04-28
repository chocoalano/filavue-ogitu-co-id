<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Repositories\Auth\Contracts\CustomerRegistrationRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;

class CustomerRegistrationService
{
    public function __construct(
        private readonly CustomerRegistrationRepositoryInterface $repository,
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    public function register(RegisterRequest $request): Customer
    {
        $sponsor = $request->sponsor();
        $proses = $this->repository->create([
            'name' => $request->string('name')->trim()->toString(),
            'username' => $request->string('username')->trim()->lower()->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'phone' => $request->string('telp')->trim()->toString(),
            'nik' => $request->filled('nik') ? $request->string('nik')->trim()->toString() : null,
            'gender' => $request->string('gender')->toString(),
            'alamat' => $request->filled('alamat') ? $request->string('alamat')->trim()->toString() : null,
            'password' => $request->string('password')->toString(),
            'sponsor_id' => $sponsor?->id ?? Customer::first()->orderBy('id')->value('id')->id,
            'status' => 3,
        ]);

        if ($proses) {
            $this->dashboardRepository->callRegistrationProcedure($proses->id);
        }

        return $proses;
    }
}
