@section('page_title')
    <a href="{{ route('isp.odps.index') }}" class="inline-flex items-center justify-center p-1.5 mr-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">edit</span>
    <span class="text-lg">Edit ODP: {{ $odp->name }}</span>
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
                            <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:20px">share</span>
                            Informasi Optical Distribution Point (ODP)
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Perbarui data ODP, ODC induk, wilayah administratif, dan titik koordinat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode ODP <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="code" placeholder="Contoh: ODP-GND-01" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama ODP <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="Contoh: ODP Tiang RT 02" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ODC Induk</label>
                        <select wire:model.live="odc_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Pilih ODC --</option>
                            @foreach($odcs as $odc)
                                <option value="{{ $odc->id }}">{{ $odc->code }} - {{ $odc->name }}</option>
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
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kecamatan (District) <span class="text-xs text-slate-400 font-normal">(Pencocokan Wilayah)</span></label>
                        <input type="text" wire:model="district" placeholder="Contoh: Girimarto / Purwantoro" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kelurahan / Desa (Village)</label>
                        <input type="text" wire:model="village" placeholder="Contoh: Tambakmerang" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kabupaten / Kota (Regency)</label>
                        <input type="text" wire:model="regency" placeholder="Contoh: Wonogiri" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Provinsi</label>
                        <input type="text" wire:model="province" placeholder="Contoh: Jawa Tengah" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Port</label>
                        <input type="number" wire:model="port_count" placeholder="Contoh: 8 / 16" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
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
                        <textarea wire:model="address" rows="2" placeholder="Masukkan alamat lengkap lokasi ODP" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Catatan</label>
                        <textarea wire:model="description" rows="2" placeholder="Catatan splitter atau keterangan tambahan..." class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-500 text-lg">add_a_photo</span>
                                Foto Fisik Tiang / Box ODP
                            </span>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-normal">Tersedia geser, zoom & pangkas (crop)</span>
                        </label>

                        @if(!empty($existingPhotos))
                            <div class="mb-4 p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $existingPhotos[0] }}" class="h-20 w-28 object-cover rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 cursor-pointer" onclick="window.open('{{ $existingPhotos[0] }}', '_blank')" onerror="this.onerror=null; this.src='/node-photo/odp/{{ $odpId }}';" title="Klik untuk melihat foto ukuran penuh">
                                    <div>
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block mb-0.5">Foto Tiang Saat Ini</span>
                                        <span class="text-[11px] text-slate-400 block">Foto ini tampil di Peta GIS & Portal Teknisi. Pilih berkas baru di bawah jika ingin mengganti.</span>
                                    </div>
                                </div>
                                <button type="button" wire:click="deletePhoto" wire:confirm="Hapus foto dokumentasi tiang ODP ini?" class="px-3.5 py-2 bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer self-start sm:self-auto shrink-0">
                                    <span class="material-symbols-outlined text-sm">delete</span> Hapus Foto
                                </button>
                            </div>
                        @endif

                        <input type="file" id="odpFileInput" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200 cursor-pointer">
                        <p class="text-xs text-slate-400 mt-1">Pilih foto kamera atau galeri untuk dokumentasi tiang/box ODP di lapangan.</p>
                        @error('photo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                        {{-- Cropper Container --}}
                        <div id="odpCropperWrapper" class="hidden mt-4 space-y-2 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-indigo-200 dark:border-indigo-900">
                            <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">crop</span>
                                Sesuaikan & Pangkas Foto Baru (Crop Image):
                            </span>
                            <div class="max-h-72 overflow-hidden rounded-lg bg-slate-950 border border-slate-300 dark:border-slate-700 flex items-center justify-center">
                                <img id="odpCropperImage" class="max-w-full block">
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-xs">
                                <span class="text-[11px] text-slate-500">Atur orientasi & ukuran:</span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" id="btnOdpRotate" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-100 transition shadow-2xs">
                                        🔄 Putar
                                    </button>
                                    <button type="button" id="btnOdpZoomIn" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-100 transition shadow-2xs">
                                        🔍+
                                    </button>
                                    <button type="button" id="btnOdpZoomOut" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-100 transition shadow-2xs">
                                        🔍-
                                    </button>
                                    <button type="button" id="btnOdpReset" class="px-2.5 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-100 transition shadow-2xs">
                                        🔁 Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.odps.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="button" id="btnSaveOdp" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 min-w-[130px]">
                    <span id="btnSaveIcon" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span id="btnSaveText">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let odpCropper = null;
            const fileInput = document.getElementById('odpFileInput');
            const wrapper = document.getElementById('odpCropperWrapper');
            const img = document.getElementById('odpCropperImage');
            const btnSave = document.getElementById('btnSaveOdp');
            const btnSaveText = document.getElementById('btnSaveText');
            const btnSaveIcon = document.getElementById('btnSaveIcon');

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (wrapper) wrapper.classList.remove('hidden');
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        if (odpCropper) {
                            odpCropper.destroy();
                            odpCropper = null;
                        }
                        img.src = evt.target.result;
                        if (typeof Cropper !== 'undefined') {
                            odpCropper = new Cropper(img, {
                                aspectRatio: 16 / 10,
                                viewMode: 1,
                                autoCropArea: 0.95,
                                responsive: true,
                                restore: false,
                            });
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            document.getElementById('btnOdpRotate')?.addEventListener('click', function() {
                if (odpCropper) odpCropper.rotate(90);
            });
            document.getElementById('btnOdpZoomIn')?.addEventListener('click', function() {
                if (odpCropper) odpCropper.zoom(0.1);
            });
            document.getElementById('btnOdpZoomOut')?.addEventListener('click', function() {
                if (odpCropper) odpCropper.zoom(-0.1);
            });
            document.getElementById('btnOdpReset')?.addEventListener('click', function() {
                if (odpCropper) odpCropper.reset();
            });

            if (btnSave) {
                btnSave.addEventListener('click', function() {
                    btnSave.disabled = true;
                    if (btnSaveText) btnSaveText.innerText = 'Menyimpan...';
                    if (btnSaveIcon) {
                        btnSaveIcon.classList.add('animate-spin');
                        btnSaveIcon.innerText = 'autorenew';
                    }

                    if (odpCropper) {
                        try {
                            const canvas = odpCropper.getCroppedCanvas({
                                maxWidth: 1200,
                                maxHeight: 800,
                                fillColor: '#fff',
                                imageSmoothingEnabled: true,
                                imageSmoothingQuality: 'high',
                            });
                            const base64 = canvas.toDataURL('image/jpeg', 0.82);
                            @this.set('croppedPhotoData', base64).then(() => {
                                @this.save();
                            });
                            return;
                        } catch (err) {
                            console.error('Cropper export failed:', err);
                        }
                    }

                    // Fallback to normal upload or text fields save
                    if (fileInput && fileInput.files && fileInput.files[0]) {
                        @this.upload('photo', fileInput.files[0], () => {
                            @this.save();
                        });
                    } else {
                        @this.save();
                    }
                });
            }
        });
    </script>
</div>
