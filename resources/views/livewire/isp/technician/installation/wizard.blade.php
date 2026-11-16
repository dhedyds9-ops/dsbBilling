<div class="space-y-3.5 pb-6">
    @section('header_title', 'Instalasi Cepat')
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
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $t->customer?->name ?? 'Pelanggan #' . $t->customer_id }}</span>
                                    @php
                                        $ticketReseller = $t->reseller?->name ?? $t->customer?->reseller?->name ?? null;
                                    @endphp
                                    @if($ticketReseller)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold text-[10px] border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px] notranslate" translate="no">storefront</span>
                                            {{ $ticketReseller }}
                                        </span>
                                    @endif
                                </div>
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
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $inst->customer?->name ?? $inst->customer_name }}</span>
                                    @php
                                        $instReseller = $inst->reseller?->name ?? $inst->customer?->reseller?->name ?? $inst->survey?->prospect?->reseller?->name ?? null;
                                    @endphp
                                    @if($instReseller)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold text-[10px] border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px] notranslate" translate="no">storefront</span>
                                            {{ $instReseller }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs notranslate" translate="no">location_on</span>
                                    <span>{{ $inst->installation_address ?: ($inst->customer?->address ?: ($inst->survey?->address ?: 'Alamat pemasangan')) }}</span>
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
        {{-- Stepper Progress (7 Tahap Standar INETS) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-[#DCE6F5] dark:border-slate-700 p-4">
            <div class="flex items-center justify-between relative mb-3">
                <div class="absolute left-0 top-4 w-full h-1 bg-slate-200 dark:bg-slate-700 rounded-full z-0"></div>
                <div class="absolute left-0 top-4 h-1 bg-blue-600 rounded-full z-0 transition-all duration-300" style="background-color: #0866F5; width: {{ $currentStep >= 7 ? '100%' : ((($currentStep - 1) / 6) * 100) }}%"></div>
                @foreach(range(1, 7) as $step)
                    <div class="relative z-10 flex flex-col items-center">
                        <button type="button" 
                            @if($step < $currentStep && $currentStep < 7) wire:click="goToStep({{ $step }})" @else disabled @endif
                            style="{{ $currentStep === $step ? 'background-color: #0866F5 !important; color: #ffffff !important;' : ($currentStep > $step ? 'background-color: #16A36A !important; color: #ffffff !important;' : '') }}"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-sm
                            {{ $currentStep === $step ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-900/50 scale-105' :
                              ($currentStep > $step ? 'bg-emerald-600 text-white cursor-pointer hover:opacity-90' : 'bg-white dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700') }}">
                            @if($currentStep > $step)
                                <span class="material-symbols-outlined" style="font-size:16px">check</span>
                            @else
                                {{ $step }}
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase">Tahap {{ $currentStep }} / 7</span>
                <p class="text-right text-xs font-bold text-[#0866F5] dark:text-blue-400 uppercase tracking-wider">
                    @if($currentStep === 1) 1. SPK & Pelanggan
                    @elseif($currentStep === 2) 2. Paket Layanan
                    @elseif($currentStep === 3) 3. Titik ODP
                    @elseif($currentStep === 4) 4. OLT & Port PON
                    @elseif($currentStep === 5) 5. Registrasi ONT, WiFi & Material
                    @elseif($currentStep === 6) 6. Konfirmasi & Review
                    @elseif($currentStep === 7) 7. Hasil Akhir & Status Provisioning
                    @endif
                </p>
            </div>
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
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 truncate flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $activeWorkOrder['customer_name'] }} • {{ $activeWorkOrder['customer_phone'] }}</span>
                                    @if(!empty($activeWorkOrder['reseller_name']))
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 font-bold text-[10px]">
                                            <span class="material-symbols-outlined text-[11px] notranslate" translate="no">storefront</span>
                                            Mitra: {{ $activeWorkOrder['reseller_name'] }}
                                        </span>
                                    @endif
                                </div>
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

                @if(!$isRestrictedTechnician || empty($activeWorkOrder))
                    <div class="bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl flex items-center shadow-inner mb-5">
                        <button type="button" wire:click="$set('is_new_customer', true)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ $is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Baru</button>
                        <button type="button" wire:click="$set('is_new_customer', false)" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all {{ !$is_new_customer ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">Terdaftar</button>
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
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Owner / Reseller (Opsional)</label>
                            <x-ds-searchable-select
                                wire:model="new_customer_reseller_id"
                                strategy="local"
                                :options="$resellersOptions"
                                placeholder="-- Pilih Owner / Reseller (Default: Kantor Pusat / HQ) --"
                                search-placeholder="Cari nama atau email reseller..."
                            />
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                Pilih Mitra / Reseller jika pemasangan ini untuk pelanggan mitra, atau kosongkan jika milik Kantor Pusat (HQ).
                            </p>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        @if($isRestrictedTechnician && $this->selectedCustomer && !empty($activeWorkOrder))
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
                                @php
                                    $resellerObj = $this->selectedCustomer->reseller ?? (!empty($this->new_customer_reseller_id) ? \App\Models\User::withoutGlobalScopes()->find($this->new_customer_reseller_id) : null);
                                @endphp
                                <div class="text-xs flex items-center justify-between pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60">
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Owner / Reseller:</span>
                                    @if($resellerObj)
                                        <span class="inline-flex items-center gap-1 font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg border border-purple-200 dark:border-purple-800 text-[11px]">
                                            <span class="material-symbols-outlined text-[13px] notranslate" translate="no">storefront</span>
                                            <span>{{ $resellerObj->name }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-lg text-[11px]">
                                            <span class="material-symbols-outlined text-[13px] notranslate" translate="no">domain</span>
                                            <span>Kantor Pusat (HQ)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <x-ds-searchable-select
                                wire:model.live="customer_id"
                                strategy="local"
                                :options="$customersOptions"
                                search-action="searchCustomers"
                                placeholder="-- Pilih Pelanggan Terdaftar --"
                                search-placeholder="Cari nama, nomor HP, atau kode..."
                                :selected-label="$this->selectedCustomerLabel"
                                :selected-sublabel="$this->selectedCustomerSublabel"
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
                                            @php
                                                $selectResellerObj = $this->selectedCustomer->reseller ?? (!empty($this->new_customer_reseller_id) ? \App\Models\User::withoutGlobalScopes()->find($this->new_customer_reseller_id) : null);
                                            @endphp
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                                                <span>Owner / Reseller:</span>
                                                @if($selectResellerObj)
                                                    <span class="font-bold text-purple-700 dark:text-purple-300">{{ $selectResellerObj->name }}</span>
                                                @else
                                                    <span class="font-semibold text-slate-600 dark:text-slate-400">Kantor Pusat (HQ)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 shrink-0">
                                        Terdaftar
                                    </span>
                                </div>

                                @if(empty($this->selectedCustomer->reseller_id))
                                    <div class="mt-2.5">
                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Tautkan ke Owner / Reseller (Opsional)</label>
                                        <x-ds-searchable-select
                                            wire:model="new_customer_reseller_id"
                                            strategy="local"
                                            :options="$resellersOptions"
                                            placeholder="-- Tautkan ke Owner / Reseller (Default: HQ) --"
                                            search-placeholder="Cari reseller..."
                                        />
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                @endif
            @endif

        @if($currentStep === 2)
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500" style="font-size:22px">package</span> 
                            Pilih Paket Layanan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih paket kecepatan & tipe koneksi yang akan dipasang ke ONT pelanggan.</p>
                    </div>

                    {{-- Quick Filter Tipe Layanan --}}
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl self-start sm:self-center">
                        <button type="button" wire:click="setPackageFilterType('all')"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $package_filter_type === 'all' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            Semua
                        </button>
                        <button type="button" wire:click="setPackageFilterType('pppoe')"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $package_filter_type === 'pppoe' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            PPPoE
                        </button>
                        <button type="button" wire:click="setPackageFilterType('hotspot')"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $package_filter_type === 'hotspot' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                            Hotspot
                        </button>
                    </div>
                </div>

                {{-- Search Box & Filter --}}
                @if(count($allServiceRecords ?? []) > 4)
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 pointer-events-none">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">search</span>
                        </span>
                        <input type="text" wire:model.live.debounce.250ms="package_search"
                               placeholder="Ketik untuk mencari nama paket, kode, atau kecepatan..."
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    </div>
                @endif

                {{-- Grid Kartu Pilihan Paket (Interactive Plan Cards) --}}
                @if(isset($serviceRecords) && count($serviceRecords) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($serviceRecords as $pkg)
                            @php
                                $isSelected = (string)$service_profile_id === (string)$pkg->id;
                                $price = $pkg->base_price ?? $pkg->price ?? $pkg->cost_price ?? 0;
                                $rawType = strtolower((string)($pkg->service_type ?: 'pppoe'));
                                $isHotspot = in_array($rawType, ['hotspot', 'voucher', 'member_hotspot']);
                            @endphp
                            <div wire:key="pkg-card-{{ $pkg->id }}"
                                 wire:click="selectPackage({{ $pkg->id }})"
                                 class="relative p-4 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between gap-3 shadow-xs {{ $isSelected ? 'border-indigo-600 bg-indigo-50/60 dark:bg-indigo-950/40 dark:border-indigo-400 ring-2 ring-indigo-500/20 shadow-md' : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm' }}">
                                
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-md font-black text-[10px] uppercase {{ $isHotspot ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                                {{ $isHotspot ? 'HOTSPOT' : 'PPPoE' }}
                                            </span>
                                            @if($pkg->code)
                                                <span class="font-mono text-[10px] text-slate-400">{{ $pkg->code }}</span>
                                            @endif
                                        </div>

                                        @if($isSelected)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white font-extrabold text-[10px] flex items-center gap-0.5 shadow-xs">
                                                <span class="material-symbols-outlined text-[13px] notranslate" translate="no">check</span>
                                                DIPILIH
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm leading-tight mb-1">
                                        {{ $pkg->name }}
                                    </h4>

                                    @if($pkg->download_speed)
                                        <div class="flex items-center gap-1 text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">
                                            <span class="material-symbols-outlined text-indigo-500 text-sm notranslate" translate="no">speed</span>
                                            <span>Speed: <strong>{{ $pkg->download_speed }} Mbps</strong></span>
                                            @if($pkg->upload_speed && $pkg->upload_speed != $pkg->download_speed)
                                                <span class="text-slate-400 font-normal text-[11px]">/ {{ $pkg->upload_speed }}M UL</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($pkg->description)
                                        <p class="text-[11px] text-slate-400 line-clamp-2 mt-1">
                                            {{ $pkg->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-2.5 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block leading-none">Harga</span>
                                        <span class="font-black text-sm text-emerald-600 dark:text-emerald-400">
                                            Rp{{ number_format($price, 0, ',', '.') }}
                                            <span class="text-[10px] font-normal text-slate-400">/bln</span>
                                        </span>
                                    </div>

                                    <button type="button" class="px-3 py-1 rounded-xl text-xs font-bold transition-colors {{ $isSelected ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                        {{ $isSelected ? 'Terpilih' : 'Pilih' }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center bg-slate-50 dark:bg-slate-800/40 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-6">
                        <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">inventory_2</span>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Tidak ada paket yang sesuai</p>
                        <p class="text-xs text-slate-400 mt-1">Coba ganti filter tipe layanan atau hapus kata pencarian.</p>
                        <button type="button" wire:click="$set('package_filter_type', 'all'); $set('package_search', '');" class="mt-3 px-3 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold">
                            Tampilkan Semua Paket
                        </button>
                    </div>
                @endif

                {{-- Fallback Dropdown Select Standar (Untuk kemudahan akses) --}}
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                        Atau pilih langsung lewat dropdown:
                    </label>
                    <select wire:model.live="service_profile_id"
                            class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-900 dark:text-slate-100 shadow-xs">
                        <option value="">-- Pilih Paket Layanan --</option>
                        @foreach($allServiceRecords ?? $serviceRecords as $opt)
                            @php
                                $optPrice = $opt->base_price ?? $opt->price ?? 0;
                                $optType = strtoupper($opt->service_type ?: 'PPPOE');
                                $optSpeed = $opt->download_speed ? " • {$opt->download_speed} Mbps" : '';
                            @endphp
                            <option value="{{ $opt->id }}">
                                {{ $opt->name }} [{{ $optType }}] - Rp{{ number_format($optPrice, 0, ',', '.') }}{{ $optSpeed }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_profile_id')
                        <p class="text-xs text-red-500 mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Detail Paket Terpilih --}}
                @if($this->selectedServiceProfile)
                    @php $sp = $this->selectedServiceProfile; @endphp
                    <div class="mt-2 p-4 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex items-start justify-between gap-3 animate-fadeIn">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">wifi</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-emerald-950 dark:text-emerald-100 truncate">
                                    {{ $sp->name }}
                                </div>
                                <div class="text-xs text-emerald-700 dark:text-emerald-300 flex flex-wrap items-center gap-2 mt-0.5">
                                    <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase {{ $service_type === 'hotspot' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                        {{ strtoupper($service_type) }}
                                    </span>
                                    @if($sp->download_speed)
                                        <span>Bandwidth: <strong>{{ $sp->download_speed }}M / {{ $sp->upload_speed ?? $sp->download_speed }}M</strong></span>
                                    @endif
                                    <span>&bull;</span>
                                    <span class="font-bold text-emerald-700 dark:text-emerald-200">Rp{{ number_format($sp->base_price ?? $sp->price ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-emerald-200/80 dark:bg-emerald-800 text-emerald-900 dark:text-emerald-100 shrink-0">
                            Paket Terverifikasi
                        </span>
                    </div>
                @endif

                {{-- Kredensial PPPoE / Hotspot --}}
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
            </div>
        @endif

        @if($currentStep === 3)
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">router</span> Titik ODP
                </h3>
                <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full border border-slate-200 dark:border-slate-700">
                    Opsional
                </span>
            </div>

            @if($allOdps->isEmpty())
                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 space-y-2 mb-4">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <span class="material-symbols-outlined text-amber-500" style="font-size:20px">info</span>
                        Belum Ada Data ODP di Sistem
                    </div>
                    <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                        Data ODP belum diinput oleh Admin atau lokasi ini belum memiliki ODP terdaftar. Anda dapat melewati langkah ini dan langsung melanjutkan ke pengaturan OLT & Port PON.
                    </p>
                    <div class="pt-1 flex items-center gap-2">
                        <button type="button" wire:click="nextStep" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <span>Lanjut ke OLT / PON</span>
                            <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Pilih ODP terdekat untuk mencatat port distribusi, atau lewati jika sambungan langsung ke OLT.
                        </p>
                        @if($odp_id)
                            <button type="button" wire:click="clearOdp" class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 font-semibold flex items-center gap-0.5">
                                <span class="material-symbols-outlined" style="font-size:14px">close</span>
                                <span>Hapus ODP</span>
                            </button>
                        @endif
                    </div>

                    <div>
                        <select wire:model.live="odp_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">-- Lewati / Tanpa ODP (Langsung ke OLT) --</option>
                            @foreach($allOdps as $odpItem)
                                @php
                                    $avail = $odpItem->availablePortCount ?? $odpItem->available_ports ?? 0;
                                    $total = $odpItem->port_count ?? 8;
                                    $isFull = $avail <= 0;
                                @endphp
                                <option value="{{ $odpItem->id }}" {{ $isFull ? 'disabled' : '' }}>
                                    {{ $odpItem->name }} {{ $odpItem->code ? "({$odpItem->code})" : '' }} • Sisa: {{ $avail }}/{{ $total }} port {{ $isFull ? '[PENUH]' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('odp_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if(!$this->odp_id)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-400 shrink-0" style="font-size:18px">check_circle</span>
                            <span>Mode <strong>Tanpa ODP</strong> aktif. Port dan OLT dapat Anda tentukan pada langkah berikutnya.</span>
                        </div>
                    @endif
                </div>

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

                {{-- Konfigurasi IP Host & TR-069 GenieACS (C-Data OLT OMCI) --}}
                <div class="border-t border-slate-200 dark:border-slate-700/60 pt-3 mt-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase flex items-center gap-1.5 cursor-pointer select-none">
                            <span class="material-symbols-outlined text-[#0866F5]" style="font-size:18px">router</span>
                            <span>Injeksi IP Host TR-069 OMCI OLT (C-Data)</span>
                        </label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="enable_tr069_iphost" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0866F5]"></div>
                        </label>
                    </div>

                    @if($enable_tr069_iphost)
                        <div class="p-3.5 bg-blue-50/60 dark:bg-slate-800/80 rounded-2xl border border-blue-200/80 dark:border-slate-700 space-y-3">
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                <span class="font-bold text-[#0866F5]">Zero-Touch Provisioning:</span> OLT akan mengirimkan perintah OMCI <code class="bg-blue-100 dark:bg-slate-900 px-1 py-0.5 rounded text-[10px] font-mono font-bold text-[#09265E] dark:text-blue-300">ont ipconfig ip-index 2</code> & <code class="bg-blue-100 dark:bg-slate-900 px-1 py-0.5 rounded text-[10px] font-mono font-bold text-[#09265E] dark:text-blue-300">ont tr069-server</code> agar ONT otomatis mendapat IP Management dan terhubung ke GenieACS.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Mode IP Management</label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="$set('tr069_mode', 'dhcp')" class="flex-1 py-2 px-3 text-xs font-bold rounded-xl border transition-all {{ $tr069_mode === 'dhcp' ? 'bg-[#0866F5] text-white border-[#0866F5] shadow-sm' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600' }}">
                                            DHCP (Otomatis)
                                        </button>
                                        <button type="button" wire:click="$set('tr069_mode', 'static')" class="flex-1 py-2 px-3 text-xs font-bold rounded-xl border transition-all {{ $tr069_mode === 'static' ? 'bg-[#0866F5] text-white border-[#0866F5] shadow-sm' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600' }}">
                                            Static IP
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">VLAN Management TR-069</label>
                                    <input type="number" wire:model="tr069_vlan" class="w-full px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-[#0866F5] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="901">
                                </div>
                            </div>

                            @if($tr069_mode === 'static')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1">IP Static TR-069</label>
                                        <input type="text" wire:model="tr069_static_ip" class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono focus:ring-2 focus:ring-[#0866F5] bg-white dark:bg-slate-800" placeholder="10.10.10.55">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1">Subnet Mask</label>
                                        <input type="text" wire:model="tr069_netmask" class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono focus:ring-2 focus:ring-[#0866F5] bg-white dark:bg-slate-800" placeholder="255.255.255.0">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1">Gateway</label>
                                        <input type="text" wire:model="tr069_gateway" class="w-full px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono focus:ring-2 focus:ring-[#0866F5] bg-white dark:bg-slate-800" placeholder="10.10.10.1">
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1">GenieACS Server URL</label>
                                <input type="text" wire:model="tr069_acs_url" class="w-full px-3.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-mono focus:ring-2 focus:ring-[#0866F5] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="http://10.10.10.1:7547">
                            </div>
                        </div>
                    @endif
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
                <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/50 text-[#0866F5] rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <span class="material-symbols-outlined" style="font-size:30px">fact_check</span>
                </div>
                <h3 class="text-lg font-black text-[#09265E] dark:text-slate-100">Tahap 6: Konfirmasi & Review</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Periksa kembali seluruh parameter instalasi fisik & logis sebelum provisioning dieksekusi.</p>
            </div>

            <div class="space-y-3 mb-5">
                {{-- Group 1: SPK & Pelanggan --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-black text-[#09265E] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#0866F5]">assignment</span> 1. SPK & Pelanggan
                        </span>
                        <button type="button" wire:click="goToStep(1)" class="text-[11px] font-bold text-[#0866F5] hover:underline flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">edit</span> Ubah
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Pelanggan</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $this->selectedCustomer?->name ?? ($new_customer_name ?: 'Pelanggan') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">No. Telepon / HP</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $this->selectedCustomer?->phone ?? ($new_customer_phone ?: '-') }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat Pemasangan</span>
                            <span class="text-slate-700 dark:text-slate-300 text-xs">{{ $this->selectedCustomer?->address ?? ($new_customer_address ?: '-') }}</span>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] uppercase font-bold">Owner / Reseller</span>
                            @php
                                $reviewReseller = !empty($this->new_customer_reseller_id) 
                                    ? \App\Models\User::withoutGlobalScopes()->find($this->new_customer_reseller_id) 
                                    : ($this->selectedCustomer?->reseller ?? null);
                            @endphp
                            @if($reviewReseller)
                                <span class="inline-flex items-center gap-1 font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg border border-purple-200 dark:border-purple-800 text-[11px]">
                                    <span class="material-symbols-outlined text-[13px] notranslate" translate="no">storefront</span>
                                    <span>{{ $reviewReseller->name }}</span>
                                </span>
                            @else
                                <span class="text-slate-600 dark:text-slate-400 font-semibold text-xs flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[13px] notranslate" translate="no">domain</span>
                                    <span>Kantor Pusat (HQ / Direct)</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Group 2: Paket Layanan --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-black text-[#09265E] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#0866F5]">layers</span> 2. Paket Layanan
                        </span>
                        <button type="button" wire:click="goToStep(2)" class="text-[11px] font-bold text-[#0866F5] hover:underline flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">edit</span> Ubah
                        </button>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $this->selectedServiceProfile?->name ?? 'Standar' }}</span>
                            <span class="block text-[11px] text-slate-500 font-mono">Tipe: {{ strtoupper($service_type) }}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-900/50 text-[#0866F5] font-black text-xs rounded-full">
                            Rp {{ number_format($this->selectedServiceProfile?->price ?? 0, 0, ',', '.') }}/bln
                        </span>
                    </div>
                </div>

                {{-- Group 3 & 4: Titik ODP & OLT PON --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-black text-[#09265E] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#0866F5]">share_location</span> 3. Titik ODP
                            </span>
                            <button type="button" wire:click="goToStep(3)" class="text-[11px] font-bold text-[#0866F5] hover:underline flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs">edit</span> Ubah
                            </button>
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $this->selectedOdp?->name ?? 'Direct Cable' }}</div>
                        <div class="text-[11px] text-slate-500">Kode: {{ $this->selectedOdp?->code ?? '-' }}</div>
                    </div>

                    <div class="p-3.5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-black text-[#09265E] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-[#0866F5]">router</span> 4. OLT & Port PON
                            </span>
                            <button type="button" wire:click="goToStep(4)" class="text-[11px] font-bold text-[#0866F5] hover:underline flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs">edit</span> Ubah
                            </button>
                        </div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $this->selectedOlt?->name ?? 'OLT Utama' }}</div>
                        <div class="text-[11px] text-[#0866F5] font-black">Port PON: {{ $pon_port }}</div>
                    </div>
                </div>

                {{-- Group 5 & 6: ONT, Wi-Fi & PPPoE --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-black text-[#09265E] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-[#0866F5]">devices</span> 5. ONT, Wi-Fi & PPPoE
                        </span>
                        <button type="button" wire:click="goToStep(5)" class="text-[11px] font-bold text-[#0866F5] hover:underline flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">edit</span> Ubah
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Serial Number (SN)</span>
                            <span class="font-mono font-black text-[#0866F5]">{{ $onu_sn }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Wi-Fi SSID</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ !empty(trim($wifi_ssid)) ? $wifi_ssid : ('WINETS-' . substr($onu_sn, -4)) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">PPPoE Username</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ !empty(trim($custom_username)) ? $custom_username : '(Otomatis Sistem)' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">PPPoE Password</span>
                            <span class="font-mono text-slate-700 dark:text-slate-300">{{ !empty(trim($custom_password)) ? '••••••' : 'pppoe' }}</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] uppercase font-bold">Injeksi OMCI TR-069 (OLT)</span>
                            <span class="font-bold text-xs {{ $enable_tr069_iphost ? 'text-[#16A36A]' : 'text-slate-500' }}">
                                @if($enable_tr069_iphost)
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-[#16A36A]"></span>
                                        VLAN {{ $tr069_vlan }} ({{ strtoupper($tr069_mode) }})
                                    </span>
                                @else
                                    <span class="text-slate-400">Nonaktif</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Group 7 & 8: Redaman & Material --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">7. Redaman RX</span>
                        <span class="text-sm font-black {{ ((float)$optical_power >= -24 && (float)$optical_power <= -12) ? 'text-[#16A36A]' : 'text-amber-600' }}">
                            {{ !empty($optical_power) ? $optical_power . ' dBm' : 'Belum diukur' }}
                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-[#DCE6F5] dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">8. Material</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ (int)$dropcore_meters }}m Dropcore • {{ (int)$fast_connector_qty }} Fast Conn
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/40 text-[#09265E] dark:text-blue-200 rounded-2xl border border-blue-200 dark:border-blue-800/50 flex gap-2.5 mb-4">
                <span class="material-symbols-outlined text-[#0866F5] shrink-0 mt-0.5" style="font-size:20px">verified_user</span>
                <p class="text-xs leading-relaxed">Setelah menekan tombol aktivasi, sistem akan menjalankan registrasi OLT port PON, membuat profil WAN PPPoE di MikroTik, serta mengirimkan provisioning TR-069 ke GenieACS.</p>
            </div>

            @if(session('error') || $errors->any())
                <div class="mb-4 p-3 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 rounded-2xl border border-rose-200 dark:border-rose-800/50 flex gap-2" x-data x-init="$el.scrollIntoView({behavior: 'smooth', block: 'center'})">
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
                <button type="button" wire:click="previousStep" class="w-1/3 py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">
                    Kembali
                </button>
            @endif
            @if($currentStep < 6)
                <button type="button" wire:click="nextStep" style="background-color: #0866F5 !important; color: #ffffff !important;" class="{{ $currentStep > 1 ? 'w-2/3' : 'w-full' }} py-3.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 active:bg-blue-800 flex items-center justify-center gap-2 shadow-md shadow-blue-500/20 active:scale-95 transition-all">
                    <span>Lanjut</span>
                    <span class="material-symbols-outlined shrink-0" style="font-size:18px">arrow_forward</span>
                </button>
            @else
                <button type="button" wire:click="submit" wire:loading.attr="disabled" style="background-color: #16A36A !important; color: #ffffff !important;" class="w-2/3 py-3.5 bg-emerald-600 text-white rounded-xl font-black text-sm hover:bg-emerald-700 active:bg-emerald-800 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20">
                    <span wire:loading.remove wire:target="submit" class="flex items-center gap-2 justify-center">
                        <span class="material-symbols-outlined shrink-0" style="font-size:18px">rocket_launch</span>
                        AKTIFKAN SEKARANG
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2 justify-center">
                        <span class="material-symbols-outlined animate-spin shrink-0" style="font-size:18px">sync</span>
                        Memproses Aktivasi...
                    </span>
                </button>
            @endif
        </div>
        @endif
    </div>
    @endif

</div>
