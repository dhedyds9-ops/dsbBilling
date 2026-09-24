@section('page_title')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        </a>
        <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
        </div>
        <span class="text-lg">Edit User</span>
    </div>
@endsection

<div class="max-w-4xl mx-auto space-y-6 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Informasi User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Ubah detail profil dan hak akses untuk {{ $user->name }}.</p>
        </div>
        
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">No. Identitas (KTP/SIM) *</label>
                    <input type="text" wire:model="identity_number" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('identity_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Job Title</label>
                    <input type="text" wire:model="job_title" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('job_title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Job Function (Departemen)</label>
                    <select wire:model="job_function" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Tidak Ada --</option>
                        @foreach(\App\Enums\JobFunction::cases() as $jobFunc)
                            <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                        @endforeach
                    </select>
                    @error('job_function') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email *</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor WhatsApp</label>
                    <input type="text" wire:model="whatsapp" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
                    @error('whatsapp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Lengkap</label>
                <textarea wire:model="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100"></textarea>
                @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">key</span>
                    Kredensial Login
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username *</label>
                        <input type="text" wire:model="username" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 dark:text-slate-100 opacity-70 dark:bg-slate-900 dark:text-slate-100" readonly>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Username tidak dapat diubah setelah dibuat.</p>
                        @error('username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password Baru</label>
                        <div class="relative">
                            <input x-ref="pwInput" :type="show ? 'text' : 'password'" wire:model="password" class="w-full px-4 py-2.5 pr-20 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Kosongkan jika tidak diubah">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
                                <button type="button" @click="show = !show" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors" title="Lihat Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" x-text="show ? 'visibility_off' : 'visibility'" translate="no">visibility</span>
                                </button>
                                <button type="button" @click="navigator.clipboard.writeText($refs.pwInput.value); window.toast ? window.toast('Password disalin') : alert('Password disalin!')" class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none transition-colors" title="Salin Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" translate="no">content_copy</span>
                                </button>
                            </div>
                        </div>
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ showConfirm: false }">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konfirmasi Password</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" wire:model="password_confirmation" class="w-full px-4 py-2.5 pr-12 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 placeholder-slate-400" placeholder="Ketik ulang password baru">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                <button type="button" @click="showConfirm = !showConfirm" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors" title="Lihat Password">
                                    <span class="material-symbols-outlined notranslate text-[20px]" x-text="showConfirm ? 'visibility_off' : 'visibility'" translate="no">visibility</span>
                                </button>
                            </div>
                        </div>
                        @error('password_confirmation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-6 mt-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">admin_panel_settings</span>
                    Hak Akses (Role)
                </h3>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Pilih Jabatan (Pilih salah satu) *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($roles as $role)
                            <label class="relative flex cursor-pointer p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors {{ $selectedRoles == $role->id ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-500 dark:border-indigo-500 ring-1 ring-indigo-500' : 'bg-white dark:bg-slate-800' }}">
                                <input type="radio" wire:model="selectedRoles" value="{{ $role->id }}" class="sr-only">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ ucfirst($role->name) }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        @if($role->name == 'administrator') Akses penuh
                                        @elseif($role->name == 'manager') Staff internal
                                        @elseif($role->name == 'reseller') Partner bisnis
                                        @else Akses standar
                                        @endif
                                    </span>
                                </div>
                                @if($selectedRoles == $role->id)
                                    <span class="absolute top-4 right-4 material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no" style="font-size:20px">check_circle</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('selectedRoles') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
                </div>
                
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-600 dark:bg-slate-900 dark:text-slate-100">
                    <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Akun Aktif</label>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-6 mt-2 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>