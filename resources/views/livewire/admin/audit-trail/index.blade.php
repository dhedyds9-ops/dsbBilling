<div>
  {{-- Header --}}
  <x-admin.page-header title="Audit Trail" subtitle="Log semua aktivitas pengguna dan sistem" />

  {{-- Search Bar --}}
  <x-base.card class="mb-3">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <input
          type="search"
          wire:model.live.debounce.400ms="search"
          placeholder="Cari event- model- user..."
          class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100"
        />
      </div>
    </div>
  </x-base.card>

  {{-- Table --}}
  <x-admin.table-card>
    <x-ui.table>
      <thead>
        <tr class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wider">
          <th class="px-4 py-2 rounded-l-lg">Waktu</th>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Event</th>
          <th class="px-4 py-2">Model</th>
          <th class="px-4 py-2 rounded-r-lg">ID Record</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        @forelse($auditLogs as $log)
          <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 transition-colors">
            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs">
              {{ optional($log->created_at)->format('d/m/Y H:i:s') }}
            </td>
            <td class="px-4 py-2.5">
              <span class="text-slate-700 dark:text-slate-200 font-medium text-xs">
                {{ $log->user_id ?? 'System' }}
              </span>
            </td>
            <td class="px-4 py-2.5">
              @php
                $evtColor = match(strtolower($log->event ?? '')) {
                  'created'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                  'updated'  => 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
                  'deleted'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                  default    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                };
              @endphp
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $evtColor }}">
                {{ $log->event ?? ',' }}
              </span>
            </td>
            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300 text-xs font-mono">
              {{ class_basename($log->model ?? ',') }}
            </td>
            <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400 text-xs font-mono">
              {{ $log->model_id ?? ',' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500 dark:text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Tidak ada log audit ditemukan</span>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </x-ui.table>

  {{-- Pagination --}}
  @if($auditLogs->hasPages())
    <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-700">
      {{ $auditLogs->links() }}
    </div>
  @endif
  </x-admin.table-card>
</div>






