@section('header_title', 'Instalasi Cepat')

<div class="space-y-4 p-4">

    {{-- Stepper Progress --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex items-center justify-between relative mb-3">
            <div class="absolute left-0 top-4 w-full h-1 bg-slate-200 dark:bg-slate-700 rounded-full z-0"></div>
            <div class="absolute left-0 top-4 h-1 bg-indigo-500 rounded-full z-0 transition-all duration-300" style="width: {{ (($currentStep - 1) / 5) * 100 }}%"></div>
            @foreach(range(1, 6) as $step)
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors shadow-sm
                        {{ $currentStep === $step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 dark:ring-indigo-900/50' :
                          ($currentStep > $step ? 'bg-indigo-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700') }}">
                        @if($currentStep > $step)
                            <span class="material-symbols-outlined" style="font-size:16px">check</span>
                        @else
                            {{ $step }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-center text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
            @if($currentStep === 1) Pilih Pelanggan
            @elseif($currentStep === 2) Paket Layanan
            @elseif($currentStep === 3) Titik ODP
            @elseif($currentStep === 4) OLT & Port
            @elseif($currentStep === 5) Scan ONU
            @elseif($currentStep === 6) Konfirmasi
            @endif
        </p>
    </div>

    {{-- Main Wizard Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">

        @if($currentStep === 1)
            <h3 class="text-base font-black text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">person</span> Data Pelanggan
            </h3>
            <div class="bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl flex items-center shadow-inner mb-5">
                <button wire:click="$set('is_new_customer', true)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ $is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Baru</button>
                <button wire:click="$set('is_new_customer', false)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ !$is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Terdaftar</button>
            </div>
            @if($is_new_customer)
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Nama Lengkap</label>
                        <input type="text" wire:model="new_customer_name" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: Budi Santoso">
                        @error('new_customer_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Nomor HP/WA</label>
                        <input type="tel" wire:model="new_customer_phone" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="08123456789">
                        @error('new_customer_phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Alamat</label>
                        <textarea wire:model="new_customer_address" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" rows="2" placeholder="Alamat lengkap lokasi"></textarea>
                        @error('new_customer_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Owner / Reseller (Opsional)</label>
                        <select wire:model="new_customer_reseller_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Owner/Reseller --</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px">search</span>
                        <input type="text" wire:model.live.debounce.300ms="searchCustomer" class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Cari nama atau nomor HP...">
                    </div>
                    <select wire:model="customer_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            @endif
        @endif

        @if($currentStep === 2)
            <h3 class="text-base font-black mb-4 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">package</span> Paket Layanan
            </h3>
            <select wire:model="service_profile_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="">-- Pilih Paket --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} (Rp{{ number_format($s->base_price, 0, ',', '.') }})</option>
                @endforeach
            </select>
            @error('service_profile_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        @endif

        @if($currentStep === 3)
            <h3 class="text-base font-black mb-4 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">router</span> Pemilihan ODP
            </h3>
            <select wire:model="odp_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="">-- Pilih ODP Terdekat --</option>
                @foreach($odps as $odp)
                    <option value="{{ $odp->id }}">{{ $odp->name }} (Sisa: {{ $odp->available_ports }} port)</option>
                @endforeach
            </select>
            @error('odp_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        @endif

        @if($currentStep === 4)
            <h3 class="text-base font-black mb-4 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">settings_input_component</span> OLT & PON
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">OLT Server</label>
                    <select wire:model="olt_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih OLT --</option>
                        @foreach($olts as $olt)
                            <option value="{{ $olt->id }}">{{ $olt->name }} ({{ $olt->ip_address }})</option>
                        @endforeach
                    </select>
                    @error('olt_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">PON Port</label>
                    <input type="number" wire:model="pon_port" min="1" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center font-bold text-lg dark:bg-slate-900 dark:text-slate-100" placeholder="1">
                    @error('pon_port') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        @if($currentStep === 5)
            <h3 class="text-base font-black mb-1 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">qr_code_scanner</span> Scan ONU
            </h3>
            <p class="text-slate-400 text-xs mb-4">Scan barcode atau ketik manual Serial Number ONU.</p>
            <div class="space-y-4">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:22px">barcode</span>
                    <input type="text" wire:model="onu_sn" class="w-full pl-12 pr-4 py-4 font-mono text-xl font-black tracking-widest border-2 border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase dark:bg-slate-900 dark:text-slate-100" placeholder="ZTEG1234..." autofocus>
                </div>
                @error('onu_sn') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Vendor</label>
                        <select wire:model="onu_vendor_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Model</label>
                        <input type="text" wire:model="onu_model" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase dark:bg-slate-900 dark:text-slate-100" placeholder="F609">
                    </div>
                </div>
            </div>
        @endif

        @if($currentStep === 6)
            <div class="text-center mb-5">
                <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined" style="font-size:30px">fact_check</span>
                </div>
                <h3 class="text-lg font-black text-slate-800 dark:text-slate-200">Konfirmasi Akhir</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Periksa data sebelum dieksekusi</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-200 dark:divide-slate-700 overflow-hidden mb-4">
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Tipe</span>
                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/50 text-blue-700 font-bold text-[10px] uppercase rounded-full">{{ $service_type }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Pelanggan</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $is_new_customer ? 'Baru' : 'ID: '.$customer_id }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Paket</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">ID: {{ $service_profile_id }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">ODP</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">ID: {{ $odp_id }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">OLT / PON</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">OLT {{ $olt_id }} / Port {{ $pon_port }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">ONU SN</span>
                    <span class="font-mono text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $onu_sn }}</span>
                </div>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 rounded-xl border border-amber-200 dark:border-amber-800/50 flex gap-2">
                <span class="material-symbols-outlined shrink-0 mt-0.5" style="font-size:18px">warning</span>
                <p class="text-xs leading-relaxed">Pastikan fiber sudah terpasang sebelum menekan Aktifkan. Sistem akan provisioning otomatis.</p>
            </div>
        @endif

        {{-- Navigation --}}
        <div class="mt-6 flex gap-2">
            @if($currentStep > 1)
                <button wire:click="previousStep" class="w-1/3 py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">
                    Kembali
                </button>
            @endif
            @if($currentStep < 6)
                <button wire:click="nextStep" class="{{ $currentStep > 1 ? 'w-2/3' : 'w-full' }} py-3.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 flex items-center justify-center gap-2 shadow-md shadow-indigo-600/20 active:scale-95 transition-transform">
                    Lanjut <span class="material-symbols-outlined shrink-0" style="font-size:18px">arrow_forward</span>
                </button>
            @else
                <button wire:click="submit" wire:loading.attr="disabled" class="w-2/3 py-3.5 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 active:scale-95 transition-transform flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
                    <span wire:loading.remove wire:target="submit" class="flex items-center gap-2 justify-center">
                        <span class="material-symbols-outlined shrink-0" style="font-size:18px">rocket_launch</span>
                        AKTIFKAN
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2 justify-center">
                        <span class="material-symbols-outlined animate-spin shrink-0" style="font-size:18px">sync</span>
                        Proses...
                    </span>
                </button>
            @endif
        </div>
    </div>

</div>
