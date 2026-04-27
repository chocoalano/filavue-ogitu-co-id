<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Customer;
use App\Services\QontactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CustomerPasswordResetController extends Controller
{
    public function __construct(
        private readonly QontactService $qontactService,
    ) {}

    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'seo' => [
                'title' => 'Lupa Kata Sandi',
                'description' => 'Reset kata sandi akun member '.config('app.name').' melalui WhatsApp.',
                'canonical' => route('password.forgot'),
            ],
        ]);
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse|SymfonyResponse
    {
        $username = trim($request->string('username')->lower()->toString());
        $telp = trim($request->string('telp')->toString());
        $normalizedPhone = $this->qontactService->normalizePhoneNumber($telp);

        $customer = Customer::query()
            ->where('username', $username)
            ->where(function ($query) use ($telp, $normalizedPhone): void {
                $query->where('phone', $telp);

                if ($normalizedPhone !== $telp) {
                    $query->orWhere('phone', $normalizedPhone);
                }
            })
            ->first();

        if (! $customer || ! filled($customer->phone)) {
            return back()
                ->withErrors(['error' => 'Username dan nomor WhatsApp tidak cocok dengan akun manapun.'])
                ->onlyInput('username', 'telp');
        }

        $pwh = substr(hash('sha256', (string) $customer->password), 0, 16);

        $resetUrl = URL::signedRoute('password.reset', [
            'customer' => $customer->getKey(),
            'pwh' => $pwh,
        ], now()->addHours(2));

        $customerPhone = preg_replace('/[^0-9]/', '', $this->qontactService->normalizePhoneNumber((string) ($customer->phone ?? '')));

        if ($customerPhone === '') {
            return back()
                ->withErrors(['error' => 'Nomor WhatsApp pada akun ini tidak valid. Silakan hubungi admin.'])
                ->onlyInput('username', 'telp');
        }

        $message = 'Link reset kata sandi akun Anda:'
            ."\n{$resetUrl}"
            ."\n\nLink berlaku 2 jam. Jangan bagikan link ini kepada siapapun.";

        $waUrl = 'https://wa.me/'.$customerPhone.'?text='.rawurlencode($message);

        return Inertia::location($waUrl);
    }

    public function showResetPassword(Request $request, Customer $customer): Response|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('password.forgot')
                ->withErrors(['error' => 'Link reset kata sandi tidak valid atau sudah kedaluwarsa.']);
        }

        $pwh = substr(hash('sha256', (string) $customer->password), 0, 16);

        if ($request->query('pwh') !== $pwh) {
            return redirect()->route('password.forgot')
                ->withErrors(['error' => 'Link reset kata sandi sudah tidak berlaku karena kata sandi telah diubah.']);
        }

        return Inertia::render('Auth/ResetPassword', [
            'resetUrl' => $request->fullUrl(),
            'seo' => [
                'title' => 'Buat Kata Sandi Baru',
                'description' => 'Buat kata sandi baru untuk akun member '.config('app.name').'.',
                'canonical' => route('password.reset', ['customer' => $customer->getKey()]),
            ],
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, Customer $customer): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('password.forgot')
                ->withErrors(['error' => 'Link reset kata sandi tidak valid atau sudah kedaluwarsa.']);
        }

        $pwh = substr(hash('sha256', (string) $customer->password), 0, 16);

        if ($request->query('pwh') !== $pwh) {
            return redirect()->route('password.forgot')
                ->withErrors(['error' => 'Link reset kata sandi sudah tidak berlaku karena kata sandi telah diubah.']);
        }

        $customer->update(['password' => $request->string('password')->toString()]);

        return redirect()->route('login')
            ->with('status', 'Kata sandi berhasil direset! Silakan masuk dengan kata sandi baru Anda.');
    }
}
