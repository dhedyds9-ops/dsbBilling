<div class="max-w-7xl mx-auto p-3">
    <form wire:submit.prevent="save" class="space-y-3">
        {{-- Breadcrumb --}}
        <x-admin.breadcrumbs />

        {{-- Error Summary --}}
        @if ($errors->any())
            <x-feedback.alert variant="danger" title="Terjadi kesalahan:">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-feedback.alert>
        @endif

        {{-- Flash Messages --}}
        <div class="mb-3">
            @if(session()->has('success'))
                <x-feedback.alert variant="success">
                    {{ session('success') }}
                </x-feedback.alert>
            @endif
            @if(session()->has('error'))
                <x-feedback.alert variant="danger">
                    {{ session('error') }}
                </x-feedback.alert>
            @endif
            @if(session()->has('warning'))
                <x-feedback.alert variant="warning">
                    {{ session('warning') }}
                </x-feedback.alert>
            @endif
            @if(session()->has('info'))
                <x-feedback.alert variant="info">
                    {{ session('info') }}
                </x-feedback.alert>
            @endif
        </div>

        {{-- Card 1: Informasi Paket --}}
        <x-base.card>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Informasi Paket</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket <span class="text-danger-500">*</span></label>
                    <input type="text" wire:model.live="name" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Home 20 Mbps">
                    @error('name') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Service <span class="text-danger-500">*</span></label>
                    <select wire:model.live="service_type" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        <option value="pppoe">PPPoE</option>
                        <option value="hotspot">Hotspot</option>
                        <option value="voucher">Voucher</option>
                    </select>
                    @error('service_type') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Download (Mbps) <span class="text-danger-500">*</span></label>
                    <input type="number" wire:model="download_speed" min="1" max="10000" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="20">
                    @error('download_speed') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload (Mbps) <span class="text-danger-500">*</span></label>
                    <input type="number" wire:model="upload_speed" min="1" max="10000" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="10">
                    @error('upload_speed') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Paket <span class="text-danger-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" wire:model.live="package_type" value="unlimited" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Unlimited</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model.live="package_type" value="time_based" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Time Based</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model.live="package_type" value="quota_based" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Quota Based</span>
                        </label>
                    </div>
                    @error('package_type') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Masa Aktif <span class="text-danger-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="number" wire:model="validity_value" min="1" class="flex-1 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="30">
                        <select wire:model="validity_unit" class="w-28 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            <option value="hours">Jam</option>
                            <option value="days">Hari</option>
                            <option value="months">Bulan</option>
                        </select>
                    </div>
                    @error('validity_value') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    @error('validity_unit') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shared Users <span class="text-danger-500">*</span></label>
                    <input type="number" wire:model="max_devices" min="1" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="1">
                    <p class="text-xs text-gray-500 mt-1">Jumlah perangkat yang dapat login bersamaan menggunakan paket ini.</p>
                    @error('max_devices') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-danger-500">*</span></label>
                    <select wire:model="status" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    @error('status') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-base.card>

        {{-- Card 2: Limitasi --}}
        <x-base.card>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Limitasi</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                {{-- Time Based Fields --}}
                <div x-show="$wire.package_type === 'time_based'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi <span class="text-danger-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="number" wire:model="duration_value" min="1" class="flex-1 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="30">
                        <select wire:model="duration_unit" class="w-32 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            <option value="hours">Jam</option>
                            <option value="days">Hari</option>
                        </select>
                    </div>
                    @error('duration_value') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    @error('duration_unit') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Quota Based Fields --}}
                <div x-show="$wire.package_type === 'quota_based'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kuota <span class="text-danger-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="number" wire:model="quota_value" min="1" class="flex-1 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="100">
                        <select wire:model="quota_unit" class="w-32 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                            <option value="MB">MB</option>
                            <option value="GB">GB</option>
                        </select>
                    </div>
                    @error('quota_value') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    @error('quota_unit') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-base.card>

        {{-- Card 3: Harga --}}
        <x-base.card>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Harga</h3>

            <div class="mb-3">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="is_free" class="w-5 h-5 text-success-600 border-gray-300 rounded focus:ring-success-500">
                    <span class="ml-3 text-sm font-medium text-gray-700">Gratis</span>
                </label>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                @if($isSuperAdmin)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Owner <span class="text-danger-500">*</span></label>
                        <input type="number" wire:model="owner_price" min="0" @if($is_free) disabled @endif class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="100000">
                        @error('owner_price') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($isSuperAdmin)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Reseller <span class="text-danger-500">*</span></label>
                        <input type="number" wire:model="reseller_price" min="0" @if($is_free) disabled @endif class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="120000">
                        @error('reseller_price') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-danger-500">*</span></label>
                    <input type="number" wire:model="base_price" min="0" @if($is_free) disabled @endif class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="150000">
                    @error('base_price') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-base.card>

        {{-- Card 4: Voucher (Only if Hotspot) --}}
        @if($service_type === 'hotspot')
            <x-base.card>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Voucher</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prefix Voucher</label>
                        <input type="text" wire:model="voucher_prefix" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="HOTSPOT">
                        @error('voucher_prefix') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Masa Berlaku setelah Aktivasi</label>
                        <div class="flex gap-2">
                            <input type="number" wire:model="voucher_validity_after_activation" min="1" class="flex-1 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="30">
                            <select class="w-32 border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" disabled>
                                <option>Hari</option>
                            </select>
                        </div>
                        @error('voucher_validity_after_activation') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-base.card>
        @endif

        {{-- Card 5: Advanced (Collapsible) --}}
        <x-base.card padding="false">
            <div class="p-4">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left" x-data="{ open: false }">
                    <h3 class="text-lg font-semibold text-gray-900">Advanced</h3>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" x-collapse class="pt-4 border-t border-gray-200 space-y-3">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PPN (%)</label>
                            <input type="number" wire:model="tax_percentage" min="0" max="100" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="11">
                            @error('tax_percentage') <span class="text-xs text-danger-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="tax_enabled" class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">Aktifkan PPN</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Login Start Time</label>
                            <input type="time" wire:model="login_start_time" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Login End Time</label>
                            <input type="time" wire:model="login_end_time" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>
                        @if($isSuperAdmin)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                                <select wire:model="visibility" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                                    <option value="private">Private</option>
                                    <option value="shared">Shared</option>
                                    <option value="global">Global</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                                <select wire:model="owner_id" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                                    <option value="">Pilih Owner</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Technical Notes</label>
                        <textarea wire:model="technical_notes" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Catatan teknis"></textarea>
                    </div>
                </div>
            </div>
        </x-base.card>

        {{-- Footer Buttons --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('isp.service-profiles.index') }}" class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                Batal
            </a>
            <button type="submit" wire:loading.attr="disabled" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <svg wire:loading wire:target="save" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="save">{{ isset($profileId) ? 'Simpan Paket' : 'Simpan Paket' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
