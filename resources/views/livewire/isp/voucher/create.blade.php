<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('isp.vouchers.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Generate Voucher</h1>
            <p class="mt-1 text-sm text-slate-500">Generate voucher Hotspot dengan mudah</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Generate -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <form wire:submit.prevent="generate" class="space-y-6 p-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Paket Internet</label>
                    <select wire:model="service_profile_id" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Paket</option>
                        @foreach($serviceProfiles as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->name }} - {{ $profile->download_speed }}/{{ $profile->upload_speed }} Mbps - {{ $profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : 'Gratis' }}</option>
                        @endforeach
                    </select>
                    @error('service_profile_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Voucher</label>
                        <input type="number" wire:model="quantity" min="1" max="1000" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Panjang Karakter</label>
                        <input type="number" wire:model="length" min="4" max="32" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('length') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prefix (Opsional)</label>
                        <input type="text" wire:model="prefix" placeholder="VOUCHER-" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Masa Berlaku (Hari)</label>
                        <input type="number" wire:model="validity_days" min="1" placeholder="30" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea wire:model="notes" rows="2" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                    <button type="button" wire:click="resetForm" class="px-6 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                        Reset
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Generate Voucher
                    </button>
                </div>
            </form>
        </div>

        <!-- Hasil Generate -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-lg font-medium text-slate-800">Voucher yang Dihasilkan</h3>
            </div>
            <div class="p-6">
                @if(count($generated_vouchers) > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($generated_vouchers as $voucher)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div>
                                    <div class="text-sm font-medium text-slate-900 font-mono">{{ $voucher->code }}</div>
                                    <div class="text-xs text-slate-500">{{ $voucher->serviceProfile?->name ?? '-' }} - {{ $voucher->expires_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                </div>
                                <button onclick="navigator.clipboard.writeText('{{ $voucher->code }}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button onclick="copyAllVouchers()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                            Salin Semua
                        </button>
                    </div>
                    <script>
                        function copyAllVouchers() {
                            const vouchers = {!! json_encode($generated_vouchers->pluck('code')) !!};
                            const text = vouchers.join('\n');
                            navigator.clipboard.writeText(text);
                            alert('Semua voucher berhasil disalin!');
                        }
                    </script>
                @else
                    <div class="text-center py-12 text-slate-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <p class="text-lg font-medium text-slate-900">Belum Ada Voucher</p>
                        <p class="text-slate-500 mt-1">Isi form di sebelah kiri dan klik Generate Voucher</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
