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

            <!-- Jabatan & Divisi -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Posisi / Jabatan Pekerjaan</label>
                    @if($position)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Divisi: {{ \App\Enums\JobFunction::resolveDivision($position) }}
                        </span>
                    @endif
                </div>
                <select wire:model.live="position"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Posisi / Jabatan --</option>
                    @foreach(\App\Enums\JobFunction::groupedPositions() as $divName => $posList)
                        <optgroup label="{{ $divName }}">
                            @foreach($posList as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                    @if($position && !in_array($position, \App\Enums\JobFunction::allPositions()))
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
                        <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }} ({{ $jobFunc->detailedLabel() }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Otomatis terisi sesuai posisi yang dipilih dan tersinkron ke akun login.</p>
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
                    <div class="mt-1.5 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs">
                        <div class="text-emerald-700 dark:text-emerald-300 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Akun Login Tertaut: <b>{{ $employee->user->name }}</b>
                        </div>
                        <div class="text-slate-600 dark:text-slate-400 mt-1.5 flex flex-wrap items-center gap-3">
                            <span>Username: <code class="font-bold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">{{ $employee->user->username ?? $employee->user->email }}</code></span>
                            <span>Password Default: <code class="font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700">Password123!</code></span>
                        </div>
                        
                        <!-- Tombol Kirim WhatsApp -->
                        <div class="mt-2.5 pt-2 border-t border-emerald-200/70 dark:border-emerald-800/60 flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="sendWhatsAppCredentials" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">send</span>
                                <span wire:loading.remove wire:target="sendWhatsAppCredentials">Kirim Otomatis via Gateway</span>
                                <span wire:loading wire:target="sendWhatsAppCredentials">Mengirim via Gateway...</span>
                            </button>
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string)($phone ?: ($employee->phone ?? '')));
                                if (str_starts_with($cleanPhone, '08')) {
                                    $cleanPhone = '628' . substr($cleanPhone, 2);
                                } elseif (str_starts_with($cleanPhone, '8')) {
                                    $cleanPhone = '628' . substr($cleanPhone, 1);
                                } elseif (str_starts_with($cleanPhone, '6208')) {
                                    $cleanPhone = '628' . substr($cleanPhone, 4);
                                }
                                $manualWaText = rawurlencode("Halo *{$name}*,\n\nBerikut informasi akun login sistem Anda:\n━━━━━━━━━━━━━━━━━━━━\n🏢 Aplikasi: dsBilling & MStore\n👤 Nama: {$name}\n💼 Jabatan: " . ($position ?: 'Staff') . "\n🌐 Link Login: " . url('/login') . "\n\n🔐 Kredensial Login:\n• Username: *" . ($employee->user?->username ?? $employee->user?->email ?? $email) . "*\n• Password: *Password123!*\n━━━━━━━━━━━━━━━━━━━━\nSilakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan, segera ganti password Anda setelah login pertama kali.\n\nTerima kasih.");
                            @endphp
                            @if($cleanPhone)
                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $manualWaText }}" target="_blank"
                                   title="Buka WhatsApp Web secara manual jika Gateway server sedang offline"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5 fill-current text-emerald-600" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                    Buka WhatsApp Web Manual
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-[11px] text-slate-400 mt-1">Pilih user dari daftar atau klik tombol <b>"+ Buat Akun Login Langsung"</b> di atas (Password default: <code>Password123!</code>).</p>
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
