@php
  $sortIcon = function($field) {
    $dir = $this->sortField === $field ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '';
    return $dir ? " <span class='text-blue-600'>{$dir}</span>" : '';
  };
  $statusBadge = function($status) {
    $map = [
      'available' => ['Aktif', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
      'active' => ['Aktif', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
      'used' => ['Terpakai', 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
      'expired' => ['Expired', 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
      'disabled' => ['Disabled', 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
    ];
    [$label, $cls] = $map[$status] ?? ['-', 'bg-slate-100 text-slate-600'];
    return "<span class='inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full {$cls}'>{$label}</span>";
  };
@endphp
<div>
  @include('partials.enterprise.list-toolbar', [
    'title' => 'Voucher Pelanggan',
    'primaryLabel' => 'Generate',
    'primaryAction' => 'openGenerate()',
    'actions' => [
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
      ['label' => 'Print', 'icon' => 'printer', 'action' => 'printSelected()'],
      ['label' => 'Sync Router', 'icon' => 'refresh-cw', 'action' => 'syncRouterAll()'],
    ],
    'searchPlaceholder' => 'Cari kode, nama, paket, router...',
    'showFiltersToggle' => true,
  ])

  @include('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total Voucher', 'value' => number_format($this->summary['all'] ?? 0), 'color' => 'slate', 'icon' => 'ticket'],
      ['label' => 'Aktif', 'value' => number_format($this->summary['active'] ?? 0), 'color' => 'green', 'icon' => 'check-circle'],
      ['label' => 'Terpakai', 'value' => number_format($this->summary['used'] ?? 0), 'color' => 'blue', 'icon' => 'credit-card'],
      ['label' => 'Expired', 'value' => number_format($this->summary['expired'] ?? 0), 'color' => 'amber', 'icon' => 'alert-triangle'],
    ],
  ])

  @if ($this->showFilters)
    @include('partials.enterprise.filters', [
      'filters' => [
        ['key' => 'router_id', 'label' => 'Router', 'type' => 'select', 'options' => $this->filterOptions['routers'] ?? []],
        ['key' => 'package_id', 'label' => 'Paket', 'type' => 'select', 'options' => $this->filterOptions['packages'] ?? []],
        ['key' => 'sales_id', 'label' => 'Sales', 'type' => 'select', 'options' => $this->filterOptions['sales'] ?? []],
        ['key' => 'reseller_id', 'label' => 'Reseller', 'type' => 'select', 'options' => $this->filterOptions['resellers'] ?? []],
        ['key' => 'wilayah_id', 'label' => 'Wilayah', 'type' => 'select', 'options' => $this->filterOptions['wilayahs'] ?? []],
        ['key' => 'type', 'label' => 'Tipe', 'type' => 'select', 'options' => ['reguler' => 'Reguler', 'evoucher' => 'e-Voucher']],
      ],
    ])
  @endif

  @include('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'sync', 'label' => 'Sync Router'],
      ['key' => 'disable', 'label' => 'Disable'],
      ['key' => 'delete', 'label' => 'Delete', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
      ['key' => 'export', 'label' => 'Export'],
    ],
  ])

  <div class="relative overflow-auto bg-white dark:bg-slate-800">
    @if ($this->loading)
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data...
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
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('code')">
            Kode Voucher{!! $sortIcon('code') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('customer_name')">
            Nama Pelanggan{!! $sortIcon('customer_name') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('package_name')">
            Paket{!! $sortIcon('package_name') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('router_name')">
            Router{!! $sortIcon('router_name') !!}
          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('price')">
            Harga{!! $sortIcon('price') !!}
          </th>
          <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('duration')">
            Durasi{!! $sortIcon('duration') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('status')">
            Status{!! $sortIcon('status') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('created_at')">
            Dibuat{!! $sortIcon('created_at') !!}
          </th>
          <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('redeemed_at')">
            Diredeem{!! $sortIcon('redeemed_at') !!}
          </th>
          <th class="w-24 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
        @forelse ($rows as $row)
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
            <td class="px-3 py-2">
              <label class="inline-flex items-center">
                <input type="checkbox" value="{{ $row->id }}" wire:model.live="selected"
                  class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600">
              </label>
            </td>
            <td class="px-3 py-2">
              <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $row->code }}</span>
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              {{ $row->customer_name ?? '-' }}
            </td>
            <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
              {{ $row->package_name ?? '-' }}
            </td>
            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
              {{ $row->router_name ?? '-' }}
            </td>
            <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200 font-medium">
              {{ $row->price ? 'Rp ' . number_format($row->price, 0, ',', '.') : '-' }}
            </td>
            <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">
              {{ $row->duration ? "{$row->duration}h" : '-' }}
            </td>
            <td class="px-3 py-2">{!! $statusBadge($row->status) !!}</td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              {{ $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-' }}
            </td>
            <td class="px-3 py-2 text-slate-500 dark:text-slate-400 text-xs">
              {{ $row->redeemed_at ? \Illuminate\Support\Carbon::parse($row->redeemed_at)->format('d/m/Y H:i') : '-' }}
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-1">
                <button wire:click="confirmRowAction('detail', {{ $row->id }})" title="Detail"
                  class="p-1.5 rounded-md text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:text-slate-400 dark:hover:bg-blue-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                <button wire:click="confirmRowAction('sync', {{ $row->id }})" title="Sync"
                  class="p-1.5 rounded-md text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:text-slate-400 dark:hover:bg-emerald-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button wire:click="confirmRowAction('disable', {{ $row->id }})" title="Disable"
                  class="p-1.5 rounded-md text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:text-slate-400 dark:hover:bg-amber-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </button>
                <button wire:click="confirmRowAction('delete', {{ $row->id }})" title="Delete"
                  class="p-1.5 rounded-md text-slate-500 hover:text-red-600 hover:bg-red-50 dark:text-slate-400 dark:hover:bg-red-900/30">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 3h4a1 1 0 011 1v3H9V4a1 1 0 011-1z"/></svg>
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="11" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div class="text-sm font-medium">Tidak ada data voucher</div>
                <div class="text-xs opacity-80">Ganti filter atau buat voucher baru.</div>
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

  @if ($this->showGenerateModal)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 dark:text-slate-100">Generate Voucher Baru</h3>
          <button wire:click="closeGenerate" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="grid grid-cols-2 gap-3 px-5 py-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Paket *</label>
            <select wire:model.live="generateParams.package_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2 focus:ring-1 focus:ring-blue-500">
              <option value="">Pilih Paket</option>
              @foreach ($this->filterOptions['packages'] ?? [] as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Jumlah *</label>
            <input wire:model.live="generateParams.count" type="number" min="1" max="10000" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Tipe</label>
            <select wire:model.live="generateParams.type" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <option value="reguler">Reguler</option>
              <option value="evoucher">e-Voucher</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Router</label>
            <select wire:model.live="generateParams.router_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <option value="">Semua Router</option>
              @foreach ($this->filterOptions['routers'] ?? [] as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Reseller</label>
            <select wire:model.live="generateParams.reseller_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
              <option value="">-</option>
              @foreach ($this->filterOptions['resellers'] ?? [] as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Panjang Kode</label>
            <input wire:model.live="generateParams.length" type="number" min="4" max="32" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Prefix</label>
            <input wire:model.live="generateParams.prefix" type="text" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Masa Aktif (hari)</label>
            <input wire:model.live="generateParams.validity_days" type="number" min="0" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2">
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Catatan</label>
            <textarea wire:model.live="generateParams.notes" rows="2" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 py-1.5 px-2"></textarea>
          </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
          <button wire:click="closeGenerate" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700">Batal</button>
          <button wire:click="submitGenerate" class="px-4 py-1.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Generate</button>
        </div>
      </div>
    </div>
  @endif
</div>
