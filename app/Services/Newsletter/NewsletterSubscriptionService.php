<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class NewsletterSubscriptionService
{
    public function subscribe(string $email, ?string $ipAddress = null): bool
    {
        $normalizedEmail = Str::lower(trim($email));
        $subscriber = NewsletterSubscriber::query()
            ->where('email', $normalizedEmail)
            ->first();

        if ($subscriber) {
            $subscriber->update([
                'subscribed_at' => now(),
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        NewsletterSubscriber::query()->create([
            'email' => $normalizedEmail,
            'subscribed_at' => now(),
            'ip_address' => $ipAddress,
        ]);

        return true;
    }
}
