<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ForgotPasswordOtp extends Component
{
    /**
     * Langkah:
     * 1 = Masukkan No. WhatsApp / Username / Email
     * 2 = Masukkan 6 Digit Kode OTP & Password Baru
     * 3 = Berhasil / Sukses
     */
    public int $step = 1;

    public string $identifier = '';
    public ?int $userId = null;
    public string $userName = '';
    public string $maskedPhone = '';
    public string $targetPhone = '';

    public string $otpCode = '';
    public string $password = '';
    public string $password_confirmation = '';

    public int $resendCountdown = 0;

    protected $queryString = [
        'identifier' => ['except' => ''],
    ];

    public function mount()
    {
        // Jika ada query string identifier (misal dari form login)
        if (!empty($this->identifier)) {
            $this->identifier = trim($this->identifier);
        }
    }

    /**
     * Langkah 1: Cari akun user dan kirim OTP via WhatsApp
     */
    public function sendOtp()
    {
        $this->validate([
            'identifier' => 'required|string|min:3',
        ], [
            'identifier.required' => 'Silakan masukkan No. WhatsApp, Username, atau Email akun Anda.',
            'identifier.min' => 'Identitas akun minimal 3 karakter.',
        ]);

        $input = trim($this->identifier);

        // Siapkan varian format nomor HP (08xx, 628xx, 8xx)
        $cleanDigits = preg_replace('/[^0-9]/', '', $input);
        $phoneVariants = [];
        if (strlen($cleanDigits) >= 8) {
            if (str_starts_with($cleanDigits, '08')) {
                $phoneVariants[] = $cleanDigits;
                $phoneVariants[] = '628' . substr($cleanDigits, 2);
            } elseif (str_starts_with($cleanDigits, '628')) {
                $phoneVariants[] = $cleanDigits;
                $phoneVariants[] = '08' . substr($cleanDigits, 3);
            } elseif (str_starts_with($cleanDigits, '8')) {
                $phoneVariants[] = '0' . $cleanDigits;
                $phoneVariants[] = '62' . $cleanDigits;
            } else {
                $phoneVariants[] = $cleanDigits;
            }
        }

        // Cari user di database
        $user = User::query()
            ->where(function ($q) use ($input, $phoneVariants) {
                $q->where('email', $input)
                  ->orWhere('username', $input);

                if (!empty($phoneVariants)) {
                    $q->orWhereIn('whatsapp', $phoneVariants);
                    $q->orWhereHas('employee', function ($eq) use ($phoneVariants) {
                        $eq->whereIn('phone', $phoneVariants);
                    });
                }
            })
            ->first();

        if (!$user) {
            $this->addError('identifier', 'Akun dengan Email, Username, atau No. WhatsApp tersebut tidak ditemukan.');
            return;
        }

        // Cari nomor WhatsApp valid dari profil user atau data pegawai
        $rawPhone = $user->whatsapp ?: ($user->employee?->phone ?: ($user->phone ?? null));

        if (empty($rawPhone)) {
            $this->addError('identifier', 'Akun ditemukan, namun belum ada nomor WhatsApp yang terdaftar pada profil Anda. Silakan hubungi Administrator untuk mengatur password Anda.');
            return;
        }

        // Normalisasi format nomor WhatsApp ke 628xxx
        $phone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($phone, '08')) {
            $phone = '628' . substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '628' . substr($phone, 1);
        }

        if (strlen($phone) < 10 || strlen($phone) > 16 || !str_starts_with($phone, '628')) {
            $this->addError('identifier', "Format nomor WhatsApp user ('{$rawPhone}') tidak valid. Harap hubungi Administrator.");
            return;
        }

        // Cek cooldown pengiriman OTP (maks 1x per 60 detik)
        $cooldownKey = "otp:cooldown:{$user->id}";
        if (Cache::has($cooldownKey)) {
            $expiresAt = (int) Cache::get($cooldownKey);
            $remaining = max(1, $expiresAt - time());
            if ($remaining > 0) {
                $this->addError('identifier', "Mohon tunggu {$remaining} detik sebelum meminta kode OTP baru.");
                return;
            }
        }

        // Generate 6 Digit Angka OTP Acak
        $otp = (string) random_int(100000, 999999);

        // Simpan data OTP di Cache selama 5 Menit (300 detik)
        Cache::put("otp:reset_password:{$user->id}", [
            'code' => $otp,
            'attempts' => 0,
            'phone' => $phone,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        // Set cooldown 60 detik
        Cache::put($cooldownKey, now()->addSeconds(60)->timestamp, 60);

        // Susun teks pesan WhatsApp
        $companyName = Setting::getValue('company.name', config('app.name', 'dsBilling'));
        $msgText = "🔐 *Kode Keamanan {$companyName}*\n\n"
            . "Halo *{$user->name}*,\n"
            . "Kode OTP untuk verifikasi reset kata sandi akun Anda adalah:\n\n"
            . "👉 *{$otp}* 👈\n\n"
            . "⏱️ Kode ini berlaku selama *5 menit*.\n"
            . "⚠️ *PENTING:* JANGAN bagikan kode ini kepada siapa pun demi keamanan akun Anda.\n\n"
            . "Jika Anda tidak meminta perubahan kata sandi ini, abaikan pesan ini.";

        // Kirim via WhatsApp Gateway
        $sent = $this->sendWhatsAppMessage($phone, $msgText);

        if (!$sent) {
            Cache::forget("otp:reset_password:{$user->id}");
            Cache::forget($cooldownKey);
            $this->addError('identifier', 'Gagal mengirim pesan WhatsApp. Pastikan perangkat WhatsApp Gateway aktif atau hubungi Administrator.');
            return;
        }

        // Siapkan state untuk Langkah 2
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->targetPhone = $phone;
        $this->maskedPhone = substr($phone, 0, 4) . '****' . substr($phone, -4);
        $this->resendCountdown = 60;
        $this->otpCode = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();

        $this->step = 2;
        session()->flash('otp_sent', "Kode OTP 6 digit telah dikirim ke WhatsApp Anda ({$this->maskedPhone}).");
    }

    /**
     * Kirim ulang kode OTP jika belum masuk
     */
    public function resendOtp()
    {
        if (!$this->userId) {
            $this->step = 1;
            return;
        }

        $cooldownKey = "otp:cooldown:{$this->userId}";
        if (Cache::has($cooldownKey)) {
            $expiresAt = (int) Cache::get($cooldownKey);
            $remaining = max(1, $expiresAt - time());
            if ($remaining > 0) {
                $this->addError('otpCode', "Harap tunggu {$remaining} detik sebelum meminta kirim ulang kode.");
                return;
            }
        }

        $user = User::find($this->userId);
        if (!$user) {
            $this->step = 1;
            return;
        }

        $phone = $this->targetPhone;
        $otp = (string) random_int(100000, 999999);

        Cache::put("otp:reset_password:{$user->id}", [
            'code' => $otp,
            'attempts' => 0,
            'phone' => $phone,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        Cache::put($cooldownKey, now()->addSeconds(60)->timestamp, 60);

        $companyName = Setting::getValue('company.name', config('app.name', 'dsBilling'));
        $msgText = "🔐 *Kode Keamanan {$companyName}*\n\n"
            . "Halo *{$user->name}*,\n"
            . "Kode OTP baru untuk verifikasi reset kata sandi Anda adalah:\n\n"
            . "👉 *{$otp}* 👈\n\n"
            . "⏱️ Kode berlaku selama *5 menit*.\n"
            . "⚠️ *PENTING:* JANGAN bagikan kode ini kepada siapa pun.\n\n"
            . "Jika Anda tidak meminta perubahan kata sandi, abaikan pesan ini.";

        $this->sendWhatsAppMessage($phone, $msgText);
        $this->resendCountdown = 60;
        $this->resetErrorBag();

        session()->flash('otp_sent', "Kode OTP baru telah berhasil dikirim ulang ke WhatsApp {$this->maskedPhone}.");
    }

    /**
     * Langkah 2: Verifikasi 6 Digit OTP dan Simpan Password Baru
     */
    public function verifyAndResetPassword()
    {
        $this->validate([
            'otpCode' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'otpCode.required' => 'Masukkan 6 digit kode OTP yang dikirim ke WhatsApp Anda.',
            'otpCode.digits' => 'Kode OTP harus tepat 6 digit angka.',
            'otpCode.numeric' => 'Kode OTP hanya berupa angka.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if (!$this->userId) {
            $this->step = 1;
            return;
        }

        $cacheKey = "otp:reset_password:{$this->userId}";
        $cacheData = Cache::get($cacheKey);

        if (!$cacheData) {
            $this->addError('otpCode', 'Kode OTP telah kadaluarsa atau tidak valid. Silakan klik "Kirim Ulang Kode".');
            return;
        }

        $attempts = (int)($cacheData['attempts'] ?? 0);
        if ($attempts >= 5) {
            Cache::forget($cacheKey);
            $this->addError('otpCode', 'Terlalu banyak percobaan salah. Kode OTP dibatalkan. Silakan minta kode baru.');
            return;
        }

        // Cek kecocokan OTP
        if (trim((string)$this->otpCode) !== (string)$cacheData['code']) {
            $cacheData['attempts'] = $attempts + 1;
            $remainingAttempts = max(0, 5 - $cacheData['attempts']);
            Cache::put($cacheKey, $cacheData, 300);

            $this->addError('otpCode', "Kode OTP tidak cocok! Periksa kembali WhatsApp Anda (Sisa kesempatan: {$remainingAttempts}x).");
            return;
        }

        // Sukses! Update password user
        $user = User::findOrFail($this->userId);
        $user->password = Hash::make($this->password);
        $user->password_changed_at = now();
        $user->save();

        // Hapus token OTP dari Cache
        Cache::forget($cacheKey);
        Cache::forget("otp:cooldown:{$this->userId}");

        // Langkah 3: Tampilkan layar sukses
        $this->step = 3;
    }

    /**
     * Helper untuk kembali ke Langkah 1 (ganti nomor/user)
     */
    public function backToStepOne()
    {
        $this->step = 1;
        $this->otpCode = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();
    }

    /**
     * Kirim pesan via WhatsApp (Unified SSOT Service & Fallback Gateway HTTP)
     */
    protected function sendWhatsAppMessage(string $phone, string $msgText): bool
    {
        // 1. Coba lewat WhatsAppNotificationService jika terdaftar
        try {
            if (class_exists(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)) {
                $service = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class);
                $waMsg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                    toPhone: $phone,
                    type: 'text',
                    text: $msgText,
                    category: 'otp',
                    priorityHigh: true,
                );
                if ($service->sendImmediate($waMsg)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppNotificationService sendImmediate failed in OTP: ' . $e->getMessage());
        }

        // 2. Fallback direct HTTP via Setting whatsapp.connection
        $conn = Setting::getValue('whatsapp.connection', []);
        $provider = $conn['provider'] ?? 'fonnte';
        $apiToken = trim((string)($conn['api_token'] ?? ''));
        $baseUrl = trim((string)($conn['base_url'] ?? ''));

        if (empty($apiToken)) {
            Log::error('WhatsApp OTP failed: API token is empty in settings.');
            return false;
        }

        try {
            if ($provider === 'fonnte') {
                $url = $baseUrl ?: 'https://api.fonnte.com/send';
                $resp = Http::timeout(15)
                    ->withHeaders(['Authorization' => $apiToken])
                    ->asForm()
                    ->post($url, [
                        'target' => $phone,
                        'message' => $msgText,
                    ]);

                if ($resp->successful()) {
                    $json = $resp->json();
                    return ($json['status'] ?? true) !== false;
                }
            } elseif ($provider === 'wablas') {
                $url = $baseUrl ?: 'https://jogja.wablas.com/api/send-message';
                $resp = Http::timeout(15)
                    ->withHeaders(['Wablas-Api-Key' => $apiToken])
                    ->asForm()
                    ->post($url, [
                        'phone' => $phone,
                        'message' => $msgText,
                    ]);

                return $resp->successful();
            } elseif ($provider === 'whacenter') {
                $url = $baseUrl ?: 'https://app.whacenter.com/api/send';
                $resp = Http::timeout(15)
                    ->asForm()
                    ->post($url, [
                        'device_id' => $conn['device_id'] ?? '',
                        'number' => $phone,
                        'message' => $msgText,
                    ]);

                return $resp->successful();
            }
        } catch (\Throwable $e) {
            Log::error('Direct WhatsApp HTTP Gateway failed in OTP: ' . $e->getMessage());
        }

        return false;
    }

    public function render()
    {
        $companyName = Setting::getValue('company.name', config('app.name', 'dsBilling'));
        return view('livewire.auth.forgot-password-otp')
            ->layout('layouts.guest', ['title' => 'Reset Kata Sandi via WhatsApp - ' . $companyName]);
    }
}
