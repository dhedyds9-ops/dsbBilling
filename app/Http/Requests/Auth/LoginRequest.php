<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identity = trim((string) $this->string('login'));
        $password = (string) $this->string('password');
        $remember = $this->boolean('remember');
        $isCustomerLogin = $this->input('login_type') === 'customer';

        $user = $this->resolveIdentity($identity);

        if (!$user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if ($isCustomerLogin) {
            if (!$user->hasRole('customer')) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'login' => trans('auth.failed'),
                ]);
            }
        }

        // Profil CRM Customer akan dicek & di-link otomatis (auto-heal) di Dashboard.php

        // ALL users must be checked for password!
        $passwordValid = \Illuminate\Support\Facades\Hash::check($password, $user->password);

        // Fallback pintar untuk akun customer (Portal Pelanggan)
        if (!$passwordValid && $user->hasRole('customer')) {
            $customer = $user->customer ?: \App\Models\CRM\Customer::where('user_id', $user->id)->first();
            
            // 1. Cek apakah cocok dengan password layanan (PPPoE / Hotspot)
            if ($customer) {
                $service = \App\Models\Customer\CustomerService::where('customer_id', $customer->id)->first();
                if ($service && !empty($service->password) && $service->password === $password) {
                    $user->update(['password' => \Illuminate\Support\Facades\Hash::make($password)]);
                    $passwordValid = true;
                }
            }

            // 2. Cek apakah mencoba login dengan password default '123456'
            if (!$passwordValid && $password === '123456') {
                if (empty($user->password_changed_at)) {
                    $user->update(['password' => \Illuminate\Support\Facades\Hash::make('123456')]);
                    $passwordValid = true;
                }
            }
        }

        if (!$passwordValid) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if (!$user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Akun Anda belum aktif atau telah dinonaktifkan.',
            ]);
        }

        Auth::login($user, $remember);

        RateLimiter::clear($this->throttleKey());
    }

    private function resolveIdentity(string $identity): ?\App\Models\User
    {
        $lower = mb_strtolower(trim($identity));
        
        $customerByCode = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
            // 1. Coba cari di tabel customers dulu berdasarkan code (ID Pelanggan)
            $customerByCode = \App\Models\CRM\Customer::withoutGlobalScopes()->whereRaw('LOWER(code) = ?', [$lower])->first();
            
            // 1b. Jika belum ketemu dan formatnya angka (misal 2026101607 atau 7), coba cari via ID atau sequence
            if (!$customerByCode && is_numeric($identity)) {
                if (strlen($identity) >= 9) {
                    $idSuffix = (int) substr($identity, 8);
                    if ($idSuffix > 0) {
                        $customerByCode = \App\Models\CRM\Customer::withoutGlobalScopes()->find($idSuffix);
                    }
                } else {
                    $customerByCode = \App\Models\CRM\Customer::withoutGlobalScopes()->find((int)$identity);
                }
            }
        }

        if ($customerByCode) {
            $u = null;
            if ($customerByCode->user_id) {
                $u = \App\Models\User::find($customerByCode->user_id);
            }
            
            if (!$u) {
                // Auto-heal: Cari user berdasarkan nomor HP/WA, email, atau username
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customerByCode->phone);
                $phone0 = $cleanPhone;
                $phone62 = $cleanPhone;
                if (str_starts_with($cleanPhone, '62')) $phone0 = '0' . substr($cleanPhone, 2);
                elseif (str_starts_with($cleanPhone, '0')) $phone62 = '62' . substr($cleanPhone, 1);
                
                $u = \App\Models\User::where(function($q) use ($phone0, $phone62, $customerByCode) {
                    if ($phone0 && $phone62) {
                        $q->whereRaw('LOWER(whatsapp) IN (?, ?)', [$phone0, $phone62]);
                    }
                    if ($customerByCode->email) {
                        $q->orWhereRaw('LOWER(email) = ?', [mb_strtolower($customerByCode->email)]);
                    }
                    if ($customerByCode->code) { $q->orWhereRaw('LOWER(username) = ?', [mb_strtolower($customerByCode->code)]); }
                })->first();
            }

            // Jika user masih belum ada sama sekali, buatkan akun User untuk customer ini sekarang juga!
            if (!$u) {
                $service = \Illuminate\Support\Facades\Schema::hasTable('customer_services')
                    ? \App\Models\Customer\CustomerService::withoutGlobalScopes()->where('customer_id', $customerByCode->id)->first()
                    : null;
                $defaultPass = !empty($service->password) ? $service->password : '123456';
                
                $u = \App\Models\User::create([
                    'name' => $customerByCode->name,
                    'email' => $customerByCode->email,
                    'whatsapp' => $customerByCode->phone,
                    'customer_code' => $customerByCode->code ?: $identity,
                    'username' => $customerByCode->code ?: $identity,
                    'pppoe_username' => $service?->username,
                    'password' => \Illuminate\Support\Facades\Hash::make($defaultPass),
                    'is_active' => true,
                ]);
            }

            // Pastikan role customer terpasang
            if (!$u->hasRole('customer')) {
                $role = \App\Models\Role::where('name', 'customer')->first();
                if ($role) {
                    $u->roles()->syncWithoutDetaching([$role->id]);
                }
            }

            if ($customerByCode->user_id !== $u->id) {
                $customerByCode->update(['user_id' => $u->id]);
            }

            return $u;
        }

        // 2. Coba cari di tabel customer_services (PPPoE/Hotspot Username)
        $customerService = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('customer_services')) {
            $customerService = \App\Models\Customer\CustomerService::withoutGlobalScopes()->with('customer')
                ->whereRaw('LOWER(username) = ?', [$lower])
                ->first();
        }
            
        if ($customerService && $customerService->customer) {
            $customer = $customerService->customer;
            $u = $customer->user_id ? \App\Models\User::find($customer->user_id) : null;
            if (!$u) {
                $u = \App\Models\User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'whatsapp' => $customer->phone,
                    'customer_code' => $customer->code,
                    'username' => $customer->code ?: $customerService->username,
                    'pppoe_username' => $customerService->username,
                    'password' => \Illuminate\Support\Facades\Hash::make(!empty($customerService->password) ? $customerService->password : '123456'),
                    'is_active' => true,
                ]);
            }
            if (!$u->hasRole('customer')) {
                $role = \App\Models\Role::where('name', 'customer')->first();
                if ($role) $u->roles()->syncWithoutDetaching([$role->id]);
            }
            if ($customer->user_id !== $u->id) {
                $customer->update(['user_id' => $u->id]);
            }
            return $u;
        }

        // 3. Fallback ke tabel users utama (untuk email, whatsapp, username bawaan)
        $query = \App\Models\User::query();
        $cleanPhone = preg_replace('/[^0-9]/', '', $identity);
        $phone0 = null;
        $phone62 = null;
        
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 9) {
            $phone0 = $cleanPhone;
            $phone62 = $cleanPhone;
            if (str_starts_with($cleanPhone, '62')) {
                $phone0 = '0' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '0')) {
                $phone62 = '62' . substr($cleanPhone, 1);
            }
        }

        return $query->where(function ($q) use ($lower, $phone0, $phone62) {
            $q->whereRaw('LOWER(email) = ?', [$lower])
                ->orWhereRaw('LOWER(username) = ?', [$lower])
                ->orWhereRaw('LOWER(customer_code) = ?', [$lower])
                ->orWhereRaw('LOWER(pppoe_username) = ?', [$lower]);
                
            if ($phone0 && $phone62) {
                $q->orWhereRaw('LOWER(whatsapp) IN (?, ?)', [$phone0, $phone62])
                  ->orWhereHas('customer', function($subQ) use ($phone0, $phone62) {
                      $subQ->whereRaw('LOWER(phone) IN (?, ?)', [$phone0, $phone62]);
                  });
            }
        })->first();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}

