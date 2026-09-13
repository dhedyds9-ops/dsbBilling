@section('header_title', 'Informasi')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)]">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-4">Pusat Informasi & Notifikasi</h3>
        
        <div class="space-y-4">
            @forelse($notifications as $notif)
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-full {{ $notif->type === 'system' ? 'bg-blue-100 dark:bg-blue-900/50 text-primary-600' : 'bg-teal-100 dark:bg-teal-900/50 text-teal-600' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">{{ $notif->type === 'system' ? 'campaign' : 'notifications' }}</span>
                </div>
                <div>
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200">{{ $notif->title }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $notif->message }}</p>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <div class="text-center py-6">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada informasi atau notifikasi.</p>
            </div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    </div>
</div>






