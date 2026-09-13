<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">ACS Logs</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lihat log aktivitas ACS</p>
        </div>
    </div>

    {{-- Data Table --}}
    <x-base.card :padding="false">
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $log->device?->serial_number ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $log->message }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="delete({{ $log->id }})" wire:confirm="Yakin ingin menghapus log ini?" class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg">Belum ada log</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-base.card>
</div>
