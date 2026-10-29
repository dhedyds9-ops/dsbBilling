<div class="space-y-6 pb-12 max-w-4xl">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employee.index') }}" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Data Pegawai</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Isi formulir lengkap untuk mendaftarkan staf / teknisi baru.</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-300 text-sm">
            <p class="font-bold mb-1">Terdapat kesalahan pada isian formulir:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- NIK -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">NIK (Nomor Induk Karyawan) <span class="text-red-500">*</span></label>
                <input type="text" wire:model="nik" placeholder="Contoh: EMP-2026-001"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('nik') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nama Pegawai -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" wire:model.live.debounce.400ms="name" placeholder="Nama lengkap pegawai"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Jabatan -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Posisi / Jabatan Pekerjaan</label>
                <select wire:model.live="position"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Posisi / Jabatan --</option>
                    @foreach(\App\Enums\JobFunction::ispPositions() as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                    @if($position && !in_array($position, \App\Enums\JobFunction::ispPositions()))
                        <option value="{{ $position }}" selected>{{ $position }} (Kustom)</option>
                    @endif
                </select>
                @error('position') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Departemen (Job Function) -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Job Function (Departemen / Fungsi Tugas)</label>
                <select wire:model="department"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Fungsi Tugas --</option>
                    @foreach(\App\Enums\JobFunction::cases() as $jobFunc)
                        <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Otomatis terisi sesuai posisi yang dipilih dan tersinkron ke akun login.</p>
                @error('department') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Telepon / WhatsApp -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor HP / WhatsApp</label>
                <input type="text" wire:model="phone" placeholder="08xxxxxxxxxx"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Email</label>
                <input type="email" wire:model="email" placeholder="email@perusahaan.com"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tanggal Bergabung -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Tanggal Bergabung</label>
                <input type="date" wire:model="join_date"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('join_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Gaji Pokok -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Gaji Pokok (Rp)</label>
                <input type="number" wire:model="base_salary" step="1000" placeholder="Contoh: 4500000"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('base_salary') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nama Bank -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Bank</label>
                <input type="text" wire:model="bank_name" placeholder="BCA / Mandiri / BRI / BNI"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('bank_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nomor Rekening -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor Rekening</label>
                <input type="text" wire:model="bank_account" placeholder="Nomor rekening transfer"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('bank_account') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Status -->
            <div class="col-span-1 md:col-span-2">
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status Kepegawaian <span class="text-red-500">*</span></label>
                <select wire:model="status"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif / Resign</option>
                </select>
                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Integrasi Akun Login User -->
            <div class="col-span-1 md:col-span-2 p-5 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">manage_accounts</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Akun Login & Hak Akses Pengguna</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Otomatis buatkan user login baru untuk pegawai ini atau hubungkan ke akun terdaftar.</p>
                        </div>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model.live="create_user_account" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                        <span class="ms-3 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ $create_user_account ? 'Buat Akun Baru' : 'Pilih Akun Terdaftar' }}
                        </span>
                    </label>
                </div>

                @if($create_user_account)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                        <!-- Username -->
                        <div>
                            <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="username" placeholder="Username login"
                                   class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                            <p class="text-[11px] text-slate-400 mt-1">Otomatis terisi dari nama pegawai.</p>
                            @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password Default -->
                        <div x-data="{ show: false }">
                            <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Password Default <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" wire:model="password"
                                       class="w-full px-4 py-2.5 pr-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <span class="material-symbols-outlined notranslate text-[18px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Default: Password123!</p>
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Role / Hak Akses <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="role_id"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->name) }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Default staf internal: Manager.</p>
                            @error('role_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @else
                    <div class="pt-1">
                        <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Pilih Akun Login Terdaftar
                        </label>
                        <select wire:model="user_id"
                                class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Belum Ditautkan --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pilih akun pengguna yang sudah ada untuk ditautkan dengan profil pegawai ini.</p>
                        @error('user_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.employee.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors">
                Batal
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class=" px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-all inline-flex items-center gap-2 cursor-pointer shadow-sm">
                <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                <span wire:loading.remove wire:target="save">Simpan Data Pegawai</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
