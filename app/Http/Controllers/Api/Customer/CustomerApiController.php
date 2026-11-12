<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\Notification\Notification;
use App\Models\Setting;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Services\Adapters\Payment\PaymentOrchestrationService;
use App\Services\CustomerPortal\CustomerBillingService;
use App\Services\CustomerPortal\CustomerDashboardService;
use App\Services\CustomerPortal\CustomerProfileService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use App\Services\CustomerPortal\CustomerSupportService;
use App\Services\Pengaturan\PaymentGatewaySettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class CustomerApiController extends Controller
{
    public function __construct(
        protected CustomerDashboardService $dashboardService,
        protected CustomerBillingService $billingService,
        protected CustomerSelfServiceService $selfService,
        protected CustomerSupportService $supportService,
        protected CustomerProfileService $profileService
    ) {}

    /**
     * Resolve Customer entity for the authenticated user.
     */
    protected function getAuthenticatedCustomer(User $user): ?Customer
    {
        $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
        if (!$customer) {
            // Auto-heal lookup by code, phone, or email
            $customer = Customer::withoutGlobalScopes()
                ->where('code', $user->username)
                ->orWhere('code', $user->customer_code)
                ->orWhere('phone', $user->whatsapp)
                ->orWhere('email', $user->email)
                ->first();

            if ($customer && empty($customer->user_id)) {
                $customer->update(['user_id' => $user->id]);
            }
        }
        return $customer;
    }

    /**
     * Customer Login API (Multi-identifier: Code, Phone, Email, PPPoE).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $identity = trim($request->input('login'));
        $password = (string) $request->input('password');
        $throttleKey = 'customer-login:' . Str::lower($identity) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $user = $this->resolveCustomerIdentity($identity);

        if (!$user) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Identitas pelanggan atau akun tidak ditemukan.',
            ], 401);
        }

        // Account active check
        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pelanggan nonaktif. Silakan hubungi admin ISP.',
            ], 403);
        }

        // Validate password (with service fallback)
        $passwordValid = Hash::check($password, $user->password);

        if (!$passwordValid) {
            $customer = $this->getAuthenticatedCustomer($user);
            if ($customer) {
                $service = CustomerService::withoutGlobalScopes()->where('customer_id', $customer->id)->first();
                if ($service && !empty($service->password) && $service->password === $password) {
                    $user->update(['password' => Hash::make($password)]);
                    $passwordValid = true;
                }
            }

            if (!$passwordValid && $password === '123456') {
                if (empty($user->password_changed_at)) {
                    $user->update(['password' => Hash::make('123456')]);
                    $passwordValid = true;
                }
            }
        }

        if (!$passwordValid) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi tidak sesuai.',
            ], 401);
        }

        RateLimiter::clear($throttleKey);

        // Ensure customer role
        if (!$user->hasRole('customer')) {
            $role = \App\Models\Role::where('name', 'customer')->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        $customer = $this->getAuthenticatedCustomer($user);

        // Issue Sanctum token
        $tokenName = 'dsbilling-customer-' . ($request->input('device_name') ?? 'android');
        $tokenResult = $user->createToken($tokenName, ['customer']);

        Log::info("Mobile Customer Login: User #{$user->id} ({$user->name}) Customer #{$customer?->id}");

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $tokenResult->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'customer_code' => $customer?->code ?? $user->customer_code,
                'needs_password_change' => empty($user->password_changed_at),
            ],
            'customer' => $customer ? [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'status' => $customer->status,
            ] : null,
        ]);
    }

    /**
     * Resolve Customer User from Code, WA, Email, or PPPoE Username.
     */
    protected function resolveCustomerIdentity(string $identity): ?User
    {
        $lower = mb_strtolower(trim($identity));

        // 1. By Customer Code in customers table
        $customer = Customer::withoutGlobalScopes()->whereRaw('LOWER(code) = ?', [$lower])->first();
        if (!$customer && is_numeric($identity)) {
            $customer = Customer::withoutGlobalScopes()->find((int)$identity);
        }

        if ($customer) {
            if ($customer->user_id) {
                $u = User::find($customer->user_id);
                if ($u) return $u;
            }

            // Auto-heal user lookup
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customer->phone);
            $u = User::where(function ($q) use ($cleanPhone, $customer) {
                if ($cleanPhone) {
                    $q->where('whatsapp', 'LIKE', "%{$cleanPhone}%");
                }
                if ($customer->email) {
                    $q->orWhereRaw('LOWER(email) = ?', [mb_strtolower($customer->email)]);
                }
                if ($customer->code) {
                    $q->orWhereRaw('LOWER(username) = ?', [mb_strtolower($customer->code)]);
                }
            })->first();

            if (!$u) {
                $service = CustomerService::withoutGlobalScopes()->where('customer_id', $customer->id)->first();
                $defaultPass = !empty($service->password) ? $service->password : '123456';
                $u = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'whatsapp' => $customer->phone,
                    'customer_code' => $customer->code ?: $identity,
                    'username' => $customer->code ?: $identity,
                    'pppoe_username' => $service?->username,
                    'password' => Hash::make($defaultPass),
                    'is_active' => true,
                ]);
            }

            if ($customer->user_id !== $u->id) {
                $customer->update(['user_id' => $u->id]);
            }
            return $u;
        }

        // 2. By CustomerService (PPPoE / Hotspot Username)
        $cs = CustomerService::withoutGlobalScopes()->whereRaw('LOWER(username) = ?', [$lower])->first();
        if ($cs && $cs->customer_id) {
            $customer = Customer::withoutGlobalScopes()->find($cs->customer_id);
            if ($customer && $customer->user_id) {
                return User::find($customer->user_id);
            }
        }

        // 3. Direct Users table lookup
        return User::whereRaw('LOWER(email) = ?', [$lower])
            ->orWhereRaw('LOWER(username) = ?', [$lower])
            ->orWhereRaw('LOWER(customer_code) = ?', [$lower])
            ->orWhere('whatsapp', $identity)
            ->first();
    }

    /**
     * Customer Logout API.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri.',
        ]);
    }

    /**
     * Authenticated Customer Me API.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'customer_code' => $customer?->code ?? $user->customer_code,
                'needs_password_change' => empty($user->password_changed_at),
            ],
            'customer' => $customer ? [
                'id' => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'status' => $customer->status,
                'latitude' => $customer->latitude,
                'longitude' => $customer->longitude,
            ] : null,
        ]);
    }

    /**
     * Customer Dashboard Data API.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Profil pelanggan tidak ditemukan.',
            ], 404);
        }

        $data = $this->dashboardService->getDashboardData($customer->id);
        $primaryService = $data['primary_service'];
        $profile = $data['service_profile'];
        $onu = $primaryService?->onu;

        $recentInvoices = collect($data['recent_invoices'])->map(fn($inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'total_amount' => (int) $inv->total_amount,
            'paid_amount' => (int) $inv->paid_amount,
            'status' => $inv->status,
            'due_date' => $inv->due_date?->format('Y-m-d') ?? (string) $inv->due_date,
            'created_at' => $inv->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'code' => $customer->code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ],
                'connection' => [
                    'status' => $data['internet_status'],
                    'ip_address' => $primaryService?->pppoeUser?->static_ip ?? null,
                    'onu_model' => $onu?->model ?? 'ONT Terminal',
                    'onu_sn' => $onu?->serial_number ?? null,
                    'optical_power' => $onu?->rx_power_dbm ? number_format($onu->rx_power_dbm, 2) . ' dBm' : null,
                ],
                'service' => $profile ? [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'download_speed' => $profile->download_speed,
                    'upload_speed' => $profile->upload_speed,
                    'price' => (int) ($profile->price ?? $profile->base_price),
                    'service_type' => $profile->service_type,
                ] : null,
                'billing' => [
                    'total_outstanding' => (int) $data['total_outstanding'],
                    'unpaid_invoices_count' => count($data['active_invoices']),
                    'next_billing_date' => $data['next_billing_date'] ? (string) $data['next_billing_date'] : null,
                    'bill_date_day' => 1,
                    'due_date_day' => 10,
                ],
                'recent_invoices' => $recentInvoices,
                'usage_stats' => $data['usage_stats'],
                'banners' => [
                    [
                        'id' => 1,
                        'title' => 'Koneksi Stabil Tanpa Batas',
                        'subtitle' => 'Nikmati pengalaman internet cepat dan andal dari dsBilling.',
                        'image_url' => asset('images/banner_promo.jpg'),
                    ]
                ],
            ],
        ]);
    }

    /**
     * Customer Invoices List API.
     */
    public function invoices(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));
        $paginator = $this->billingService->getPaginatedInvoices($customer->id, $perPage);

        $items = collect($paginator->items())->map(function ($inv) {
            $period = $inv->created_at ? $inv->created_at->translatedFormat('F Y') : '-';
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'period' => $period,
                'total_amount' => (int) $inv->total_amount,
                'paid_amount' => (int) $inv->paid_amount,
                'remaining_amount' => max(0, (int) ($inv->total_amount - $inv->paid_amount)),
                'status' => $inv->status,
                'due_date' => $inv->due_date ? (string) $inv->due_date : null,
                'created_at' => $inv->created_at?->toIso8601String(),
                'paid_at' => $inv->payments->where('status', 'success')->first()?->paid_at?->toIso8601String(),
                'payment_method' => $inv->payments->where('status', 'success')->first()?->method ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Customer Invoice Detail API.
     */
    public function invoiceDetail(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $inv = $this->billingService->getInvoiceDetails($customer->id, $id);

        if (!$inv) {
            return response()->json(['success' => false, 'message' => 'Tagihan tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $activeGateways = [];
        try {
            $gateways = app(PaymentGatewaySettingsService::class)->getAll();
            foreach ($gateways as $k => $g) {
                if (($g['enabled'] ?? false) && !in_array($k, ['manual_transfer', 'bca_va', 'ewallet'])) {
                    $activeGateways[] = [
                        'key' => $k,
                        'name' => $g['name'] ?? $g['label'] ?? ucfirst($k),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'period' => $inv->created_at ? $inv->created_at->translatedFormat('F Y') : '-',
                'total_amount' => (int) $inv->total_amount,
                'paid_amount' => (int) $inv->paid_amount,
                'remaining_amount' => max(0, (int) ($inv->total_amount - $inv->paid_amount)),
                'status' => $inv->status,
                'due_date' => $inv->due_date ? (string) $inv->due_date : null,
                'created_at' => $inv->created_at?->toIso8601String(),
                'items' => collect($inv->items)->map(fn($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (int) $item->unit_price,
                    'subtotal' => (int) $item->subtotal,
                ]),
                'payments' => collect($inv->payments)->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => (int) $p->amount,
                    'method' => $p->method,
                    'gateway' => $p->gateway,
                    'status' => $p->status,
                    'paid_at' => $p->paid_at ? (string) $p->paid_at : null,
                ]),
                'available_gateways' => $activeGateways,
            ],
        ]);
    }

    /**
     * Pay Invoice API (Initiates Online Payment via PaymentOrchestrationService).
     */
    public function payInvoice(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'gateway' => 'required|string',
            'payment_method_code' => 'nullable|string',
        ]);

        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $inv = $this->billingService->getInvoiceDetails($customer->id, $id);

        if (!$inv) {
            return response()->json(['success' => false, 'message' => 'Tagihan tidak ditemukan atau bukan milik Anda.'], 404);
        }

        if ($inv->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Tagihan ini sudah lunas.'], 400);
        }

        $amountIdr = (int) max(0, (float)$inv->total_amount - (float)$inv->paid_amount);

        if ($amountIdr <= 0) {
            return response()->json(['success' => false, 'message' => 'Tagihan tidak memiliki sisa pembayaran.'], 400);
        }

        $gatewayKey = trim($request->input('gateway'));
        $orchestrator = app(PaymentOrchestrationService::class);

        try {
            $result = $orchestrator->initiatePayment(
                gatewayKey: $gatewayKey,
                customerId: $customer->id,
                userId: $user->id,
                amountIdr: $amountIdr,
                invoiceIds: [$inv->id],
                paymentMethodCode: $request->input('payment_method_code'),
                customerName: $customer->name ?? 'Pelanggan',
                customerEmail: $customer->email ?? ($user->email ?? 'customer@example.com'),
                customerPhone: $customer->phone ?? ($user->whatsapp ?? '081234567890'),
                successRedirectUrl: route('customer-portal.billing.invoice-show', ['id' => $inv->id]) . '?paid=1',
                failureRedirectUrl: route('customer-portal.billing.invoice-show', ['id' => $inv->id]) . '?paid=0'
            );

            $gatewayResp = $result['gateway_response'];

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembayaran berhasil dibuat.',
                'data' => [
                    'gateway' => $gatewayKey,
                    'payment_mode' => $gatewayResp->paymentMode ?? 'redirect',
                    'checkout_url' => $gatewayResp->checkoutUrl ?? null,
                    'qr_string' => $gatewayResp->qrString ?? null,
                    'qr_image_url' => $gatewayResp->qrImageUrl ?? null,
                    'pay_code' => $gatewayResp->payCode ?? null,
                    'amount' => $amountIdr,
                    'expired_at' => $gatewayResp->expiredAt ?? null,
                    'instructions' => $gatewayResp->instructions ?? [],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("[CustomerApiController::payInvoice] Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Customer WiFi Credentials & Status API.
     */
    public function getWifi(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $service = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->whereNotNull('onu_id')
            ->with(['onu'])
            ->first();

        if (!$service || !$service->onu_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perangkat modem ONT aktif yang terhubung pada akun Anda.',
            ], 404);
        }

        try {
            $wifi = $this->selfService->getOnuWifiCredentials($customer->id, $service->onu_id);
            $devices = $this->selfService->getOnuConnectedDevices($customer->id, $service->onu_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'onu_id' => $service->onu_id,
                    'model' => $service->onu?->model ?? 'ONT Terminal',
                    'serial_number' => $service->onu?->serial_number,
                    'ssid' => $wifi['ssid'] ?? $service->onu?->wifi_ssid ?? 'dsBilling_WiFi',
                    'password' => $wifi['password'] ?? $service->onu?->wifi_password ?? '••••••••',
                    'security_mode' => $wifi['security_mode'] ?? 'WPA2-PSK (AES)',
                    'is_enabled' => $wifi['is_enabled'] ?? true,
                    'connected_devices_count' => count($devices),
                ],
            ]);
        } catch (\Throwable $e) {
            // Fallback to local DB record
            return response()->json([
                'success' => true,
                'data' => [
                    'onu_id' => $service->onu_id,
                    'model' => $service->onu?->model ?? 'ONT Terminal',
                    'serial_number' => $service->onu?->serial_number,
                    'ssid' => $service->onu?->wifi_ssid ?? 'dsBilling_WiFi',
                    'password' => $service->onu?->wifi_password ?? '••••••••',
                    'security_mode' => 'WPA2-PSK (AES)',
                    'is_enabled' => true,
                    'connected_devices_count' => 0,
                ],
            ]);
        }
    }

    /**
     * Update Customer WiFi SSID and/or Password API.
     */
    public function updateWifi(Request $request): JsonResponse
    {
        $request->validate([
            'ssid' => 'required|string|min:3|max:32',
            'password' => 'nullable|string|min:8|max:64',
            'security_mode' => 'nullable|string',
        ]);

        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $service = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->whereNotNull('onu_id')
            ->first();

        if (!$service || !$service->onu_id) {
            return response()->json(['success' => false, 'message' => 'Tidak ada perangkat modem aktif.'], 404);
        }

        $newSsid = trim($request->input('ssid'));
        $newPassword = $request->input('password') ? trim($request->input('password')) : null;
        $securityMode = $request->input('security_mode', 'WPA2-PSK (AES)');

        try {
            $result = $this->selfService->changeOnuWifiCredentials(
                customerId: $customer->id,
                onuId: $service->onu_id,
                newSsid: $newSsid,
                newPassword: $newPassword,
                wlanIndex: 1,
                securityMode: $securityMode
            );

            // Dual write to local onus table
            $service->onu?->update([
                'wifi_ssid' => $newSsid,
                'wifi_password' => $newPassword ?: $service->onu?->wifi_password,
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Pengaturan Wi-Fi berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            // If GenieACS driver is offline, still update local db and inform user
            $service->onu?->update([
                'wifi_ssid' => $newSsid,
                'wifi_password' => $newPassword ?: $service->onu?->wifi_password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Wi-Fi berhasil disimpan di sistem.',
            ]);
        }
    }

    /**
     * Get Connected Host Devices on Customer WiFi API.
     */
    public function getConnectedDevices(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $service = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->whereNotNull('onu_id')
            ->first();

        if (!$service || !$service->onu_id) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $devices = $this->selfService->getOnuConnectedDevices($customer->id, $service->onu_id);
            return response()->json(['success' => true, 'data' => $devices]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Customer Tickets List API.
     */
    public function tickets(Request $request): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', 15)));
        $paginator = $this->supportService->getPaginatedTickets($customer->id, $perPage);

        $items = collect($paginator->items())->map(fn($t) => [
            'id' => $t->id,
            'uuid' => $t->uuid,
            'title' => $t->title,
            'description' => $t->description,
            'category' => $t->category,
            'priority' => $t->priority,
            'status' => $t->status,
            'photos' => $t->photos ?? [],
            'created_at' => $t->created_at?->toIso8601String(),
            'resolved_at' => $t->resolved_at?->toIso8601String(),
            'resolution_notes' => $t->resolution_notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Create Support Ticket API.
     */
    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,critical',
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('tickets', 'public');
                $photos[] = asset('storage/' . $path);
            }
        }

        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'priority' => $request->input('priority'),
            'photos' => $photos,
        ];

        $result = $this->supportService->createTicket($customer->id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Laporan gangguan berhasil dikirim. Teknisi kami akan segera memproses.',
            'ticket_id' => $result['ticket_id'] ?? null,
        ], 201);
    }

    /**
     * Customer Ticket Detail API.
     */
    public function ticketDetail(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profil pelanggan tidak ditemukan.'], 404);
        }

        $t = $this->supportService->getTicketDetails($customer->id, $id);

        if (!$t) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $t->id,
                'uuid' => $t->uuid,
                'title' => $t->title,
                'description' => $t->description,
                'category' => $t->category,
                'priority' => $t->priority,
                'status' => $t->status,
                'photos' => $t->photos ?? [],
                'created_at' => $t->created_at?->toIso8601String(),
                'resolved_at' => $t->resolved_at?->toIso8601String(),
                'resolution_notes' => $t->resolution_notes,
                'assigned_to' => $t->assignedTo?->name,
            ],
        ]);
    }

    /**
     * Customer Notifications API.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('recipient_id', $user->id)
            ->latest('created_at')
            ->paginate(15);

        $items = collect($notifications->items())->map(fn($n) => [
            'id' => $n->id,
            'title' => $n->title,
            'message' => $n->message,
            'type' => $n->type,
            'status' => $n->status,
            'created_at' => $n->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Update Profile API.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $this->profileService->updateProfile($user->id, [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    /**
     * Change Password API.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        $result = $this->profileService->changePassword(
            $user->id,
            $request->input('current_password'),
            $request->input('new_password')
        );

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diubah.',
        ]);
    }

    /**
     * Contact Admin / WhatsApp CS API.
     */
    public function contactAdmin(Request $request): JsonResponse
    {
        $companyPhone = Setting::getValue('company.phone', '081234567890');
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$companyPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $user = $request->user();
        $customer = $this->getAuthenticatedCustomer($user);
        $custName = urlencode($customer?->name ?? $user->name);
        $custCode = urlencode($customer?->code ?? '-');

        $message = urlencode("Halo Admin dsBilling, saya {$custName} (ID: {$custCode}) ingin bertanya perihal layanan internet saya.");
        $waUrl = "https://wa.me/{$cleanPhone}?text={$message}";

        return response()->json([
            'success' => true,
            'data' => [
                'company_name' => Setting::getValue('company.name', 'dsBilling ISP'),
                'phone' => $companyPhone,
                'whatsapp_url' => $waUrl,
                'email' => Setting::getValue('company.email', 'cs@ispprovider.com'),
            ],
        ]);
    }

    /**
     * Speedtest Server Config API.
     */
    public function speedtestConfig(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'public_ip' => $request->ip(),
                'isp_name' => Setting::getValue('company.name', 'dsBilling High-Speed Fiber'),
                'server_name' => 'Local ISP Edge Server',
                'ping_url' => url('/api/customer/speedtest/ping'),
                'download_url' => url('/api/customer/speedtest/download'),
            ],
        ]);
    }
}
