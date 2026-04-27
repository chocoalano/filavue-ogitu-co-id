<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\SubscribeNewsletterRequest;
use App\Services\Newsletter\NewsletterSubscriptionService;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController extends Controller
{
    public function __construct(
        private readonly NewsletterSubscriptionService $newsletterSubscriptionService,
    ) {}

    public function __invoke(SubscribeNewsletterRequest $request): RedirectResponse
    {
        $isNewSubscriber = $this->newsletterSubscriptionService->subscribe(
            $request->email(),
            $request->ip()
        );

        if ($isNewSubscriber) {
            return back()->with('success', 'Berhasil berlangganan promo terbaru.');
        }

        return back()->with('success', 'Email sudah terdaftar. Promo terbaru tetap akan kami kirimkan.');
    }
}
