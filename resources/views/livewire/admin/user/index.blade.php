@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">manage_accounts</span>
        </div>
        <span class="text-lg">Manajemen User</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
            </div>
            
            <button wire:click="resetFilters" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 font-medium hidden sm:block">
                Reset
            </button>
        </div>

        <div class="flex items-center gap-3">
            {{-- Per Page Selector --}}
            <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="all">Semua</option>
            </select>
            
            <button wire:click="export" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                Export
            </button>

            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">person_add</span>
                Tambah User
            </a>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Nama</th>
                        <th class="px-6 py-4">Fungsi / Job</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Role & Hak Akses</th>
                        <th class="px-6 py-4">Terdaftar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 group transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0">
                                        {{ substr($user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $user->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700 dark:text-slate-300">
                                    {{ $user->job_function ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->job_title ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-600 dark:text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">call</span>
                                    {{ $user->whatsapp ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $user->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/30 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/30 dark:hover:bg-amber-900/30 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/30 flex items-center justify-center transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">group_off</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Tidak ada User</p>
                                <p class="text-slate-500 dark:text-slate-400 mt-1">Belum ada data staf atau user yang terdaftar.</p>
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
</div>