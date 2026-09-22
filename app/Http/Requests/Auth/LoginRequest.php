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

        // ALL users must be checked for password!
        if (!\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
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
        
        // 1. Coba cari di tabel customers (members) dulu berdasarkan code (ID Pelanggan)
        $customerByCode = \App\Models\CRM\Customer::whereRaw('LOWER(code) = ?', [$lower])->first();
        if ($customerByCode) {
            if ($customerByCode->user_id) {
                $u = \App\Models\User::find($customerByCode->user_id);
                if ($u) return $u;
            } else {
                // Auto-heal agressive (Phone 08 vs 62, Name, etc)
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
                    $q->orWhereRaw('LOWER(name) = ?', [mb_strtolower($customerByCode->name)]);
                })->whereHas('roles', function($q) {
                    $q->where('name', 'customer');
                })->first();
                if ($u) {
                    $customerByCode->update(['user_id' => $u->id]);
                    return $u;
                }
            }
        }

        // 2. Coba cari di tabel customer_services (PPPoE/Hotspot Username)
        $customerService = \App\Models\Customer\CustomerService::with('customer')
            ->whereRaw('LOWER(username) = ?', [$lower])
            ->first();
            
        if ($customerService && $customerService->customer) {
            $customer = $customerService->customer;
            if ($customer->user_id) {
                $u = \App\Models\User::find($customer->user_id);
                if ($u) return $u;
            } else {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customer->phone);
                $phone0 = $cleanPhone;
                $phone62 = $cleanPhone;
                if (str_starts_with($cleanPhone, '62')) $phone0 = '0' . substr($cleanPhone, 2);
                elseif (str_starts_with($cleanPhone, '0')) $phone62 = '62' . substr($cleanPhone, 1);
                
                $u = \App\Models\User::where(function($q) use ($phone0, $phone62, $customer) {
                    if ($phone0 && $phone62) {
                        $q->whereRaw('LOWER(whatsapp) IN (?, ?)', [$phone0, $phone62]);
                    }
                    if ($customer->email) {
                        $q->orWhereRaw('LOWER(email) = ?', [mb_strtolower($customer->email)]);
                    }
                    $q->orWhereRaw('LOWER(name) = ?', [mb_strtolower($customer->name)]);
                })->whereHas('roles', function($q) {
                    $q->where('name', 'customer');
                })->first();
                if ($u) {
                    $customer->update(['user_id' => $u->id]);
                    return $u;
                }
            }
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