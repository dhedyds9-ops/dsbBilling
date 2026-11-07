@section('header_title', 'Instalasi Cepat')

<div class="space-y-4 p-4">
    {{-- Global Alert Banner --}}
    @if(session('error') || $errors->any())
        <div class="p-4 bg-rose-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-start gap-3 shadow-md">
            <span class="material-symbols-outlined text-lg shrink-0 mt-0.5 notranslate" translate="no">error</span>
            <div class="space-y-1 flex-1">
                @if(session('error'))
                    <p class="font-bold">{{ session('error') }}</p>
                @endif
                @if($errors->any())
                    <ul class="list-disc pl-4 space-y-0.5 text-xs">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 bg-emerald-500 text-white rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-md">
            <span class="material-symbols-outlined text-lg shrink-0 notranslate" translate="no">check_circle</span>
            <p class="font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if(!$hasAccess)
        {{-- KEAMANAN: AKSES INSTALASI TERKUNCI KARENA TIDAK ADA TUGAS RESMI --}}
        <div class="py-12 px-6 text-center max-w-lg mx-auto bg-white dark:bg-slate-800 rounded-3xl border border-rose-200 dark:border-rose-900/50 shadow-xl space-y-5 my-6 animate-fadeIn">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-4xl notranslate" translate="no">shield_lock</span>
            </div>
            <div class="space-y-2">
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Akses Instalasi Ditutup</h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Tidak ditemukan <strong>Surat Perintah Kerja (SPK)</strong> atau <strong>Tiket Tugas Pemasangan Baru (PSB)</strong> aktif yang ditugaskan kepada Anda saat ini.
                </p>
            </div>
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-left text-xs text-amber-800 dark:text-amber-300 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm notranslate" translate="no">verified_user</span>
                    <span>Kebijakan Keamanan Jaringan & Data Pelanggan</span>
                </div>
                <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-400">
                    Untuk mencegah pemasangan jalur tidak resmi dan menjaga validitas data pelanggan, teknisi lapangan hanya dapat mengaktivasi ONU jika terdapat penugasan resmi dari Admin / Dispatcher.
                </p>
            </div>
            <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('technician.my-jobs.psb') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base notranslate" translate="no">assignment</span>
                    <span>Cek Daftar Tugas Saya (PSB)</span>
                </a>
                <a href="{{ route('technician.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs sm:text-sm transition flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base notranslate" translate="no">dashboard</span>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    @elseif($requireWorkOrderSelection)
        {{-- PEMILIHAN TUGAS PSB SEBELUM MEMULAI INSTALASI --}}
        <div class="space-y-4 max-w-2xl mx-auto my-4 animate-fadeIn">
            <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-2xl border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-3">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-2xl shrink-0 mt-0.5 notranslate" translate="no">assignment_late</span>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-indigo-950 dark:text-indigo-200">Pilih Surat Tugas / SPK PSB Anda</h3>
                    <p class="text-xs text-indigo-700 dark:text-indigo-400">
                        Silakan tentukan penugasan resmi yang sedang Anda kerjakan di lapangan untuk mengunci data pelanggan yang akan diinstalasi:
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                @if(count($pendingTickets) > 0)
                    @foreach($pendingTickets as $t)
                        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs hover:border-indigo-500 transition-all space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-bold text-xs">Tiket PSB</span>
                                    <span class="font-mono text-xs text-slate-500">{{ $t->uuid ?? ('TIKET-' . $t->id) }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $t->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $t->customer?->name ?? 'Pelanggan #' . $t->customer_id }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">location_on</span>
                                    <span>{{ $t->customer?->address ?? $t->description ?? 'Alamat sesuai tiket' }}</span>
                                </div>
                            </div>
                            <button wire:click="selectWorkOrder('ticket', {{ $t->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">play_arrow</span>
                                <span>Pilih & Mulai Pemasangan</span>
                            </button>
                        </div>
                    @endforeach
                @endif

                @if(count($pendingInstallations) > 0)
                    @foreach($pendingInstallations as $inst)
                        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs hover:border-indigo-500 transition-all space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-bold text-xs">SPK CRM</span>
                                    <span class="font-mono text-xs text-slate-500">{{ $inst->uuid ?? ('INST-' . $inst->id) }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $inst->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $inst->customer?->name ?? $inst->customer_name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">location_on</span>
                                    <span>{{ $inst->installation_address ?? $inst->customer?->address ?? 'Alamat pemasangan' }}</span>
                                </div>
                            </div>
                            <button wire:click="selectWorkOrder('installation', {{ $inst->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">play_arrow</span>
                                <span>Pilih & Mulai Pemasangan</span>
                            </button>
                        </div>
                    @endforeach
                @endif

                @if(count($pendingRequisitions) > 0)
                    @foreach($pendingRequisitions as $req)
                        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs hover:border-indigo-500 transition-all space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-bold text-xs">SPK Material Gudang</span>
                                    <span class="font-mono text-xs text-slate-500">{{ $req->requisition_number }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $req->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $req->customer?->name ?? 'Pelanggan #' . $req->customer_id }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">location_on</span>
                                    <span>{{ $req->customer?->address ?? $req->notes ?? 'Alamat sesuai SPK' }}</span>
                                </div>
                            </div>
                            <button wire:click="selectWorkOrder('requisition', {{ $req->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                                <span class="material-symbols-outlined text-sm notranslate" translate="no">play_arrow</span>
                                <span>Pilih & Mulai Pemasangan</span>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @else
        {{-- Stepper Progress --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center justify-between relative mb-3">
                <div class="absolute left-0 top-4 w-full h-1 bg-slate-200 dark:bg-slate-700 rounded-full z-0"></div>
                <div class="absolute left-0 top-4 h-1 bg-indigo-500 rounded-full z-0 transition-all duration-300" style="width: {{ $currentStep >= 6 ? '100%' : ((($currentStep - 1) / 5) * 100) }}%"></div>
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
                @elseif($currentStep === 5) Scan ONU & Wi-Fi
                @elseif($currentStep === 6) Konfirmasi
                @elseif($currentStep === 7) Selesai & Status Provisioning
                @endif
            </p>
        </div>

        {{-- Main Wizard Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5">

            @if($currentStep === 1)
                {{-- Banner Surat Tugas Aktif --}}
                @if($activeWorkOrder)
                    <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-3 mb-4 animate-fadeIn">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-base notranslate" translate="no">verified</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200 truncate">Surat Tugas Terverifikasi: {{ $activeWorkOrder['number'] }}</div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 truncate">{{ $activeWorkOrder['customer_name'] }} • {{ $activeWorkOrder['customer_phone'] }}</div>
                            </div>
                        </div>
                        <button wire:click="clearWorkOrder" type="button" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">
                            Ganti Tugas
                        </button>
                    </div>
                @endif

                <h3 class="text-base font-black text-slate-800 dark:text-slate-200 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">person</span> Data Pelanggan
                </h3>

                @if(!$isRestrictedTechnician)
                    <div class="bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl flex items-center shadow-inner mb-5">
                        <button wire:click="$set('is_new_customer', true)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ $is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Baru</button>
                        <button wire:click="$set('is_new_customer', false)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ !$is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Terdaftar</button>
                    </div>
                @endif

                @if($is_new_customer)
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Nama Lengkap</label>
                            <input type="text" wire:model="new_customer_name" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: Budi Santoso" {{ $isRestrictedTechnician ? 'readonly' : '' }}>
                            @error('new_customer_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Nomor HP/WA</label>
                            <input type="tel" wire:model="new_customer_phone" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="08123456789" {{ $isRestrictedTechnician ? 'readonly' : '' }}>
                            @error('new_customer_phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Alamat</label>
                            <textarea wire:model="new_customer_address" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" rows="2" placeholder="Alamat lengkap lokasi" {{ $isRestrictedTechnician ? 'readonly' : '' }}></textarea>
                            @error('new_customer_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @if(!$isRestrictedTechnician)
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Owner / Reseller (Opsional)</label>
                                <x-ds-searchable-select
                                    wire:model="new_customer_reseller_id"
                                    strategy="local"
                                    :options="$resellersOptions"
                                    placeholder="-- Pilih Owner/Reseller --"
                                    search-placeholder="Cari reseller..."
                                />
                            </div>
                        @endif
                    </div>
                @else
                    <div class="space-y-3">
                        @if($isRestrictedTechnician && $this->selectedCustomer)
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pelanggan Sesuai Surat Tugas</div>
                                <div class="text-base font-bold text-slate-900 dark:text-white">{{ $this->selectedCustomer->name }}</div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">call</span>
                                    <span>{{ $this->selectedCustomer->phone }}</span>
                                    <span>•</span>
                                    <span class="font-mono">{{ $this->selectedCustomer->code }}</span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 flex items-start gap-1.5 pt-1 border-t border-slate-200/60 dark:border-slate-700/60">
                                    <span class="material-symbols-outlined text-xs notranslate shrink-0 mt-0.5" translate="no">location_on</span>
                                    <span>{{ $this->selectedCustomer->address ?? 'Alamat belum diatur' }}</span>
                                </div>
                            </div>
                        @else
                            <x-ds-searchable-select
                                wire:model="customer_id"
                                strategy="remote"
                                search-action="searchCustomers"
                                placeholder="-- Pilih Pelanggan Terdaftar --"
                                search-placeholder="Cari nama, nomor HP, atau kode..."
                                :selected-label="$this->selectedCustomerLabel"
                                :error="$errors->first('customer_id')"
                            />

                            {{-- Detail Pelanggan Terpilih --}}
                            @if(!$is_new_customer && $this->selectedCustomer)
                                <div class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900/50 flex items-start justify-between gap-3 animate-fadeIn">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                            {{ strtoupper(substr($this->selectedCustomer->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                                                {{ $this->selectedCustomer->name }}
                                            </div>
                                            <div class="text-xs text-indigo-700 dark:text-indigo-400 flex items-center gap-2 mt-0.5">
                                                <span>{{ $this->selectedCustomer->phone }}</span>
                                                <span>•</span>
                                                <span class="font-mono text-[11px]">{{ $this->selectedCustomer->code }}</span>
                                            </div>
                                            @if($this->selectedCustomer->address)
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-1">
                                                    {{ $this->selectedCustomer->address }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 shrink-0">
                                        Terdaftar
                                    </span>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            @endif

        @if($currentStep === 2)
            <h3 class="text-base font-black mb-4 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">package</span> Paket Layanan
            </h3>
            <x-ds-searchable-select
                wire:model="service_profile_id"
                strategy="local"
                :options="$servicesOptions"
                placeholder="-- Pilih Paket --"
                search-placeholder="Cari paket..."
                :error="$errors->first('service_profile_id')"
            />

            {{-- Detail Paket Terpilih --}}
            @if($this->selectedServiceProfile)
                @php $sp = $this->selectedServiceProfile; @endphp
                <div class="mt-4 p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900/50 flex items-start justify-between gap-3 animate-fadeIn">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px">wifi</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                                {{ $sp->name }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-2 mt-0.5">
                                <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase {{ $service_type === 'hotspot' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                    {{ strtoupper($service_type) }}
                                </span>
                                @if($sp->download_speed)
                                    <span>Bandwidth: <strong>{{ $sp->download_speed }}M / {{ $sp->upload_speed ?? $sp->download_speed }}M</strong></span>
                                @endif
                                <span>•</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($sp->base_price ?? $sp->price ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 shrink-0">
                        Terpilih
                    </span>
                </div>
            @endif

            <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center justify-between">
                    <span>Kredensial Layanan PPPoE / Hotspot</span>
                    <span class="text-[10px] text-slate-400 font-normal normal-case">Opsional (Kosongkan jika auto-generate)</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Username</label>
                        <input type="text" wire:model="custom_username" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-mono" placeholder="Default: Otomatis sistem">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Password</label>
                        <input type="text" wire:model="custom_password" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-mono" placeholder="Default: pppoe">
                    </div>
                </div>
            </div>
        @endif

        @if($currentStep === 3)
            <h3 class="text-base font-black mb-4 text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">router</span> Pemilihan ODP
            </h3>
            <x-ds-searchable-select
                wire:model="odp_id"
                strategy="remote"
                search-action="searchOdps"
                placeholder="-- Pilih ODP Terdekat --"
                search-placeholder="Cari nama atau kode ODP..."
                :selected-label="$this->selectedOdpLabel"
                :error="$errors->first('odp_id')"
            />

            {{-- Detail ODP Terpilih --}}
            @if($this->selectedOdp)
                @php $odpObj = $this->selectedOdp; @endphp
                <div class="mt-4 p-3.5 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl border border-emerald-100 dark:border-emerald-900/50 flex items-start justify-between gap-3 animate-fadeIn">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px">hub</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">
                                {{ $odpObj->name }}
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 flex flex-wrap items-center gap-2 mt-0.5">
                                <span>Sisa Port: <strong>{{ $odpObj->availablePortCount ?? $odpObj->available_ports ?? '-' }}/{{ $odpObj->port_count ?? '-' }}</strong></span>
                                @if($odpObj->olt)
                                    <span>• OLT: <strong>{{ $odpObj->olt->name }}</strong></span>
                                @endif
                            </div>
                            @if($odpObj->address)
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                    {{ $odpObj->address }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 shrink-0">
                        Terpilih
                    </span>
                </div>
            @endif
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
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">qr_code_scanner</span> Registrasi ONU
            </h3>
            <p class="text-slate-400 text-xs mb-4">Ketik manual atau gunakan kamera HP untuk scan Serial Number (SN).</p>
            <div class="space-y-4" x-data="{
                scannerOpen: false,
                html5QrcodeScanner: null,
                startScanner() {
                    this.scannerOpen = true;
                    // Load script if not loaded
                    if (typeof Html5QrcodeScanner === 'undefined') {
                        let script = document.createElement('script');
                        script.src = 'https://unpkg.com/html5-qrcode';
                        script.onload = () => this.initScanner();
                        document.head.appendChild(script);
                    } else {
                        this.initScanner();
                    }
                },
                initScanner() {
                    this.$nextTick(() => {
                        this.html5QrcodeScanner = new Html5QrcodeScanner(
                            'reader', { fps: 10, qrbox: {width: 250, height: 100}, formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39, Html5QrcodeSupportedFormats.QR_CODE ] }, false);
                        this.html5QrcodeScanner.render(this.onScanSuccess.bind(this), this.onScanFailure.bind(this));
                    });
                },
                onScanSuccess(decodedText, decodedResult) {
                    @this.set('onu_sn', decodedText);
                    this.closeScanner();
                },
                onScanFailure(error) {
                    // ignore
                },
                closeScanner() {
                    if (this.html5QrcodeScanner) {
                        this.html5QrcodeScanner.clear();
                    }
                    this.scannerOpen = false;
                }
            }">
                
                <!-- Scanner UI (Hidden by default) -->
                <div x-show="scannerOpen" style="display: none;" class="bg-black rounded-2xl overflow-hidden relative">
                    <button type="button" @click="closeScanner" class="absolute top-2 right-2 z-50 bg-red-500 text-white p-2 rounded-full flex shadow-lg">
                        <span class="material-symbols-outlined" style="font-size:20px">close</span>
                    </button>
                    <div id="reader" class="w-full bg-black min-h-[250px]"></div>
                </div>

                <!-- Input Area -->
                <div class="relative flex gap-2" x-show="!scannerOpen">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:22px">barcode</span>
                        <input type="text" wire:model="onu_sn" class="w-full pl-12 pr-4 py-4 font-mono text-xl font-black tracking-widest border-2 border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase" placeholder="ZTEG1234..." autofocus>
                    </div>
                    <button type="button" @click="startScanner" class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl px-4 flex flex-col items-center justify-center transition-colors shadow-md">
                        <span class="material-symbols-outlined" style="font-size:24px">photo_camera</span>
                        <span class="text-[10px] font-bold mt-0.5">SCAN</span>
                    </button>
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

                {{-- Wi-Fi Configuration Inputs --}}
                <div class="border-t border-slate-200 dark:border-slate-700/60 pt-3 mt-3">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-indigo-500" style="font-size:16px">wifi</span>
                        Konfigurasi Wi-Fi Pelanggan (TR-069)
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Wi-Fi SSID</label>
                            <input type="text" wire:model="wifi_ssid" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="Default: WINETS-XXXX">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Wi-Fi Password</label>
                            <input type="text" wire:model="wifi_password" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-mono" placeholder="Default: Kosong / Bebas">
                        </div>
                    </div>
                </div>

                {{-- Redaman Optik & Pemakaian Material Lapangan --}}
                <div class="border-t border-slate-200 dark:border-slate-700/60 pt-3 mt-3 space-y-3">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-indigo-500" style="font-size:16px">settings_input_antenna</span>
                        Pengukuran Redaman & Material PSB (Opsional)
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Redaman OPM (dBm)</label>
                            <input type="text" wire:model="optical_power" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="-19.5 dBm">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Dropcore (Meter)</label>
                            <input type="number" wire:model="dropcore_meters" min="0" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Fast Connector (Pcs)</label>
                            <input type="number" wire:model="fast_connector_qty" min="0" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-center focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="0">
                        </div>
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
                    <span class="text-sm font-bold text-slate-900 dark:text-white text-right">
                        @if($this->selectedCustomer)
                            {{ $this->selectedCustomer->name }}
                            <span class="block text-xs font-normal text-slate-400">
                                {{ $this->selectedCustomer->phone ?: ($is_new_customer ? 'Pelanggan Baru' : 'ID: '.$customer_id) }}
                            </span>
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Paket</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white text-right">
                        {{ $this->selectedServiceProfile?->name ?? ($service_profile_id ? 'ID: '.$service_profile_id : '-') }}
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">ODP</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white text-right">
                        {{ $this->selectedOdp?->name ?? ($odp_id ? 'ID: '.$odp_id : '-') }}
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">OLT / PON</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white text-right">
                        {{ $this->selectedOlt?->name ?? ($olt_id ? 'OLT #'.$olt_id : '-') }} / Port {{ $pon_port }}
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">ONU SN</span>
                    <span class="font-mono text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $onu_sn }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Username</span>
                    <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ !empty(trim($custom_username)) ? $custom_username : '(Otomatis Sistem)' }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Password</span>
                    <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">{{ !empty(trim($custom_password)) ? '••••••' : ($service_type === 'pppoe' ? 'pppoe' : '(Random)') }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Wi-Fi SSID</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ !empty(trim($wifi_ssid)) ? $wifi_ssid : ('WINETS-' . substr($onu_sn, -4)) }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Wi-Fi Password</span>
                    <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">{{ !empty(trim($wifi_password)) ? '••••••' : '(Bebas / Tanpa Password)' }}</span>
                </div>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 rounded-xl border border-amber-200 dark:border-amber-800/50 flex gap-2">
                <span class="material-symbols-outlined shrink-0 mt-0.5" style="font-size:18px">warning</span>
                <p class="text-xs leading-relaxed">Pastikan fiber sudah terpasang sebelum menekan Aktifkan. Sistem akan provisioning otomatis ke OLT & GenieACS NBI.</p>
            </div>
            @if(session('error') || $errors->any())
                <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 rounded-xl border border-rose-200 dark:border-rose-800/50 flex gap-2" x-data x-init="$el.scrollIntoView({behavior: 'smooth', block: 'center'})">
                    <span class="material-symbols-outlined shrink-0 mt-0.5" style="font-size:18px">error</span>
                    <div class="text-xs leading-relaxed font-semibold space-y-0.5">
                        @if(session('error'))<p>{{ session('error') }}</p>@endif
                        @foreach($errors->all() as $err)
                            @if($err !== session('error'))<p>{{ $err }}</p>@endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Step 7: Ringkasan Hasil PSB & Status Provisioning ONT (P1-1A) --}}
        @if($currentStep === 7)
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <span class="material-symbols-outlined" style="font-size:36px">check_circle</span>
                </div>
                <h3 class="text-xl font-black text-slate-800 dark:text-slate-100">PSB Berhasil Disimpan!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Data pelanggan dan perangkat telah berhasil tersimpan ke sistem dsBilling.</p>
            </div>

            {{-- Provisioning Status Card --}}
            <div class="bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-200 dark:divide-slate-700 overflow-hidden mb-5">
                <div class="flex justify-between items-center px-4 py-3.5 bg-slate-100/70 dark:bg-slate-800">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-indigo-500" style="font-size:18px">settings_remote</span>
                        Status Provisioning ONT
                    </span>
                    @php
                        $badgeClasses = match($provisioning_status) {
                            'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300',
                            'waiting_device' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300',
                            'processing', 'queued', 'pending' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300',
                            default => 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300',
                        };
                        $statusLabels = [
                            'success' => 'BERHASIL TERKONFIGURASI',
                            'waiting_device' => 'MENUNGGU INFORM ONT',
                            'queued' => 'ANTREAN GENIEACS',
                            'processing' => 'SEDANG DIPROSES',
                            'failed' => 'GAGAL / RETRY DIBUTUHKAN',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 font-black text-[11px] uppercase rounded-full {{ $badgeClasses }}">
                        {{ $statusLabels[$provisioning_status] ?? strtoupper($provisioning_status) }}
                    </span>
                </div>

                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Serial Number ONT</span>
                    <span class="font-mono text-sm font-black text-indigo-600 dark:text-indigo-400">{{ $onu_sn }}</span>
                </div>

                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Status WAN PPPoE</span>
                    <span class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                        {{ !empty(trim($custom_username)) ? $custom_username : '(Otomatis Sistem)' }}
                    </span>
                </div>

                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Status Wi-Fi (SSID)</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        {{ !empty(trim($wifi_ssid)) ? $wifi_ssid : ('WINETS-' . substr($onu_sn, -4)) }}
                    </span>
                </div>

                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Waktu Terakhir</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $last_provisioned_at }}</span>
                </div>
            </div>

            @if(!empty($provisioning_message))
                <div class="p-3.5 mb-5 rounded-xl border text-xs leading-relaxed {{ $provisioning_status === 'failed' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800/50' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/50' }}">
                    {{ $provisioning_message }}
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="space-y-2.5">
                @if($completed_pipeline_id)
                    <a href="{{ route('technician.provisioning.show', $completed_pipeline_id) }}" class="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 flex items-center justify-center gap-2 shadow-md shadow-indigo-600/20 active:scale-95 transition-transform">
                        <span class="material-symbols-outlined" style="font-size:18px">visibility</span>
                        Lihat Detail Provisioning
                    </a>
                @endif

                <button wire:click="retryProvisioning" wire:loading.attr="disabled" type="button" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-transform">
                    <span wire:loading.remove wire:target="retryProvisioning" class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined" style="font-size:18px">refresh</span>
                        Retry Provisioning jika gagal
                    </span>
                    <span wire:loading wire:target="retryProvisioning" class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined animate-spin" style="font-size:18px">sync</span>
                        Mengirim Ulang...
                    </span>
                </button>

                <a href="{{ route('technician.my-jobs.index') }}" class="w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center gap-2 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:18px">check</span>
                    Selesai & Ke Daftar Pekerjaan
                </a>
            </div>
        @endif

        {{-- Navigation (Hanya tampil di Langkah 1 s.d. 6) --}}
        @if($currentStep < 7)
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
        @endif
    </div>
    @endif

</div>
