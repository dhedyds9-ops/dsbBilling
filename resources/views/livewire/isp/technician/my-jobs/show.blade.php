<div class="space-y-3.5 pb-6">
    @section('header_title', 'Detail Pekerjaan #' . $jobId)
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-white dark:bg-slate-900 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100">Detail Pekerjaan #{{ $jobId }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasi lengkap tugas operasional lapangan.</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <x-ui.button wire:click="startJob" variant="primary" class="flex-1 sm:flex-none">Mulai Pekerjaan</x-ui.button>
            <x-ui.button wire:click="completeJob" variant="success" class="flex-1 sm:flex-none">Selesai</x-ui.button>
        </div>
    </div>
    <x-ui.card>
        <div class="p-6 text-center text-slate-500 dark:text-slate-400 text-sm">
            Detail job akan ditampilkan di sini.
        </div>
    </x-ui.card>
</div>
