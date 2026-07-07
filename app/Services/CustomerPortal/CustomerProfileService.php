<?php

namespace App\Services\CustomerPortal;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerProfileService
{
    public function getProfile(int $customerId): ?User
    {
        return User::find($customerId);
    }

    public function updateProfile(int $customerId, array $data): array
    {
        $user = User::findOrFail($customerId);

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
    }

    public function changePassword(int $customerId, string $currentPassword, string $newPassword): array
    {
        $user = User::findOrFail($customerId);

        if (!Hash::check($currentPassword, $user->password)) {
            return ['success' => false, 'message' => 'Password saat ini salah'];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return ['success' => true, 'message' => 'Password berhasil diubah'];
    }
}

