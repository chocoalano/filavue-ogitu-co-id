<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Customer;
use App\Models\CustomerWhatsAppConfirmation;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Auth\ReferralContextService;
use App\Services\Dashboard\DashboardService;
use App\Services\QontactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CustomerAuthController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $authService,
        private readonly CustomerRegistrationService $registrationService,
        private readonly ReferralContextService $referralContextService,
        private readonly DashboardService $dashboardService,
        private readonly QontactService $qontactService,
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'seo' => [
                'title' => 'Masuk ke Akun Member',
                'description' => 'Masuk ke akun member '.config('app.name').'. Nikmati harga eksklusif, pantau e-wallet, dan kelola jaringan afiliasi Anda kapan saja.',
                'canonical' => route('login'),
            ],
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! $this->authService->attemptLogin(
            $request->string('username'),
            $request->string('password'),
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['username' => 'Username atau kata sandi salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister(Request $request): Response
    {
        $referralContext = $this->referralContextService->captureFromRequest($request);

        return Inertia::render('Auth/Register', [
            'referralCode' => $referralContext['referralCode'],
            'referralUsername' => $referralContext['referralUsername'],
            'debugMode' => config('app.debug'),
            'seo' => [
                'title' => 'Daftar Jadi Member',
                'description' => 'Bergabunglah sebagai member '.config('app.name').' dan nikmati harga eksklusif, bonus afiliasi, serta akses ke ribuan produk unggulan. Gratis daftar sekarang!',
                'canonical' => route('register'),
            ],
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse|SymfonyResponse
    {
        try {
            $this->registrationService->register($request);
        } catch (Throwable $exception) {
            Log::error('Customer registration failed.', [
                'username' => $request->string('username')->toString(),
                'email' => $request->string('email')->toString(),
                'telp' => $request->string('telp')->toString(),
                'referral_username' => $request->string('referral_username')->toString(),
                'referral_code' => $request->string('referral_code')->toString(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'error' => 'Pendaftaran gagal. Silakan coba lagi.',
                ])
                ->onlyInput(
                    'name',
                    'username',
                    'email',
                    'telp',
                    'nik',
                    'gender',
                    'alamat',
                    'referral_username',
                    'referral_code',
                    'terms',
                );
        }

        $customer = Customer::query()
            ->where('username', $request->string('username')->trim()->lower()->toString())
            ->first();

        $normalizedPhone = $this->qontactService->normalizePhoneNumber(
            $request->string('telp')->toString()
        );

        if (config('services.qontak.wa_gateway_hemat_mode')) {
            return redirect()->route('login')->with('status', 'Akun berhasil dibuat! Silakan masuk.');
        }

        if ($normalizedPhone !== '' && CustomerWhatsAppConfirmation::isConfirmed($normalizedPhone)) {
            return redirect()->route('login')->with('status', 'Akun berhasil dibuat! Silakan masuk.');
        }

        $waUrl = $this->buildWhatsAppConfirmationUrl($customer, $normalizedPhone);

        return Inertia::location($waUrl);
    }

    public function confirmWhatsApp(Request $request, Customer $customer): RedirectResponse|Response
    {
        $isLoggedIn = auth('customer')->check();

        if (! $request->hasValidSignature()) {
            $error = 'Link konfirmasi tidak valid atau sudah kedaluwarsa. Silakan ulangi proses konfirmasi WhatsApp.';

            return $isLoggedIn
                ? redirect()->route('dashboard')->withErrors(['error' => $error])
                : redirect()->route('login')->withErrors(['error' => $error]);
        }

        $phone = $this->qontactService->normalizePhoneNumber((string) ($customer->phone ?? ''));

        if ($phone === '') {
            $error = 'Nomor WhatsApp tidak ditemukan pada akun ini. Hubungi admin untuk bantuan.';

            return $isLoggedIn
                ? redirect()->route('dashboard')->withErrors(['error' => $error])
                : redirect()->route('login')->withErrors(['error' => $error]);
        }

        if (! CustomerWhatsAppConfirmation::isConfirmed($phone)) {
            return Inertia::render('Auth/WaConfirmationPending', [
                'username' => (string) ($customer->username ?? ''),
                'maskedPhone' => $this->maskPhone($phone),
                'waUrl' => $this->buildPendingWaUrl($customer, $request->fullUrl()),
                'confirmUrl' => $request->fullUrl(),
            ]);
        }

        CustomerWhatsAppConfirmation::linkCustomer($phone, (int) $customer->getKey());

        $success = 'Nomor WhatsApp berhasil dikonfirmasi! Fitur topup dan penarikan saldo sudah dapat digunakan.';

        return $isLoggedIn
            ? redirect()->route('dashboard')->with('success', $success)
            : redirect()->route('login')->with('status', 'Nomor WhatsApp berhasil dikonfirmasi! Akun Anda aktif. Silakan masuk.');
    }

    private function maskPhone(string $phone): string
    {
        $len = \strlen($phone);

        if ($len <= 7) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 4).str_repeat('*', $len - 7).substr($phone, -3);
    }

    private function buildPendingWaUrl(Customer $customer, string $confirmUrl): ?string
    {
        $gatewayNumber = preg_replace('/[^0-9]/', '', (string) config('services.qontak.wa_gateway_number', ''));

        if ($gatewayNumber === '') {
            return null;
        }

        $username = (string) ($customer->username ?? '');
        $message = "Halo, saya *{$username}* ingin mengkonfirmasi nomor WhatsApp ini."
            ."\n\nLangkah konfirmasi:"
            ."\n1. Kirim pesan ini ke kami terlebih dahulu."
            ."\n2. Setelah pesan terkirim, klik link berikut untuk menyelesaikan konfirmasi:"
            ."\n{$confirmUrl}"
            ."\n\nLink berlaku 48 jam.";

        return 'https://wa.me/'.$gatewayNumber.'?text='.rawurlencode($message);
    }

    private function buildWhatsAppConfirmationUrl(?Customer $customer, string $normalizedPhone): string
    {
        $gatewayNumber = preg_replace('/[^0-9]/', '', (string) config('services.qontak.wa_gateway_number', ''));

        if ($gatewayNumber === '') {
            return route('login', [], true).'?status='.rawurlencode('Akun berhasil dibuat! Silakan masuk.');
        }

        $confirmUrl = $customer
            ? URL::signedRoute('wa.confirm', ['customer' => $customer->getKey()], now()->addHours(48))
            : null;

        $username = $customer?->username ?? '';

        $message = "Halo, saya *{$username}* baru saja mendaftar sebagai member dan ingin mengkonfirmasi nomor WhatsApp ini."
            ."\n\nLangkah konfirmasi:"
            ."\n1. Kirim pesan ini ke kami terlebih dahulu."
            ."\n2. Setelah pesan terkirim, klik link berikut untuk menyelesaikan konfirmasi:"
            ."\n{$confirmUrl}"
            ."\n\nLink berlaku 48 jam.";

        return 'https://wa.me/'.$gatewayNumber.'?text='.rawurlencode($message);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/');
    }

    public function dashboard(Request $request): Response
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();
        // dd($customer);
        if (! $customer) {
            abort(401);
        }

        $walletFilters = [
            'search' => $request->query('wallet_search'),
            'type' => $request->query('wallet_type'),
            'status' => $request->query('wallet_status'),
        ];
        $orderFilters = [
            'q' => $request->query('orders_q'),
            'status' => $request->query('orders_status'),
            'sort' => $request->query('orders_sort'),
            'date_from' => $request->query('orders_date_from'),
            'date_to' => $request->query('orders_date_to'),
        ];
        $generationFilters = [
            'level' => $request->query('generation_level'),
        ];
        $networkRootId = $request->integer('network_root_id');

        if ($networkRootId <= 0) {
            $networkRootId = null;
        }

        return Inertia::render('Auth/Dashboard/Index', [
            'seo' => [
                'title' => 'Dashboard Member',
                'description' => 'Kelola akun member Anda di '.config('app.name').'. Pantau e-wallet, akses riwayat pesanan, dan nikmati penawaran eksklusif hanya untuk member.',
                'canonical' => route('dashboard'),
            ],
            ...$this->dashboardService->getPageData(
                $customer,
                max(1, (int) $request->integer('orders_page', 1)),
                max(1, (int) $request->integer('wallet_page', 1)),
                $walletFilters,
                $orderFilters,
                $networkRootId,
                max(1, (int) $request->integer('generation_page', 1)),
                $generationFilters,
            ),
        ]);
    }

    public function stopImpersonation(Request $request): RedirectResponse|SymfonyResponse
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return redirect()->route('home');
        }

        $adminStillAuthenticated = Auth::guard('web')->check();

        Auth::guard('customer')->logout();

        $request->session()->forget('impersonation');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($adminStillAuthenticated) {
            $customerIndexUrl = route('filament.control-panel.resources.customers.index');

            if ($request->header('X-Inertia')) {
                return Inertia::location($customerIndexUrl);
            }

            return redirect()->to($customerIndexUrl);
        }

        return redirect()->route('home');
    }
}
