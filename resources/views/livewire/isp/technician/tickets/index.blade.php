@section('header_title', 'Tugas Troubleshooting')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24">
    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-6 bg-slate-200/50 dark:bg-slate-800/50 p-1 rounded-xl">
        <button wire:click="switchTab('open')" 
            class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all {{ $activeTab === 'open' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300' }}">
            Tugas Aktif
        </button>
        <button wire:click="switchTab('resolved')" 
            class="flex-1 text-sm font-semibold py-2 rounded-lg transition-all {{ $activeTab === 'resolved' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300' }}">
            Riwayat
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-xl flex gap-2 items-center text-sm border border-emerald-100">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($tickets as $ticket)
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
            @if($ticket->priority === 'critical' || $ticket->priority === 'high')
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
            @else
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500"></div>
            @endif

            <div class="pl-2">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm line-clamp-1 pr-2">#{{ $ticket->id }} - {{ $ticket->title }}</h3>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold 
                        {{ $ticket->status === 'resolved' || $ticket->status === 'closed' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700' }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $ticket->description }}</p>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 mb-3 border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">person</span>
                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $ticket->customer->name ?? 'Pelanggan' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">home</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $ticket->customer->address ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="text-[10px] text-slate-400">
                        Masuk: {{ $ticket->created_at->format('d M H:i') }}
                    </div>
                    
                    @if($activeTab === 'open')
                        <button wire:click="markResolved({{ $ticket->id }})" wire:confirm="Yakin ingin menyelesaikan tiket ini?" class="text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">task_alt</span>
                            Selesai
                        </button>
                    @else
                        <div class="text-[10px] text-emerald-500 font-medium">
                            Selesai: {{ $ticket->resolved_at ? $ticket->resolved_at->format('d M H:i') : '-' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3 block">assignment_turned_in</span>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Tidak ada tugas saat ini.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $tickets->links('pagination::tailwind') }}
    </div>
</div>
