<div>
  @include('livewire.acs._tabs')

  <div class="space-y-5 pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">refresh</span>
                Refresh Data
            </button>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Device</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Type</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Created</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                {{ $task->device?->serial_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ ucfirst($task->type) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider 
                                    @if($task->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400
                                    @elseif($task->status === 'running') bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400
                                    @elseif($task->status === 'failed') bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400
                                    @endif
                                ">
                                    {{ $task->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-mono">
                                {{ $task->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="delete({{ $task->id }})" wire:confirm="Yakin ingin menghapus task ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">pending_actions</span>
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">Belum ada Task</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($tasks, 'hasPages') && $tasks->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
  </div>
</div>