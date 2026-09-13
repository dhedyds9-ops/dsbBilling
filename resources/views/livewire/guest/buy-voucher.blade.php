<div class="min-h-screen bg-gray-50 dark:bg-gray-900/50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <x-application-logo class="w-auto h-12 text-primary-600" />
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            Beli Voucher Internet
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Tanpa perlu mendaftar, langsung aktif!
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-slate-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
            
            <!-- <x-validation-errors --> class="mb-4" />
            
            @if (session()->has('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="material-symbols-outlined text-red-400">error</span>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </h3>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="checkout" class="space-y-6">
                
                <!-- Ringkasan Paket -->
                <div class="bg-primary-50 rounded-lg p-4 border border-primary-100 mb-6">
                    <h4 class="text-sm font-medium text-primary-800 uppercase tracking-wider mb-2">Ringkasan Pesanan</h4>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $serviceProfile->name }}</span>
                        <span class="text-gray-900 dark:text-gray-100 font-bold">Rp {{ number_format($serviceProfile->base_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Durasi: {{ $serviceProfile->validity_days }} hari
                    </div>
                </div>

                <div>
                    <label for="wa_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nomor WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">
                                <span class="material-symbols-outlined text-base">chat</span>
                            </span>
                        </div>
                        <input wire:model="wa_number" id="wa_number" type="text" required class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-slate-900 dark:text-slate-100" placeholder="081234567890">
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" id="wa-description">Username dan Password Hotspot akan dikirimkan ke nomor WhatsApp ini.</p>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="checkout">Lanjutkan Pembayaran</span>
                        <span wire:loading wire:target="checkout" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-slate-800 text-gray-500 dark:text-gray-400">
                            Aman & Otomatis
                        </span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>







