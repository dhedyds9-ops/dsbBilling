@section('page_title')
    <a href="{{ route('isp.odcs.index') }}" class="inline-flex items-center justify-center p-1.5 mr-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">add_circle</span>
    <span class="text-lg">Tambah ODC Baru</span>
@endsection

<div class="space-y-5 pb-10 max-w-4xl">
    {{-- SESSION FLASH & VALIDATION --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm">
            <span class="material-symbols-outlined notranslate mt-0.5" translate="no" style="font-size:18px">error</span>
            <div>
                <p class="font-bold mb-1">Peringatan Validasi:</p>
                <ul class="list-disc pl-4 space-y-0.5 text-red-600 dark:text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save" class="p-5 space-y-6">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:20px">hub</span>
                            Informasi Optical Distribution Cabinet (ODC)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lengkapi identitas ODC, relasi OLT/POP induk, dan lokasi koordinat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode ODC <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="code" placeholder="Contoh: ODC-GND-01" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama ODC <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Contoh: ODC Utama Gandasoli" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">OLT Induk</label>
                        <select wire:model.live="olt_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Pilih OLT --</option>
                            @foreach($olts as $olt)
                                <option value="{{ $olt->id }}">{{ $olt->code }} - {{ $olt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cabang / Wilayah Kantor <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <select wire:model="branch_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Tanpa Cabang Khusus / Global --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">POP / Shelter <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <select wire:model="pop_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Pilih POP --</option>
                            @foreach($pops as $pop)
                                <option value="{{ $pop->id }}">{{ $pop->code }} - {{ $pop->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Port</label>
                        <input type="number" wire:model="port_count" placeholder="Contoh: 48 / 96 / 144" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <x-forms.coordinate-picker latModel="latitude" lngModel="longitude" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lokasi</label>
                        <textarea wire:model="address" rows="2" placeholder="Masukkan alamat lengkap lokasi ODC" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Catatan</label>
                        <textarea wire:model="description" rows="2" placeholder="Catatan teknis atau keterangan tambahan..." class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.odcs.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 min-w-[130px]">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
