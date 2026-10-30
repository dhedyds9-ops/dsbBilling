@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">manage_accounts</span>
        </div>
        <span class="text-lg">Manajemen User</span>
    </div>
@endsection

<div class="space-y-4 pb-10">
    {{-- Role Tabs Navigation --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-1.5 overflow-x-auto">
        <div class="flex items-center gap-1.5 min-w-max">
            {{-- All Roles --}}
            <button wire:click="setActiveRole('all')" 
                class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeRole === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">groups</span>
                <span>Semua Role</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeRole === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                    {{ $totalUsersCount }}
                </span>
            </button>

            {{-- Per Role Tab --}}
            @foreach($roleList as $r)
                @php
                    $roleIcon = match(strtolower($r['name'])) {
                        'administrator', 'super_admin', 'admin' => 'admin_panel_settings',
                        'manager' => 'badge',
                        'reseller' => 'storefront',
                        'technician' => 'engineering',
                        'noc', 'noc_operator' => 'terminal',
                        default => 'manage_accounts',
                    };
                @endphp
                <button wire:click="setActiveRole('{{ $r['name'] }}')" 
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeRole === $r['name'] ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">{{ $roleIcon }}</span>
                    <span>{{ $r['display_name'] }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeRole === $r['name'] ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        {{ $r['count'] }}
                    </span>
                </button>
            @endforeach

            {{-- Tab: Tanpa Role (jika ada) --}}
            @if($unassignedCount > 0)
                <button wire:click="setActiveRole('unassigned')" 
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeRole === 'unassigned' ? 'bg-amber-600 text-white shadow-sm' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30' }}">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">person_alert</span>
                    <span>Tanpa Role</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeRole === 'unassigned' ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300' }}">
                        {{ $unassignedCount }}
                    </span>
                </button>
            @endif
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, email, username..." 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                {{-- Job Function Filter --}}
                <select wire:model.live="filterJobFunction" class="w-full sm:w-auto py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100">
                    <option value="">Semua Job Function</option>
                    @foreach(\App\Enums\JobFunction::cases() as $jf)
                        <option value="{{ $jf->value }}">{{ $jf->label() }}</option>
                    @endforeach
                </select>

                {{-- Per Page Selector --}}
                <select wire:model.live="perPage" class="w-full sm:w-auto py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    <option value="10">10 / hal</option>
                    <option value="25">25 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                    <option value="all">Semua</option>
                </select>

                @if($search || $filterJobFunction !== '' || $activeRole !== 'all')
                <button wire:click="resetFilters" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-100 font-medium whitespace-nowrap">
                    Reset
                </button>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 w-full lg:w-auto">
            <a href="{{ route('admin.roles.index') }}" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">shield_person</span>
                Kelola Role
            </a>

            <button wire:click="export" wire:loading.attr="disabled" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap disabled:opacity-50">
                <span wire:loading.remove wire:target="export" class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400" translate="no" style="font-size:18px">download</span>
                <span wire:loading wire:target="export" class="material-symbols-outlined notranslate animate-spin text-indigo-600" translate="no" style="font-size:18px">autorenew</span>
                <span wire:loading.remove wire:target="export">Export Excel</span>
                <span wire:loading wire:target="export">Mengekspor Excel...</span>
            </button>

            <a href="{{ route('admin.users.create') }}" class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">person_add</span>
                Tambah User
            </a>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5 whitespace-nowrap w-32">Aksi</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Nama & Email</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Status Kepegawaian</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Username</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Status Akun</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Fungsi / Job</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Role & Hak Akses</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 group transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    {{-- Tombol WhatsApp --}}
                                    @php
                                        $rawPhone = $user->whatsapp ?: ($user->employee?->phone ?: ($user->phone ?? ''));
                                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
                                        if (str_starts_with($cleanPhone, '08')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 2);
                                        } elseif (str_starts_with($cleanPhone, '8')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 1);
                                        } elseif (str_starts_with($cleanPhone, '6208')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 4);
                                        }
                                        $roleLabel = $user->roles->pluck('display_name')->implode(', ') ?: ($user->roles->pluck('name')->implode(', ') ?: 'User');
                                        $jabatanLabel = $user->job_title ?: ($user->job_function ?: $roleLabel);
                                        $uName = $user->username ?: $user->email;
                                        $manualWaText = rawurlencode("Halo *{$user->name}*,\n\nBerikut informasi akun login sistem Anda:\n━━━━━━━━━━━━━━━━━━━━\n🏢 *Aplikasi:* dsBilling & MStore\n👤 *Nama:* {$user->name}\n💼 *Peran/Jabatan:* {$jabatanLabel}\n🌐 *Link Login:* " . url('/login') . "\n\n🔐 *Kredensial Login:*\n• Username / Email: *{$uName}*\n• Password: *Password123!*\n━━━━━━━━━━━━━━━━━━━━\nSilakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan akun, segera ganti password Anda setelah login pertama kali.\n\nTerima kasih.");
                                    @endphp

                                    @if($cleanPhone)
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors cursor-pointer"
                                                    title="Kirim Akun via WhatsApp ({{ $cleanPhone }})">
                                                <svg class="w-4 h-4 fill-current text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24">
                                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                 class="origin-top-left absolute left-0 mt-1 w-52 rounded-xl shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 z-50 py-1 text-xs">
                                                <button type="button" wire:click="sendWhatsAppCredentials({{ $user->id }})" @click="open = false"
                                                        class="w-full text-left px-3 py-2 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 flex items-center gap-2 cursor-pointer font-medium">
                                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">send</span> Kirim Otomatis (Gateway)
                                                </button>
                                                <a href="https://wa.me/{{ $cleanPhone }}?text={{ $manualWaText }}" target="_blank" @click="open = false"
                                                   class="w-full text-left px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2">
                                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">open_in_new</span> Buka WhatsApp Web
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" disabled
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600 cursor-not-allowed"
                                                title="Nomor WhatsApp tidak tersedia">
                                            <svg class="w-4 h-4 fill-current opacity-40" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <button wire:click="showSummary({{ $user->id }})" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center transition-colors" title="Lihat Detail / Ringkasan">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center transition-colors" title="Edit Akun">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:bg-red-900/30 flex items-center justify-center transition-colors" title="Hapus User">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0 text-sm">
                                        {{ substr($user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $user->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email ?? '-' }}</div>
                                        @if($user->wilayah || $user->branch)
                                            <div class="flex items-center gap-1.5 mt-1">
                                                @if($user->branch)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/50">
                                                        <span class="material-symbols-outlined notranslate text-[11px]" translate="no">store</span>
                                                        <span>{{ $user->branch->name }}</span>
                                                    </span>
                                                @endif
                                                @if($user->wilayah)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/50 dark:border-amber-800/50">
                                                        <span class="material-symbols-outlined notranslate text-[11px]" translate="no">location_on</span>
                                                        <span>{{ $user->wilayah }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($user->employee)
                                    <a href="{{ route('admin.employee.edit', $user->employee->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-colors" title="Buka data pegawai">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">badge</span>
                                        <span>Pegawai: {{ $user->employee->nik }}</span>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-normal text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                        Non-Pegawai
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-sm text-slate-700 dark:text-slate-300">
                                {{ $user->username ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-700 dark:text-slate-300">
                                    {{ $user->job_function ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->job_title ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">group_off</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">
                                    @if($activeRole === 'all')
                                        Tidak ada User
                                    @elseif($activeRole === 'unassigned')
                                        Tidak ada User Tanpa Role
                                    @else
                                        Tidak ada User dengan Role {{ ucfirst($activeRole) }}
                                    @endif
                                </p>
                                <p class="text-slate-500 dark:text-slate-400 mt-1">
                                    @if($search || $filterJobFunction)
                                        Tidak ditemukan data yang sesuai dengan filter pencarian.
                                    @else
                                        Belum ada data staf atau user pada kategori ini.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
    {{-- Summary Modal --}}
    @if($showSummaryModal && $summaryUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden" @click.away="$wire.closeSummaryModal()">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">analytics</span>
                        Ringkasan Laporan - [ {{ strtoupper($summaryUser->name) }} ]
                    </h3>
                    <button wire:click="closeSummaryModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                
                <div class="p-4">
                    @if($summaryUser->hasRole('reseller'))
                        <div class="space-y-6">
                            {{-- Keuangan --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Income Harian</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp. {{ number_format($summaryData['income_hari_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Income Bulan Ini</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp. {{ number_format($summaryData['income_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Fee Hari ini</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp. {{ number_format($summaryData['fee_hari_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Fee Bulan Ini</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp. {{ number_format($summaryData['fee_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                {{-- Voucher --}}
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3 border-b border-slate-200 dark:border-slate-700 pb-2">Data Voucher</h4>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Stok Voucher</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['stok_voucher'] ?? 0 }} pcs</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Voucher Expired</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['voucher_expired'] ?? 0 }} pcs</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Voucher Bulan ini</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['voucher_bulan_ini'] ?? 0 }} pcs</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                {{-- Pelanggan --}}
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3 border-b border-slate-200 dark:border-slate-700 pb-2">Data Pelanggan</h4>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Jumlah Pelanggan - HOTSPOT</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['jml_hotspot'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Jumlah Pelanggan - PPPoE</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['jml_ppp'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Pelanggan Isolir - HOTSPOT</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['isolir_hotspot'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Pelanggan Isolir - PPPoE</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['isolir_ppp'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Pelanggan Bulan ini - HOTSPOT</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['hotspot_bulan_ini'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Pelanggan Bulan ini - PPPoE</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['ppp_bulan_ini'] ?? 0 }} user</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-3" translate="no" style="font-size: 48px">info</span>
                            <p class="text-slate-500 dark:text-slate-400">Laporan statistik hanya tersedia untuk akun dengan role <span class="font-semibold text-slate-700 dark:text-slate-300">Reseller</span>.</p>
                        </div>
                    @endif
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end">
                    <button wire:click="closeSummaryModal" class="px-5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
