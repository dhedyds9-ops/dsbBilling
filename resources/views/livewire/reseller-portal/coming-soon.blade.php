@section('page_title')
    <div class="flex items-center gap-2">
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $pageName }}</h1>
    </div>
@endsection

<div class="p-6 bg-slate-50 dark:bg-slate-900/30 min-h-[60vh] flex flex-col items-center justify-center text-center">
    <span class="material-symbols-outlined text-[64px] text-slate-300 dark:text-slate-700 dark:text-slate-300 mb-4">construction</span>
    <h2 class="text-2xl font-bold text-slate-700 dark:text-slate-300 mb-2">Modul Sedang Dibangun</h2>
    <p class="text-slate-500 dark:text-slate-400 max-w-md">Halaman <strong>{{ $pageName }}</strong> untuk Reseller Portal saat ini belum tersedia dan masih dalam tahap pengembangan.</p>
</div>
