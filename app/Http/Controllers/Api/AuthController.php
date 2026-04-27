<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginCustomerApiRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Dashboard\UpdateAccountProfileRequest;
use App\Models\Customer;
use App\Services\Auth\CustomerAuthService;
use App\Services\Auth\CustomerProfileService;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Auth\ReferralContextService;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService $authService,
        private readonly CustomerRegistrationService $registrationService,
        private readonly CustomerProfileService $profileService,
        private readonly ReferralContextService $referralContextService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/auth/login-meta",
     *     tags={"Customer Auth"},
     *     summary="Metadata halaman login customer",
     *     description="Mengembalikan metadata SEO yang digunakan pada halaman login web customer.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Metadata login berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "data":{
     *                     "seo":{
     *                         "title":"Masuk ke Akun Member",
     *                         "description":"Masuk ke akun member Webstore.",
     *                         "canonical":"http://localhost/login"
     *                     }
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function loginMeta(): JsonResponse
    {
        return response()->json([
            'data' => [
                'seo' => [
                    'title' => 'Masuk ke Akun Member',
                    'description' => 'Masuk ke akun member '.config('app.name').'. Nikmati harga eksklusif, pantau e-wallet, dan kelola jaringan afiliasi Anda kapan saja.',
                    'canonical' => route('login'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/register-meta",
     *     tags={"Customer Auth"},
     *     summary="Metadata halaman registrasi customer",
     *     description="Mengembalikan metadata SEO dan referral code aktif yang digunakan pada halaman registrasi web customer.",
     *
     *     @OA\Parameter(name="referral_code", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="username", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Metadata registrasi berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "data":{
     *                     "referralCode":"ABCD1234",
     *                     "referralUsername":"mitra.api",
     *                     "seo":{
     *                         "title":"Daftar Jadi Member",
     *                         "description":"Bergabunglah sebagai member Webstore.",
     *                         "canonical":"http://localhost/register"
     *                     }
     *                 }
     *             }
     *         )
     *     )
     * )
     */
    public function registerMeta(Request $request): JsonResponse
    {
        $referralContext = $this->referralContextService->captureFromRequest($request);

        return response()->json([
            'data' => [
                'referralCode' => $referralContext['referralCode'],
                'referralUsername' => $referralContext['referralUsername'],
                'seo' => [
                    'title' => 'Daftar Jadi Member',
                    'description' => 'Bergabunglah sebagai member '.config('app.name').' dan nikmati harga eksklusif, bonus afiliasi, serta akses ke ribuan produk unggulan. Gratis daftar sekarang!',
                    'canonical' => route('register'),
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Customer Auth"},
     *     summary="Registrasi customer API",
     *     description="Mendaftarkan customer baru melalui API sesuai rule registrasi customer web.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","username","email","telp","gender","password","password_confirmation","terms"},
     *
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="username", type="string", example="budi01"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *             @OA\Property(property="telp", type="string", example="08123456789"),
     *             @OA\Property(property="nik", type="string", nullable=true, example="3276010101010001"),
     *             @OA\Property(property="gender", type="string", example="L"),
     *             @OA\Property(property="alamat", type="string", nullable=true, example="Jl. Merdeka No. 1"),
     *             @OA\Property(property="referral_code", type="string", nullable=true, example="ABCD1234"),
     *             @OA\Property(property="password", type="string", format="password", example="secret12345"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret12345"),
     *             @OA\Property(property="terms", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi berhasil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Registrasi customer berhasil.",
     *                 "data":{
     *                     "customer":{
     *                         "id":1,
     *                         "name":"Budi Santoso",
     *                         "username":"budi01",
     *                         "email":"budi@example.com",
     *                         "phone":"08123456789",
     *                         "status":1,
     *                         "ref_code":"ABCD1234"
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Data tidak valid.",
     *                 "errors":{"email":{"Email sudah terdaftar."}}
     *             }
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $customer = $this->registrationService->register($request);

        return response()->json([
            'message' => 'Registrasi customer berhasil.',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                    'ref_code' => $customer->ref_code,
                ],
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Customer Auth"},
     *     summary="Login customer API",
     *     description="Autentikasi customer menggunakan username dan password untuk member aktif (status 3), lalu generate Sanctum personal access token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"username","password"},
     *
     *             @OA\Property(property="username", type="string", example="member001"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="device_name", type="string", nullable=true, example="android-app")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Login API berhasil.",
     *                 "data":{
     *                     "token_type":"Bearer",
     *                     "access_token":"1|abcdefghijklmnopqrstuvwxyz",
     *                     "customer":{
     *                         "id":1,
     *                         "name":"Budi Santoso",
     *                         "username":"budi01",
     *                         "email":"budi@example.com",
     *                         "phone":"08123456789",
     *                         "status":3
     *                     }
     *                 }
     *             },
     *
     *             @OA\Property(property="message", type="string", example="Login API berhasil."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="access_token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *                 @OA\Property(
     *                     property="customer",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Budi Santoso"),
     *                     @OA\Property(property="username", type="string", example="budi01"),
     *                     @OA\Property(property="email", type="string", example="budi@example.com"),
     *                     @OA\Property(property="phone", type="string", nullable=true, example="08123456789"),
     *                     @OA\Property(property="status", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal atau kredensial tidak valid",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Username atau kata sandi salah.",
     *                 "errors":{"username":{"Username atau kata sandi salah."}}
     *             }
     *         )
     *     )
     * )
     */
    public function login(LoginCustomerApiRequest $request): JsonResponse
    {
        $result = $this->authService->attemptApiLogin(
            $request->string('username')->toString(),
            $request->string('password')->toString(),
            $request->input('device_name')
        );

        if (
            $result === null
            || ! (($result['customer'] ?? null) instanceof Customer)
            || (int) ($result['customer']->status ?? 0) !== 3
        ) {
            return response()->json([
                'message' => 'Username atau kata sandi salah.',
                'errors' => [
                    'username' => ['Username atau kata sandi salah.'],
                ],
            ], 422);
        }

        /** @var Customer $customer */
        $customer = $result['customer'];

        return response()->json([
            'message' => 'Login API berhasil.',
            'data' => [
                'token_type' => $result['token_type'],
                'access_token' => $result['access_token'],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'status' => $customer->status,
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Customer Auth"},
     *     summary="Logout customer API",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *
     *         @OA\JsonContent(example={"message":"Logout API berhasil."})
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $customer = $request->user('sanctum');

        if (! $customer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $this->authService->logoutApi($customer);

        return response()->json([
            'message' => 'Logout API berhasil.',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Customer Auth"},
     *     summary="Profil customer saat ini",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data profil customer",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "success":true,
     *                 "message":"Profile loaded",
     *                 "data":{
     *                     "id":24,
     *                     "name":"Tumbur Siahaan",
     *                     "username":"ZENITH02",
     *                     "email":"3zenithsinergiutama@gmail.com",
     *                     "phone":"081312000697",
     *                     "status":3,
     *                     "member_package":"ZENNER Ultra",
     *                     "referral_code":"ABCD1234",
     *                     "account_compleated":true,
     *                     "summary":{"total_bonus":0,"network_count":0,"sponsor_count":0},
     *                     "orders":{"total":0,"processing":0,"completed":0},
     *                     "mitra":{"prospek":0,"aktif":0,"pasif":0},
     *                     "network_binary":{"bonus":0,"sponsor":0,"matching":0,"pairing":0,"cashback":0,"rewards":0,"retail":0,"lifetime_cash":0},
     *                     "promo":{"active_count":0},
     *                     "wallet":{"balance":0,"reward_points":0,"active":true}
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $customer = $request->user('sanctum');

        if (! $customer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile loaded',
            'data' => $this->profileService->getApiProfile($customer),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me-form",
     *     tags={"Customer Auth"},
     *     summary="Form profil customer saat ini",
     *     description="Mengembalikan data nilai form profil customer beserta metadata validasi UpdateAccountProfileRequest.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Data form profil berhasil diambil",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Form profil akun berhasil diambil.",
     *                 "data":{
     *                     "form":{
     *                         "username":"budi.santoso",
     *                         "name":"Budi Santoso",
     *                         "nik":"3276010101010001",
     *                         "gender":"L",
     *                         "email":"budi@example.com",
     *                         "phone":"08123456789",
     *                         "bank_name":"BCA",
     *                         "bank_account":"1234567890",
     *                         "npwp_nama":"Budi Santoso",
     *                         "npwp_number":"12.345.678.9-012.000",
     *                         "npwp_jk":1,
     *                         "npwp_date":"2024-01-31",
     *                         "npwp_alamat":"Jl. Merdeka No. 1, Bandung",
     *                         "npwp_menikah":"Y",
     *                         "npwp_anak":"2",
     *                         "npwp_kerja":"Y",
     *                         "npwp_office":"PT Contoh Sukses"
     *                     },
     *                     "validation":{
     *                         "required_fields":{"username","name","nik","gender","email","phone","bank_name","bank_account"},
     *                         "rules":{"username":{"required","string","min:3","max:30","regex:/^[a-zA-Z0-9_.]+$/","unique"}},
     *                         "messages":{"username.required":"Username wajib diisi."}
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Tidak terautentikasi",
     *
     *         @OA\JsonContent(example={"message":"Tidak terautentikasi."})
     *     )
     * )
     */
    public function me_form(Request $request): JsonResponse
    {
        $authenticatedCustomer = $request->user('sanctum');

        if (! $authenticatedCustomer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        $customer = Customer::query()
            ->with('npwp')
            ->find((int) $authenticatedCustomer->id) ?? $authenticatedCustomer;

        $npwp = $customer->relationLoaded('npwp') ? $customer->getRelation('npwp') : null;
        $rules = UpdateAccountProfileRequest::profileRules((int) $customer->id, $customer->phone);
        $form = UpdateAccountProfileRequest::normalizeForValidation([
            'username' => $customer->username,
            'name' => $customer->name,
            'nik' => $customer->nik,
            'gender' => $customer->gender,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'bank_name' => $customer->bank_name,
            'bank_account' => $customer->bank_account,
            'npwp_nama' => $npwp?->nama,
            'npwp_number' => $npwp?->npwp,
            'npwp_jk' => $npwp?->jk,
            'npwp_date' => $npwp?->npwp_date?->toDateString(),
            'npwp_alamat' => $npwp?->alamat,
            'npwp_menikah' => $npwp?->menikah,
            'npwp_anak' => $npwp?->anak,
            'npwp_kerja' => $npwp?->kerja,
            'npwp_office' => $npwp?->office,
        ]);

        return response()->json([
            'message' => 'Form profil akun berhasil diambil.',
            'data' => [
                'form' => $form,
                'validation' => [
                    'required_fields' => collect($rules)
                        ->filter(fn (array $fieldRules): bool => in_array('required', $fieldRules, true))
                        ->keys()
                        ->values()
                        ->all(),
                    'rules' => $this->serializeValidationRules($rules),
                    'messages' => (new UpdateAccountProfileRequest)->messages(),
                ],
            ],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/auth/me",
     *     tags={"Customer Auth"},
     *     summary="Perbarui profil customer saat ini",
     *     description="Memperbarui data profil customer yang sedang login menggunakan payload form profil customer.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"username","name","nik","gender","email","phone","bank_name","bank_account"},
     *
     *             @OA\Property(property="username", type="string", example="budi.santoso"),
     *             @OA\Property(property="name", type="string", example="Budi Santoso"),
     *             @OA\Property(property="nik", type="string", example="3276010101010001"),
     *             @OA\Property(property="gender", type="string", enum={"L","P"}, example="L"),
     *             @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="bank_name", type="string", example="BCA"),
     *             @OA\Property(property="bank_account", type="string", example="1234567890"),
     *             @OA\Property(property="npwp_nama", type="string", nullable=true, example="Budi Santoso"),
     *             @OA\Property(property="npwp_number", type="string", nullable=true, example="12.345.678.9-012.000"),
     *             @OA\Property(property="npwp_jk", type="integer", nullable=true, enum={1,2}, example=1),
     *             @OA\Property(property="npwp_date", type="string", format="date", nullable=true, example="2024-01-31"),
     *             @OA\Property(property="npwp_alamat", type="string", nullable=true, example="Jl. Merdeka No. 1, Bandung"),
     *             @OA\Property(property="npwp_menikah", type="string", nullable=true, enum={"Y","N"}, example="Y"),
     *             @OA\Property(property="npwp_anak", type="string", nullable=true, example="2"),
     *             @OA\Property(property="npwp_kerja", type="string", nullable=true, enum={"Y","N"}, example="Y"),
     *             @OA\Property(property="npwp_office", type="string", nullable=true, example="PT Contoh Sukses")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Profil berhasil diperbarui", @OA\JsonContent(example={"message":"Profil akun berhasil diperbarui."})),
     *     @OA\Response(response=401, description="Tidak terautentikasi", @OA\JsonContent(example={"message":"Tidak terautentikasi."})),
     *     @OA\Response(response=422, description="Validasi gagal", @OA\JsonContent(example={"message":"Data tidak valid.","errors":{"field":{"Field wajib diisi."}}}))
     * )
     */
    public function updateMe(
        UpdateAccountProfileRequest $request,
        DashboardService $dashboardService,
    ): JsonResponse {
        $customer = $request->user('sanctum');

        if (! $customer instanceof Customer) {
            return response()->json([
                'message' => 'Tidak terautentikasi.',
            ], 401);
        }

        try {
            $dashboardService->updateAccountProfile($customer, $request->payload());

            return response()->json([
                'message' => 'Profil akun berhasil diperbarui.',
            ]);
        } catch (ValidationException $exception) {
            $firstError = collect($exception->errors())->flatten()->first();
            $message = is_string($firstError) ? $firstError : 'Gagal memperbarui profil akun.';

            return response()->json([
                'message' => $message,
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    /**
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, array<int, string>>
     */
    private function serializeValidationRules(array $rules): array
    {
        return collect($rules)
            ->map(function (array $fieldRules, string $field): array {
                return collect($fieldRules)
                    ->map(function (mixed $rule) use ($field): string {
                        if (is_string($rule)) {
                            return $rule;
                        }

                        if ($rule instanceof Unique) {
                            return 'unique';
                        }

                        if ($rule instanceof \Closure) {
                            return $field === 'phone'
                                ? 'custom:phone_max_7_accounts'
                                : 'custom';
                        }

                        return strtolower(class_basename($rule));
                    })
                    ->values()
                    ->all();
            })
            ->all();
    }

    /**
     * @OA\Post(
     *     path="/api/auth/impersonation/stop",
     *     tags={"Customer Auth"},
     *     summary="Hentikan sesi impersonasi customer",
     *     description="Mengakhiri sesi impersonasi customer dan mengembalikan konteks autentikasi admin jika tersedia.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(example={})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Impersonasi berhasil dihentikan",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message":"Impersonasi berhasil dihentikan.",
     *                 "data":{
     *                     "redirect_to":"http://localhost/admin/customers",
     *                     "admin_still_authenticated":true
     *                 }
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Tidak ada sesi impersonasi aktif",
     *
     *         @OA\JsonContent(example={"message":"Tidak ada sesi impersonasi aktif."})
     *     )
     * )
     */
    public function stopImpersonation(Request $request): JsonResponse
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return response()->json([
                'message' => 'Tidak ada sesi impersonasi aktif.',
            ], 400);
        }

        $adminStillAuthenticated = Auth::guard('web')->check();

        Auth::guard('customer')->logout();

        $request->session()->forget('impersonation');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $redirectUrl = $adminStillAuthenticated
            ? route('filament.control-panel.resources.customers.index')
            : route('home');

        return response()->json([
            'message' => 'Impersonasi berhasil dihentikan.',
            'data' => [
                'redirect_to' => $redirectUrl,
                'admin_still_authenticated' => $adminStillAuthenticated,
            ],
        ]);
    }
}
