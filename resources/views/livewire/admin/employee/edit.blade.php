<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employee.index') }}" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Data Pegawai</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui profil, jabatan, rekening, dan status kepegawaian {{ $employee->name }}.</p>
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
                <input type="text" wire:model="nik"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('nik') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nama Pegawai -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Jabatan -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Posisi / Jabatan</label>
                <input type="text" wire:model="position"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('position') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Departemen (Job Function) -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Departemen / Fungsi Tugas</label>
                <select wire:model="department"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach(\App\Enums\JobFunction::cases() as $jobFunc)
                        <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                    @endforeach
                </select>
                @error('department') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Telepon / WhatsApp -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor HP / WhatsApp</label>
                <input type="text" wire:model="phone"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Email</label>
                <input type="email" wire:model="email"
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
                <input type="number" wire:model="base_salary" step="1000"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('base_salary') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nama Bank -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Bank</label>
                <input type="text" wire:model="bank_name"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('bank_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nomor Rekening -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor Rekening</label>
                <input type="text" wire:model="bank_account"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                @error('bank_account') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tautkan Akun User Login -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Tautkan Akun Login (User Management)
                    </label>
                    @if(empty($user_id))
                        <button type="button" wire:click="createUserAccount" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                            <span wire:loading.remove wire:target="createUserAccount">+ Buat Akun Login Langsung</span>
                            <span wire:loading wire:target="createUserAccount">Membuat Akun...</span>
                        </button>
                    @endif
                </div>
                <select wire:model="user_id"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Belum Ditautkan --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username ?? $user->email }})</option>
                    @endforeach
                </select>
                @if($user_id && $employee->user)
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-medium">
                        ✓ Akun login tertaut: <b>{{ $employee->user->name }} ({{ $employee->user->username ?? $employee->user->email }})</b>
                    </p>
                @else
                    <p class="text-[11px] text-slate-400 mt-1">Pilih user dari daftar atau klik tombol <b>"+ Buat Akun Login Langsung"</b> di atas.</p>
                @endif
                @error('user_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status Kepegawaian <span class="text-red-500">*</span></label>
                <select wire:model="status"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif / Resign</option>
                </select>
                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.employee.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer">
                Perbarui Data Pegawai
            </button>
        </div>
    </form>
</div>
