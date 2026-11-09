<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email atau kata sandi tidak valid.',
            ], 401);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'role' => $role,
                'job_title' => $user->job_title ?? $role,
                'branch_id' => $user->branch_id,
                'reseller_id' => $user->getEffectiveResellerId(),
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

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'role' => $role,
                'job_title' => $user->job_title ?? $role,
                'branch_id' => $user->branch_id,
                'reseller_id' => $user->getEffectiveResellerId(),
                'company_id' => $user->company_id ?? 1,
                'permissions' => $permissions,
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
     * Determine primary standardized operational role.
     */
    private function determinePrimaryRole(User $user): string
    {
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return 'ADMINISTRATOR';
        }
        if ($user->hasRole('manager') || strtoupper($user->job_function ?? '') === 'MANAGER') {
            return 'MANAGER';
        }
        if ($user->hasRole('noc') || strtoupper($user->job_function ?? '') === 'NOC' || str_contains(strtoupper($user->job_title ?? ''), 'NOC')) {
            return 'NOC';
        }
        if ($user->isWarehouseAdmin()) {
            return 'WAREHOUSE';
        }
        if ($user->isTechnician()) {
            return 'TECHNICIAN';
        }
        if ($user->hasRole(['reseller', 'Reseller']) || strtoupper($user->job_function ?? '') === 'RESELLER' || !empty($user->reseller_id)) {
            return 'RESELLER';
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
                break;
            case 'RESELLER':
                $perms = array_merge($perms, [
                    'workforce.view', 'workforce.execute',
                    'inventory.view', 'inventory.custody', 'inventory.request',
                    'acs.view', 'acs.manage', 'acs.reboot', 'acs.wifi',
                    'customer.view', 'customer.create', 'customer.manage',
                    'support.view', 'support.create',
                    'gis.view'
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
}
