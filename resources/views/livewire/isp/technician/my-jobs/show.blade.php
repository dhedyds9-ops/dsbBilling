<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Detail Pekerjaan #{{ $jobId }}</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Informasi lengkap tugas lapangan.</p>
        </div>
        <div class="flex gap-3">
            <x-ui.button wire:click="startJob" variant="primary">Mulai Pekerjaan</x-ui.button>
            <x-ui.button wire:click="completeJob" variant="success">Selesai</x-ui.button>
        </div>
    </div>
    <x-ui.card>
        <div class="p-6 text-center text-slate-500 dark:text-slate-400">
            Detail job akan ditampilkan di sini.
        </div>
    </x-ui.card>
</div>
