<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\PlaceMemberRequest;
use App\Models\Customer;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;

class MlmPlacementController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function store(PlaceMemberRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $placedMember = $this->dashboardService->placeMember($customer, $request->payload());

        return back()->with(
            'success',
            "Member {$placedMember['name']} berhasil ditempatkan di posisi {$placedMember['position']}."
        );
    }
}

