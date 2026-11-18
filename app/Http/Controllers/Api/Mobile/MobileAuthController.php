<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MobileAuthController extends Controller
{
    /**
     * Staff Login for dsBilling Staff Android App.
     * Authenticates internal staff and rejects customers / resellers.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        $login = trim($request->input('email'));
        $ip = $request->ip();
        $throttleKey = 'mobile_auth_lock:' . sha1($ip . '|' . strtolower($login));

        // Rate limiting: maximum 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning("Mobile Staff Login: Rate limit exceeded for {$login} from IP {$ip}.");
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login gagal. Demi keamanan, silakan coba lagi dalam {$seconds} detik.",
                'retry_after' => $seconds,
            ], 429);
        }

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Username/Email atau kata sandi tidak valid.',
            ], 401);
        }

        // Clear rate limiter upon successful authentication
        RateLimiter::clear($throttleKey);

        // Account status check
        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun staf dinonaktifkan. Silakan hubungi Administrator.',
            ], 403);
        }

        // Reject pure customer (end-user) role
        $isCustomer = $user->hasRole('customer') || strtoupper($user->job_function ?? '') === 'CUSTOMER';

        if ($isCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pelanggan dapat menggunakan portal dsBilling Connect.',
            ], 403);
        }

        // Determine primary role
        $role = $this->determinePrimaryRole($user);

        // Collect permissions
        $permissions = $this->collectPermissions($user, $role);

        // Generate Sanctum token
        $tokenName = 'dsbilling-staff-' . ($request->input('device_info') ?? 'android');
        $tokenResult = $user->createToken($tokenName);
        $plainToken = $tokenResult->plainTextToken;

        // Audit log
        Log::info("Mobile Staff Login: User #{$user->id} ({$user->name}) logged in as {$role}.");

        $userWilayah = $user->getEffectiveWilayah();
        $branchName = $user->branch?->name ?: ($user->parentReseller?->branch?->name ?: ("Cabang " . ($user->branch_id ?? 1)));
        if (empty($userWilayah) && !empty($branchName)) {
            $userWilayah = $branchName;
        }

        $brandName = $this->resolveBrandName($request);
        $brandLogoUrl = $this->resolveBrandLogoUrl($request);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'server_url' => $request->getSchemeAndHttpHost(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'role' => $role,
                'job_function' => $user->job_function,
                'job_title' => $user->job_title ?? $role,
                'branch_id' => $user->branch_id,
                'reseller_id' => $user->getEffectiveResellerId(),
                'wilayah' => $userWilayah ?: $branchName,
                'branch_name' => $branchName,
                'brand_name' => $brandName,
                'logo_url' => $brandLogoUrl,
                'company_id' => $user->company_id ?? 1,
                'permissions' => $permissions,
            ],
            'acs_config' => [
                'url' => config('services.genieacs.url', env('GENIEACS_URL', 'http://127.0.0.1:7557')),
                'username' => config('services.genieacs.username', env('GENIEACS_USERNAME', '')),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get current authenticated staff profile & permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $role = $this->determinePrimaryRole($user);
        $permissions = $this->collectPermissions($user, $role);
        $hasActiveAttendance = \App\Services\Workforce\AttendanceAuthorizationService::hasActiveAttendance($user);
        $activeAttendance = \App\Services\Workforce\AttendanceAuthorizationService::getActiveAttendance($user);
        $canOperate = \App\Services\Workforce\AttendanceAuthorizationService::canPerformOperations($user);

        $userWilayah = $user->getEffectiveWilayah();
        $branchName = $user->branch?->name ?: ($user->parentReseller?->branch?->name ?: ("Cabang " . ($user->branch_id ?? 1)));
        if (empty($userWilayah) && !empty($branchName)) {
            $userWilayah = $branchName;
        }

        $brandName = $this->resolveBrandName($request);
        $brandLogoUrl = $this->resolveBrandLogoUrl($request);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'role' => $role,
                'job_function' => $user->job_function,
                'job_title' => $user->job_title ?? $role,
                'branch_id' => $user->branch_id,
                'reseller_id' => $user->getEffectiveResellerId(),
                'wilayah' => $userWilayah ?: $branchName,
                'branch_name' => $branchName,
                'brand_name' => $brandName,
                'logo_url' => $brandLogoUrl,
                'company_id' => $user->company_id ?? 1,
                'permissions' => $permissions,
                'has_active_attendance' => $hasActiveAttendance,
                'can_operate' => $canOperate,
                'active_attendance_id' => $activeAttendance?->id,
                'active_shift' => $activeAttendance ? [
                    'id' => $activeAttendance->id,
                    'checked_in_at' => $activeAttendance->checked_in_at?->toIso8601String(),
                    'checked_in_time' => $activeAttendance->checked_in_at?->format('H:i'),
                    'date' => $activeAttendance->date?->toDateString(),
                ] : null,
            ],
        ]);
    }

    /**
     * Staff Logout & Token Revocation.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri (Logout).',
        ]);
    }

    /**
     * Generate a short-lived single-use SSO token for 1-click web login.
     * POST /api/mobile/auth/sso-token
     */
    public function createSsoToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun staf dinonaktifkan.',
            ], 403);
        }

        // Generate high-entropy single-use token
        $ssoToken = \Illuminate\Support\Str::random(64);
        $cacheKey = 'mobile_sso_token:' . $ssoToken;

        // Determine destination target path based on user role
        $targetPath = '/dashboard';
        if ($user->hasRole('reseller') || (method_exists($user, 'isReseller') && $user->isReseller())) {
            $targetPath = '/reseller-portal/dashboard';
        } elseif (strtoupper($user->job_function ?? '') === 'TECHNICIAN' || $user->hasRole(['technician', 'teknisi']) || (method_exists($user, 'isTechnician') && $user->isTechnician())) {
            $targetPath = '/technician-portal/dashboard';
        } elseif ($user->hasRole('customer')) {
            $targetPath = '/customer-portal/dashboard';
        } elseif ($user->hasRole('noc') || strtoupper($user->job_function ?? '') === 'NOC') {
            $targetPath = '/noc/overview';
        }

        // Store in cache for 60 seconds (one-time use)
        Cache::put($cacheKey, [
            'user_id' => $user->id,
            'target_path' => $targetPath,
            'ip' => $request->ip(),
            'created_at' => now()->timestamp,
        ], 60);

        // Build absolute redirect URL based on request scheme and host
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $redirectUrl = $baseUrl . '/sso/login?token=' . $ssoToken;

        return response()->json([
            'success' => true,
            'message' => 'Token SSO 1-klik berhasil dibuat.',
            'sso_token' => $ssoToken,
            'redirect_url' => $redirectUrl,
            'target_path' => $targetPath,
        ]);
    }

    /**
     * Change user password for authenticated mobile staff.
     * POST /api/mobile/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
            ], 422);
        }

        // Ensure new password is not identical to current
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }

    /**
     * Update user profile (Name, Username, Phone, Email) for authenticated mobile staff.
     * POST /api/mobile/auth/update-profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'username.unique' => 'Username tersebut sudah digunakan oleh akun lain.',
            'email.unique' => 'Alamat email tersebut sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('name')) {
            $user->name = trim((string)$request->name);
        }

        if ($request->filled('username')) {
            $user->username = trim((string)$request->username);
        }

        if ($request->filled('email')) {
            $user->email = trim((string)$request->email);
        }

        $phoneInput = $request->input('phone') ?? $request->input('whatsapp');
        if ($phoneInput !== null) {
            $cleanedPhone = trim((string)$phoneInput);
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                $user->phone = $cleanedPhone;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'whatsapp')) {
                $user->whatsapp = $cleanedPhone;
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil akun berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->whatsapp ?? ($user->phone ?? ''),
                'whatsapp' => $user->whatsapp ?? '',
            ],
        ]);
    }

    /**
     * Authenticate via Single-Use Secure QR Code.
     */
    public function authenticateQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        $token = trim($request->input('qr_token'));
        $cacheKey = 'qr_auth_ticket:' . sha1($token);
        $ticketData = Cache::get($cacheKey);

        if (!$ticketData || !is_array($ticketData)) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau telah kedaluwarsa. Silakan scan ulang QR terbaru.',
            ], 401);
        }

        // Invalidate immediately (single-use)
        Cache::forget($cacheKey);

        $userId = $ticketData['user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;

        if (!$user || (isset($user->is_active) && !$user->is_active)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun staf tidak ditemukan atau dinonaktifkan.',
            ], 403);
        }

        $role = $this->determinePrimaryRole($user);
        $permissions = $this->collectPermissions($user, $role);

        $tokenName = 'dsbilling-staff-qr-' . ($request->input('device_info') ?? 'android');
        $tokenResult = $user->createToken($tokenName);
        $plainToken = $tokenResult->plainTextToken;

        Log::info("Mobile QR Staff Login: User #{$user->id} ({$user->name}) authenticated via QR.");

        $userWilayah = $user->getEffectiveWilayah();
        $branchName = $user->branch?->name ?: ($user->parentReseller?->branch?->name ?: ("Cabang " . ($user->branch_id ?? 1)));
        if (empty($userWilayah) && !empty($branchName)) {
            $userWilayah = $branchName;
        }

        $brandName = $this->resolveBrandName($request);
        $brandLogoUrl = $this->resolveBrandLogoUrl($request);

        return response()->json([
            'success' => true,
            'message' => 'Login via QR Code berhasil.',
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'role' => $role,
                'job_function' => $user->job_function,
                'job_title' => $user->job_title ?? $role,
                'branch_id' => $user->branch_id,
                'reseller_id' => $user->getEffectiveResellerId(),
                'wilayah' => $userWilayah ?: $branchName,
                'branch_name' => $branchName,
                'brand_name' => $brandName,
                'logo_url' => $brandLogoUrl,
                'company_id' => $user->company_id ?? 1,
                'permissions' => $permissions,
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Request Password Reset Information (Safe, does not leak account existence).
     */
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        $login = trim($request->input('email'));
        $user = User::where('email', $login)->orWhere('username', $login)->first();

        if ($user) {
            Log::info("Staff Password Reset Requested: User #{$user->id} ({$user->name}) requested reset.");
        }

        return response()->json([
            'success' => true,
            'message' => 'Permintaan reset telah dikirimkan ke Administrator cabang. Silakan hubungi admin atau periksa instruksi lebih lanjut.',
        ]);
    }

    /**
     * Determine primary standardized operational role.
     */
    private function determinePrimaryRole(User $user): string
    {
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return 'ADMINISTRATOR';
        }
        if ($user->isReseller() || $user->hasRole(['reseller', 'Reseller'])) {
            return 'RESELLER';
        }
        if ($user->isWarehouseAdmin()) {
            return 'WAREHOUSE';
        }
        // Manager with job_function TECHNICIAN operates as field technician
        if (strtoupper($user->job_function ?? '') === 'TECHNICIAN' || strtoupper($user->job_function ?? '') === 'TEKNISI') {
            return 'TECHNICIAN';
        }
        if ($user->hasRole('manager') || strtoupper($user->job_function ?? '') === 'MANAGER') {
            return 'MANAGER';
        }
        if ($user->isTechnician()) {
            return 'TECHNICIAN';
        }
        if ($user->hasRole('noc') || strtoupper($user->job_function ?? '') === 'NOC' || str_contains(strtoupper($user->job_title ?? ''), 'NOC')) {
            return 'NOC';
        }
        if ($user->hasRole(['customer_service', 'cs']) || strtoupper($user->job_function ?? '') === 'CUSTOMER_SERVICE') {
            return 'CS';
        }
        if ($user->hasRole(['kasir', 'cashier']) || strtoupper($user->job_function ?? '') === 'KASIR') {
            return 'CASHIER';
        }

        return 'STAFF';
    }

    /**
     * Collect all authorized permissions for the user.
     */
    private function collectPermissions(User $user, string $primaryRole): array
    {
        $perms = [];

        // Add explicit DB permissions if available
        try {
            if (method_exists($user, 'getAllPermissions')) {
                $perms = $user->getAllPermissions()->pluck('name')->toArray();
            } elseif (method_exists($user, 'permissions')) {
                $perms = $user->permissions()->pluck('name')->toArray();
            }
        } catch (\Throwable) {}

        // Add role-based capability grants
        switch ($primaryRole) {
            case 'ADMINISTRATOR':
            case 'MANAGER':
                $perms = array_merge($perms, [
                    'workforce.view', 'workforce.execute', 'workforce.approve',
                    'inventory.view', 'inventory.manage', 'inventory.custody',
                    'acs.view', 'acs.manage', 'acs.reboot', 'acs.wifi', 'acs.wan',
                    'support.view', 'billing.view'
                ]);
                break;
            case 'NOC':
                $perms = array_merge($perms, [
                    'acs.view', 'acs.manage', 'acs.reboot', 'acs.wifi', 'acs.wan',
                    'workforce.view', 'support.view'
                ]);
                break;
            case 'TECHNICIAN':
                $perms = array_merge($perms, [
                    'workforce.view', 'workforce.execute',
                    'inventory.view', 'inventory.custody',
                    'acs.view', 'acs.manage', 'acs.reboot', 'acs.wifi'
                ]);
                if ($user->hasRole('manager') || $user->isSuperAdmin()) {
                    $perms = array_merge($perms, ['workforce.approve', 'inventory.manage', 'billing.view', 'finance.view']);
                }
                break;
            case 'RESELLER':
                $perms = array_merge($perms, [
                    'workforce.view', 'workforce.execute',
                    'inventory.view', 'inventory.custody', 'inventory.request',
                    'acs.view', 'acs.manage', 'acs.reboot', 'acs.wifi', 'acs.wan',
                    'onu.view', 'onu.diagnose', 'onu.reboot', 'onu.configure.wifi', 'onu.configure.wan', 'onu.configure.admin',
                    'onu.firmware.update', 'onu.firmware.downgrade', 'onu.factory_reset', 'onu.task.retry', 'onu.task.delete',
                    'customer.view', 'customer.create', 'customer.manage',
                    'support.view', 'support.create',
                    'gis.view', 'technician.portal'
                ]);
                break;
            case 'WAREHOUSE':
                $perms = array_merge($perms, [
                    'inventory.view', 'inventory.manage', 'inventory.custody'
                ]);
                break;
            case 'CS':
                $perms = array_merge($perms, [
                    'support.view', 'workforce.view'
                ]);
                break;
            case 'CASHIER':
                $perms = array_merge($perms, [
                    'billing.view', 'billing.payment'
                ]);
                break;
        }

        return array_values(array_unique($perms));
    }

    private function resolveBrandName(Request $request): string
    {
        $settingBrand = \App\Models\Setting::getValue('app.brand_name') 
            ?: \App\Models\Setting::getValue('company.name') 
            ?: \App\Models\Setting::getValue('company.partner_name');

        if (!empty($settingBrand)) {
            return (string) $settingBrand;
        }

        $host = $request->header('X-Forwarded-Host') ?: $request->getHost();
        $parts = explode('.', $host);
        if (!empty($parts[0]) && !in_array($parts[0], ['localhost', '127', 'www', 'api', 'central'])) {
            return ucfirst($parts[0]);
        }

        return config('app.name', 'dsBilling');
    }

    private function resolveBrandLogoUrl(Request $request): ?string
    {
        $logo = \App\Models\Setting::getValue('company.logo_url') 
            ?: \App\Models\Setting::getValue('company.partner_logo_url');

        if (!empty($logo)) {
            $logoStr = (string) $logo;
            if (str_starts_with($logoStr, 'http://') || str_starts_with($logoStr, 'https://')) {
                return $logoStr;
            }
            return url(ltrim($logoStr, '/'));
        }

        if (file_exists(public_path('images/dsbilling_header_logo.jpg'))) {
            return url('images/dsbilling_header_logo.jpg');
        }

        return null;
    }
}
