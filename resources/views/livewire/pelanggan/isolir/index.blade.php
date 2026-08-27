@php
  $sortIcon = function($field) {
    $dir = $this->sortField === $field ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '';
    return $dir ? " <span class='text-blue-600'>{$dir}</span>" : '';
  };
  $statusBadge = function($status, $since) {
    if (is_string($since) && $since !== '') {
      try { $d = \Illuminate\Support\Carbon::parse($since); } catch (\Throwable $e) { $d = null; }
    } else {
      $d = $since instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($since) : null;
    }
    $days = $d ? now()->diffInDays($d) : 0;
    $cls = $days >= 14 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'
         : ($days >= 7 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
         : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200');
    return "<span class='inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full {$cls}'>" . ($days > 0 ? "Isolir {$days}h" : 'Isolir') . "</span>";
  };
@endphp
<div>
  @include('partials.enterprise.list-toolbar', [
    'title' => 'Data Isolir Pelanggan',
    'primaryLabel' => null,
    'primaryAction' => null,
    'actions' => [
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
    ],
    'searchPlaceholder' => 'Cari username, nama, paket...',
    'showFiltersToggle' => true,
  ])

  @include('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total Terisolir', 'value' => number_format($this->summary['total'] ?? 0), 'color' => 'red', 'icon' => 'wifi-off'],
      ['label' => 'Hari Ini', 'value' => number_format($this->summary['today'] ?? 0), 'color' => 'amber', 'icon' => 'clock'],
      ['label' => 'Terlama Isolir', 'value' => $this->summary['terlama'] ?? '-', 'color' => 'slate', 'icon' => 'alert-triangle'],
    ],
  ])

  @if ($this->showFilters)
    @include('partials.enterprise.filters', [
      'filters' => [
        ['key' => 'router_id', 'label' => 'Router', 'type' => 'select', 'options' => $this->filterOptions['routers'] ?? []],
        ['key' => 'package_id', 'label' => 'Paket / Bandwidth', 'type' => 'select', 'options' => $this->filterOptions['packages'] ?? []],
        ['key' => 'sales_id', 'label' => 'Sales', 'type' => 'select', 'options' => $this->filterOptions['sales'] ?? []],
        ['key' => 'reseller_id', 'label' => 'Reseller', 'type' => 'select', 'options' => $this->filterOptions['resellers'] ?? []],
        ['key' => 'start_date', 'label' => 'Isolasi Sejak (Mulai)', 'type' => 'date'],
        ['key' => 'end_date', 'label' => 'Isolasi Sejak (Sampai)', 'type' => 'date'],
      ],
    ])
  @endif

  @include('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'activate', 'label' => 'Aktifkan', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
      ['key' => 'send-wa', 'label' => 'Kirim WA'],
      ['key' => 'export', 'label' => 'Export'],
    ],
  ])

  <div class="relative overflow-auto bg-white dark:bg-slate-800">
    @if ($this->loading)
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data isolir...
        </div>
      </div>
    @endif

    @if ($this->errorMessage)
      <div class="mx-3 mt-3 p-3 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
        {{ $this->errorMessage }}
      </div>
    @endif

    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-700">
        <tr>
          <th class="w-10 px-3 py-2 text-left">
            <label class="inline-flex items-center">
              <input type="checkbox" wire:model.live="selectAll"
                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
            </label>
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('username')">
            Username{!! $sortIcon('username') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('customer_name')">
            Nama Pelanggan{!! $sortIcon('customer_name') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('package_name')">
            Paket{!! $sortIcon('package_name') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Router
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('status')">
            Status{!! $sortIcon('status') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('alasan')">
            Alasan{!! $sortIcon('alasan') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('since_isolir')">
            Sejak Isolir{!! $sortIcon('since_isolir') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('last_due_date')">
            Jatuh Tempo Terakhir{!! $sortIcon('last_due_date') !!}
          </th>
          <th class="w-32 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
        @forelse ($rows as $row)
          @php
            $displayId = $row->display_id ?? "{$row->source_type}-{$row->id}";
            $phone = $row->customer_phone ?? '';
            $name = e($row->customer_name ?? '');
          @endphp
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
            <td class="px-3 py-2">
              <label class="inline-flex items-center">
                <input type="checkbox" value="{{ $displayId }}" wire:model.live="selected"
                  class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
              </label>
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 uppercase">{{ strtoupper($row->source_type ?? '-') }}</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $row->username }}</span>
              </div>
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              <div>{{ $row->customer_name ?? '-' }}</div>
              @if ($phone)
                <div class="text-xs text-slate-400 dark:text-slate-500">{{ $phone }}</div>
              @endif
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              {{ $row->package_name ?? '-' }}
            </td>
            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
              {{ $row->router_name ?? '-' }}
            </td>
            <td class="px-3 py-2">{!! $statusBadge($row->status ?? 'suspended', $row->since_isolir ?? null) !!}</td>
            <td class="px-3 py-2 text-slate-600 dark:text-slate-300 text-xs">
              {{ $row->alasan ?? 'Non pembayaran' }}
            </td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              {{ $row->since_isolir ? (is_string($row->since_isolir) ? $row->since_isolir : \Illuminate\Support\Carbon::parse($row->since_isolir)->format('d/m/Y H:i')) : '-' }}
            </td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              {{ $row->last_due_date ? (is_string($row->last_due_date) ? $row->last_due_date : \Illuminate\Support\Carbon::parse($row->last_due_date)->format('d/m/Y')) : '-' }}
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-1">
                <button wire:click="confirmRowAction('activate', '{{ $displayId }}', '{{ $phone }}', '{{ $name }}')" title="Aktifkan"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </button>
                <button wire:click="confirmRowAction('perpanjang', '{{ $displayId }}', '{{ $phone }}', '{{ $name }}')" title="Perpanjang"
                  class="p-1.5 rounded-md text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:text-slate-400 dark:hover:bg-blue-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button wire:click="confirmRowAction('ganti-paket', '{{ $displayId }}', '{{ $phone }}', '{{ $name }}')" title="Ganti Paket"
                  class="p-1.5 rounded-md text-slate-500 hover:text-purple-600 hover:bg-purple-50 dark:text-slate-400 dark:hover:bg-purple-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"/></svg>
                </button>
                <button wire:click="confirmRowAction('wa', '{{ $displayId }}', '{{ $phone }}', '{{ $name }}')" title="Kirim WA"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/></svg>
                <div class="text-sm font-medium">Tidak ada data pelanggan terisolir</div>
                <div class="text-xs opacity-80">Bagus! Semua pelanggan dalam kondisi aktif.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    {{ $rows->links('livewire::simple-tailwind') }}
  </div>

  @include('partials.enterprise.confirm-modal')

  @if ($this->showPerpanjangModal)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100">Perpanjang Masa Aktif</h3>
          <button wire:click="closeModals" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-3 px-5 py-4">
          <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
            <div class="text-xs text-slate-500 dark:text-slate-400">Pelanggan</div>
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $this->selectedUser['name'] ?? '-' }}</div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Perpanjang (bulan)</label>
            <select wire:model.live="formParams.package_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <option value="">Pilih durasi</option>
              <option value="1">1 Bulan</option>
              <option value="3">3 Bulan</option>
              <option value="6">6 Bulan</option>
              <option value="12">12 Bulan</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan</label>
            <textarea wire:model.live="formParams.notes" rows="2" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2"></textarea>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="closeModals" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700">Batal</button>
          <button wire:click="submitPerpanjang" class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Simpan Perpanjangan</button>
        </div>
      </div>
    </div>
  @endif

  @if ($this->showGantiPaketModal)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100">Ganti Paket Pelanggan</h3>
          <button wire:click="closeModals" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-3 px-5 py-4">
          <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
            <div class="text-xs text-slate-500 dark:text-slate-400">Pelanggan</div>
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $this->selectedUser['name'] ?? '-' }}</div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Pilih Paket Baru</label>
            <select wire:model.live="formParams.package_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <option value="">Pilih Paket</option>
              @foreach ($this->filterOptions['packages'] ?? [] as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan</label>
            <textarea wire:model.live="formParams.notes" rows="2" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2"></textarea>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="closeModals" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700">Batal</button>
          <button wire:click="submitGantiPaket" class="px-4 py-1.5 text-sm font-medium rounded-md bg-purple-600 hover:bg-purple-700 text-white shadow-sm">Ganti Paket</button>
        </div>
      </div>
    </div>
  @endif
</div>
