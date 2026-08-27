<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Ubah Kredensial Hotspot</h1>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        @if ($message)
            <div class="mb-4 {{ $messageType === 'success' ? 'text-green-600' : 'text-red-600' }}">{{ $message }}</div>
        @endif

        @if ($hotspotUsers->isEmpty())
            <div class="text-gray-600">Anda tidak memiliki layanan Hotspot aktif.</div>
        @else
            <form wire:submit="saveCredentials">
                <div class="mb-4">
                    <label for="hotspotUserId" class="block text-sm font-medium text-gray-700 mb-2">Pilih Hotspot User</label>
                    <select id="hotspotUserId" wire:model="hotspotUserId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        @foreach ($hotspotUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="newUsername" class="block text-sm font-medium text-gray-700 mb-2">Username Baru (Opsional)</label>
                    <input type="text" id="newUsername" wire:model="newUsername" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" minlength="3">
                </div>

                <div class="mb-4">
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">Password Baru (Opsional)</label>
                    <input type="password" id="newPassword" wire:model="newPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" minlength="6">
                </div>

                <div class="mb-4">
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" id="confirmPassword" wire:model="confirmPassword" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" minlength="6">
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Simpan Kredensial
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
