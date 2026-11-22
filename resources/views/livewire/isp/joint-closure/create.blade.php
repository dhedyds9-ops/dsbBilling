@section('page_title')
    <a href="{{ route('isp.joint-closures.index') }}" class="inline-flex items-center justify-center p-1.5 mr-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">archive</span>
    <span class="text-lg">Tambah Joint Closure Baru</span>
@endsection

<div class="space-y-5 pb-10 max-w-4xl">
    {{-- SESSION FLASH & VALIDATION --}}
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save" class="p-5 sm:p-6 space-y-6">
            {{-- 1. IDENTITAS & KAPASITAS CORE --}}
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size:20px">inventory_2</span>
                            Identitas & Kapasitas Joint Closure
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan kode, nama, model enclosure, dan kapasitas core sambungan fiber optik.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Closure <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="code" placeholder="Contoh: JC-DOME-01" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Closure <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Contoh: JC Tiang 14 Gandasoli" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Model Box Enclosure <span class="text-red-500">*</span></label>
                        <select wire:model.live="type" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer font-medium">
                            <option value="Dome">Dome (Model Kubah / Vertikal Tiang)</option>
                            <option value="Inline">Inline (Model Pipih / Horizontal Jalur Kabel)</option>
                            <option value="Wall Mount">Wall Mount (Dinding / Handhole Manhole)</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kapasitas Core Optik <span class="text-red-500">*</span></label>
                        <select wire:model.live="port_count" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer font-bold">
                            <option value="4">4 Core (Mini Drop / Branch)</option>
                            <option value="6">6 Core</option>
                            <option value="8">8 Core</option>
                            <option value="12">12 Core (Inline Mini)</option>
                            <option value="24">24 Core (Standard Dome / Inline)</option>
                            <option value="48">48 Core</option>
                            <option value="96">96 Core (Backbone Feeder)</option>
                            <option value="144">144 Core (High Capacity)</option>
                            <option value="custom">-- Custom Kapasitas Core --</option>
                        </select>
                        @error('port_count') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        @if($port_count === 'custom')
                            <div class="mt-2">
                                <input type="number" wire:model="custom_port_count" placeholder="Masukkan jumlah core (misal: 36, 72, 288)" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-indigo-400 rounded-lg text-sm font-bold dark:text-slate-100">
                                @error('custom_port_count') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2. RELASI JALUR UPLINK (INDUK) --}}
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500" style="font-size:20px">alt_route</span>
                            Relasi Jalur Uplink (Koneksi Induk)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih sumber tarikan kabel optik yang masuk ke joint closure ini.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe Jalur Uplink <span class="text-red-500">*</span></label>
                        <select wire:model.live="uplink_type" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer font-medium">
                            <option value="olt">Uplink Feeder dari Core OLT (Backbone Trunk)</option>
                            <option value="closure">Cascade dari Parent Closure (Sambungan Berantai)</option>
                            <option value="odc">Uplink Distribusi dari ODC Kabinet</option>
                        </select>
                    </div>

                    @if($uplink_type === 'olt')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">OLT Induk</label>
                            <select wire:model.live="olt_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                <option value="">-- Pilih OLT Core --</option>
                                @foreach($olts as $o)
                                    <option value="{{ $o->id }}">{{ $o->code }} - {{ $o->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Port PON OLT</label>
                            <input type="text" wire:model="pon_port" placeholder="Contoh: PON 1 atau 0/1/1" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono dark:text-slate-100">
                        </div>
                    @elseif($uplink_type === 'closure')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Parent Joint Closure (Induk)</label>
                            <select wire:model.live="parent_closure_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                <option value="">-- Pilih Parent Closure --</option>
                                @foreach($parentClosures as $pc)
                                    <option value="{{ $pc->id }}">{{ $pc->code }} - {{ $pc->name }} ({{ $pc->port_count }} Core)</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($uplink_type === 'odc')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ODC Induk</label>
                            <select wire:model.live="odc_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                                <option value="">-- Pilih ODC Distribusi --</option>
                                @foreach($odcs as $odc)
                                    <option value="{{ $odc->id }}">{{ $odc->code }} - {{ $odc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kantor Cabang</label>
                        <select wire:model="branch_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Kantor Cabang Utama --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status Operasional</label>
                        <select wire:model="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer font-semibold">
                            <option value="active">Aktif (Beroperasi Normal)</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 3. LOKASI, KOORDINAT & FOTO DOKUMENTASI TIANG --}}
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500" style="font-size:20px">pin_drop</span>
                            Lokasi & Foto Dokumentasi Tiang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Catat koordinat GPS dan foto fisik tiang untuk mempermudah penanganan gangguan.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat / Patokan Lokasi Tiang</label>
                        <input type="text" wire:model="address" placeholder="Contoh: Depan Pos Ronda RT 03 / Tiang No. 12" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-slate-100">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Latitude</label>
                            <input type="text" wire:model="latitude" placeholder="-6.79980" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Longitude</label>
                            <input type="text" wire:model="longitude" placeholder="105.93870" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono dark:text-slate-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Foto Fisik Tiang / Closure Lapangan</label>
                        <input type="file" wire:model="photo" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 10MB.</p>
                        @if ($photo)
                            <div class="mt-2">
                                <img src="{{ $photo->temporaryUrl() }}" class="h-28 w-auto rounded-lg object-cover border border-slate-200 shadow-xs">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Catatan Splice Core</label>
                        <textarea wire:model="description" rows="3" placeholder="Contoh: Core 1-4 untuk ODP 01 s/d 04, Core 5-8 sambungan lurus ke ODC Timur..." class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm dark:text-slate-100"></textarea>
                    </div>
                </div>
            </div>

            {{-- 4. INTEGRASI LOGISTIK & STOK GUDANG --}}
            <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400" translate="no" style="font-size:20px">inventory_2</span>
                            Integrasi Gudang & Keseimbangan Stok Logistik
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Otomatis mutasikan Box Joint Closure dari gudang atau teknisi agar neraca stok selalu balance.</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sumber Pengambilan Material</label>
                            <select wire:model.live="source_type" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:text-slate-100 cursor-pointer">
                                <option value="none">Tanpa Potong Stok (Pencatatan GIS / Topologi Saja)</option>
                                <option value="warehouse">Potong Langsung dari Gudang (Warehouse)</option>
                                <option value="custody">Potong dari Bawaan Teknisi Lapangan (Custody)</option>
                            </select>
                        </div>

                        @if($source_type === 'warehouse')
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Gudang Asal <span class="text-red-500">*</span></label>
                                <select wire:model="warehouse_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:text-slate-100 cursor-pointer">
                                    <option value="">-- Pilih Gudang --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name ?? 'Pusat' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($source_type === 'custody')
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Teknisi Pemasang (Pemegang Custody) <span class="text-red-500">*</span></label>
                                <select wire:model="technician_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:text-slate-100 cursor-pointer">
                                    <option value="">-- Pilih Teknisi --</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    @if($source_type !== 'none')
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Master Barang Box Joint Closure <span class="text-red-500">*</span></label>
                            <select wire:model="item_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:text-slate-100 cursor-pointer">
                                <option value="">-- Pilih Item Joint Closure --</option>
                                @foreach($closureItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SUBMIT BUTTONS --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.joint-closures.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-indigo-200 dark:shadow-none transition-all cursor-pointer">
                    <span class="material-symbols-outlined notranslate" style="font-size:18px">save</span>
                    <span>Simpan Joint Closure</span>
                </button>
            </div>
        </form>
    </div>
</div>
