@php
  $sortIcon = fn($f) => $this->sortField === $f ? ' <span class="text-blue-600 font-bold">'.($this->sortDirection==='asc'?'↑':'↓').'</span>' : '';
  $statusBadge = fn($s) => match(strtolower((string)$s)){
    'pending' => ['bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200','Pending'],
    'scheduled' => ['bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300','Dijadwalkan'],
    'in_progress' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300','Dikerjakan'],
    'completed' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300','Selesai'],
    'cancelled' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300','Dibatalkan'],
    default => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $s ?: '-'],
  };
@endphp
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">
  {{-- 1. Main Toolbar with single unified Tabs --}}
  @include('partials.enterprise.list-toolbar', [
    'title' => 'Support Instalasi',
    'primaryLabel' => 'WO Baru',
    'primaryAction' => 'openCreateWo()',
    'actions' => [
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
    ],
    'searchPlaceholder' => 'Cari WO, pelanggan, alamat...',
    'showFiltersToggle' => true,
    'tabs' => $this->tabs,
  ])

  {{-- 2. Summary KPI Cards --}}
  @include('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total WO', 'value' => number_format($summary['total_wo'] ?? 0), 'color' => 'slate', 'icon' => 'clipboard-list'],
      ['label' => 'Pending', 'value' => number_format($summary['pending'] ?? 0), 'color' => 'slate', 'icon' => 'clock'],
      ['label' => 'Jadwal Hari Ini', 'value' => number_format($summary['today_scheduled'] ?? 0), 'color' => 'blue', 'icon' => 'calendar'],
      ['label' => 'Dikerjakan', 'value' => number_format($summary['in_progress'] ?? 0), 'color' => 'amber', 'icon' => 'loader'],
      ['label' => 'Selesai', 'value' => number_format($summary['completed'] ?? 0), 'color' => 'emerald', 'icon' => 'check-circle'],
    ],
  ])

  {{-- 3. Filter Drawer --}}
  @if ($showFilters)
    @include('partials.enterprise.filters', ['filters' => $filterConfig ?? $this->getFilterConfig()])
  @endif

  {{-- 4. Bulk Action Bar (muncul saat checkbox tercentang) --}}
  @include('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'assign', 'label' => 'Assign Teknisi'],
      ['key' => 'complete', 'label' => 'Selesaikan'],
      ['key' => 'export', 'label' => 'Export'],
      ['key' => 'cancel', 'label' => 'Batal', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
    ],
  ])

  {{-- 5. Content Viewport --}}
  <div class="relative overflow-auto bg-white dark:bg-slate-800 shadow-xs border-y border-slate-200 dark:border-slate-700 flex-1">
    @if ($loading)
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data...
        </div>
      </div>
    @endif

    @if ($errorMessage)
      <div class="mx-3 my-3 p-3 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-300 text-sm flex items-center justify-between">
        <span>{{ $errorMessage }}</span>
        <button wire:click="$set('errorMessage', null)" class="text-red-500 hover:text-red-700 text-xs font-semibold">Tutup</button>
      </div>
    @endif

    {{-- TAB 1: Work Order Table --}}
    @if ($activeTab === 'workorder')
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400">
          <tr>
            <th class="w-10 px-3 py-2.5 text-left"><input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer" wire:click="sortBy('id')">Kode WO{!! $sortIcon('id') !!}</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Pelanggan</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Paket / Alamat</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider">Teknisi</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer" wire:click="sortBy('scheduled_date')">Jadwal{!! $sortIcon('scheduled_date') !!}</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer" wire:click="sortBy('priority')">Prioritas{!! $sortIcon('priority') !!}</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">Status{!! $sortIcon('status') !!}</th>
            <th class="w-36 px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
          @forelse($rows as $r)
            @php [$sc, $sl] = $statusBadge($r->status ?? 'pending'); @endphp
            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/40 transition-colors">
              <td class="px-3 py-2"><input type="checkbox" value="{{ $r->id }}" wire:model.live="selected" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"></td>
              <td class="px-3 py-2 font-mono font-semibold text-blue-600 dark:text-blue-400">{{ $r->code ?? 'WO-'.$r->id }}</td>
              <td class="px-3 py-2">
                <div class="font-medium text-slate-800 dark:text-slate-100">{{ $r->customer_name ?? $r->customer?->name ?? '-' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $r->customer?->phone ?? $r->customer_phone ?? '' }}</div>
              </td>
              <td class="px-3 py-2">
                <div class="text-slate-700 dark:text-slate-200 font-medium text-xs">{{ $r->contract?->package_name ?? $r->package_name ?? '-' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[280px]" title="{{ $r->customer?->address ?? $r->installation_address ?? $r->notes ?? '' }}">
                  {{ $r->customer?->address ?? $r->installation_address ?? $r->notes ?? '-' }}
                </div>
              </td>
              <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                <span class="inline-flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full {{ $r->technician_id ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                  {{ $r->technician?->name ?? $r->assignedTo?->name ?? $r->technician_name ?? 'Belum Ditugaskan' }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap text-slate-600 dark:text-slate-300 text-xs">
                {{ $r->scheduled_date ? \Carbon\Carbon::parse($r->scheduled_date)->format('d/m/Y') : '-' }}
                @if($r->scheduled_time)
                  <span class="text-slate-400 font-mono">({{ $r->scheduled_time }})</span>
                @endif
              </td>
              <td class="px-3 py-2">
                @php
                  $p = strtolower((string)($r->priority ?? 'medium'));
                  $pc = ['low'=>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200','medium'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300','high'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300','critical'=>'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'];
                @endphp
                <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full {{ $pc[$p] ?? $pc['medium'] }}">{{ ucfirst($p) }}</span>
              </td>
              <td class="px-3 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full {{ $sc }}">{{ $sl }}</span></td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-1">
                  <button wire:click="rowDetail({{$r->id}})" title="Detail" class="p-1.5 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                  <button wire:click="rowAssign({{$r->id}})" title="Tugaskan Teknisi" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></button>
                  @if(($r->status??'pending')==='scheduled' || ($r->status??'pending')==='pending')
                    <button wire:click="rowStart({{$r->id}})" title="Mulai Pengerjaan" class="p-1.5 rounded text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                  @endif
                  @if(($r->status??'pending')==='in_progress')
                    <button wire:click="rowComplete({{$r->id}})" title="Tandai Selesai" class="p-1.5 rounded text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                  @endif
                  <button wire:click="rowCancel({{$r->id}})" title="Batalkan WO" class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-slate-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                <svg class="w-12 h-12 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <div class="text-sm font-semibold text-slate-600 dark:text-slate-300">Belum ada Work Order Instalasi yang cocok</div>
                <div class="text-xs text-slate-400">Klik tombol <strong>+ WO Baru</strong> di atas untuk membuat Work Order instalasi baru.</div>
              </div>
            </td></tr>
          @endforelse
        </tbody>
      </table>

      {{-- Pagination Footer --}}
      <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        {{ $rows?->links('livewire::simple-tailwind') }}
      </div>

    {{-- TAB 2: Calendar Schedule Grid --}}
    @elseif($activeTab === 'schedule')
      <div class="p-4 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
            Jadwal Instalasi Bulan Ini ({{ now()->translatedFormat('F Y') }})
          </div>
          <div class="text-xs text-slate-500 dark:text-slate-400">
            Total Event: {{ count($calendarEvents ?? []) }} hari terjadwal
          </div>
        </div>
        <div class="grid grid-cols-7 gap-1 text-xs">
          @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d)
            <div class="px-2 py-2 font-bold text-center text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded-md border border-slate-200/60 dark:border-slate-700/60">{{$d}}</div>
          @endforeach
          @foreach(($calendar ?? []) as $cell)
            <div class="min-h-[96px] border border-slate-200 dark:border-slate-700/70 rounded-lg p-1.5 flex flex-col justify-between transition-colors {{ ($cell['is_today'] ?? false) ? 'bg-blue-50/60 dark:bg-blue-900/20 ring-1 ring-blue-400 dark:ring-blue-600' : (($cell['is_current_month'] ?? true) ? 'bg-white dark:bg-slate-800' : 'bg-slate-50/50 dark:bg-slate-900/30 opacity-60') }}">
              <div class="flex items-center justify-between text-[11px] font-semibold {{ ($cell['is_today'] ?? false) ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-400' }}">
                <span>{{ ($cell['is_today'] ?? false) ? 'Hari Ini' : '' }}</span>
                <span>{{ $cell['day'] }}</span>
              </div>
              <div class="space-y-1 mt-1 overflow-y-auto max-h-[70px]">
                @foreach(($cell['events'] ?? []) as $ev)
                  <div class="text-[10px] leading-tight truncate rounded px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 font-medium" title="{{ $ev['title'] }} ({{ $ev['time'] }})">
                    <span class="font-mono text-[9px]">{{ $ev['time'] }}</span> {{ $ev['title'] }}
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>

    {{-- TAB 3: Technician Load Matrix --}}
    @elseif($activeTab === 'technician')
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse(($technicianLoad ?? []) as $t)
          <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 bg-white dark:bg-slate-800/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                  {{ strtoupper(substr($t['name'] ?? 'T', 0, 1)) }}
                </div>
                <div>
                  <div class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $t['name'] }}</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400">Teknisi Lapangan</div>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                Total: {{ $t['total'] ?? 0 }} WO
              </span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-2 border border-slate-100 dark:border-slate-700">
                <div class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">{{ $t['pending'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-semibold">Pending</div>
              </div>
              <div class="rounded-lg bg-amber-50 dark:bg-amber-950/40 p-2 border border-amber-200 dark:border-amber-800/50">
                <div class="font-extrabold text-amber-700 dark:text-amber-300 text-sm">{{ $t['in_progress'] ?? 0 }}</div>
                <div class="text-[10px] text-amber-600/90 uppercase font-semibold">Proses</div>
              </div>
              <div class="rounded-lg bg-emerald-50 dark:bg-emerald-950/40 p-2 border border-emerald-200 dark:border-emerald-800/50">
                <div class="font-extrabold text-emerald-700 dark:text-emerald-300 text-sm">{{ $t['completed'] ?? 0 }}</div>
                <div class="text-[10px] text-emerald-600/90 uppercase font-semibold">Selesai</div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-span-full text-center py-16 text-slate-400 dark:text-slate-500">
            <svg class="w-12 h-12 mx-auto stroke-1 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Belum ada personil teknisi yang terdaftar untuk penugasan.
          </div>
        @endforelse
      </div>

    {{-- TAB 4: Checklist & Material Standards --}}
    @elseif($activeTab === 'checklist')
      <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 shadow-xs overflow-hidden">
          <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center justify-between">
            <span>Template Checklist Instalasi Standar</span>
            <span class="text-xs font-normal text-slate-500">Wajib diverifikasi teknisi</span>
          </div>
          <div class="p-5 space-y-3">
            @foreach(($checklistTemplate ?? []) as $c)
              <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-200 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <input type="checkbox" disabled checked class="w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-0">
                <span class="font-medium">{{ $c['item'] ?? $c['label'] ?? '' }}</span>
                @if($c['required'] ?? false)
                  <span class="text-[10px] text-red-500 font-semibold uppercase ml-auto">Wajib</span>
                @endif
              </label>
            @endforeach
          </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 shadow-xs overflow-hidden">
          <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center justify-between">
            <span>Standar Material Instalasi Baru</span>
            <span class="text-xs font-normal text-slate-500">Estimasi default per titik</span>
          </div>
          <div class="p-5 divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Patch Cord SC/UPC 3 Meter</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">2 pcs</span></div>
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Kabel Drop Core 1 Core (GJXH)</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">± 50 - 150 meter</span></div>
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Fast Connector SC/UPC</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">2 pcs</span></div>
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Roset Optik 1 Core (Faceplate)</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">1 pcs</span></div>
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Clamp Hook / S-Clamp Tarikan</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">3 - 6 pcs</span></div>
            <div class="py-2.5 flex justify-between text-slate-700 dark:text-slate-200 font-medium"><span>Cable Ties + Velcro Pengikat</span><span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">1 pack</span></div>
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Modal 1: Buat Work Order Baru --}}
  @if ($showCreateWo)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
          <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
            Buat Work Order Instalasi Baru
          </h3>
          <button wire:click="closeCreateWo" class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="p-5 space-y-4 text-sm">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelanggan <span class="text-red-500">*</span></label>
            <select wire:model="newWo.customer_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
              <option value="">-- Pilih Pelanggan --</option>
              @foreach(($filterOptions['customers'] ?? []) as $cid => $cname)
                <option value="{{ $cid }}">{{ $cname }}</option>
              @endforeach
            </select>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Jadwal</label>
              <input type="date" wire:model="newWo.scheduled_date" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Waktu</label>
              <input type="time" wire:model="newWo.scheduled_time" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Teknisi Ditugaskan</label>
              <select wire:model="newWo.technician_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
                <option value="">-- Belum Ditugaskan --</option>
                @foreach(($filterOptions['technicians'] ?? []) as $tid => $tname)
                  <option value="{{ $tid }}">{{ $tname }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prioritas</label>
              <select wire:model="newWo.priority" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
                <option value="low">Rendah (Low)</option>
                <option value="medium">Normal (Medium)</option>
                <option value="high">Tinggi (High)</option>
                <option value="critical">Darurat (Critical)</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan / Detail Lokasi</label>
            <textarea wire:model="newWo.notes" rows="3" placeholder="Patokan lokasi, ODP target, panjang kabel..." class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500"></textarea>
          </div>
        </div>
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
          <button wire:click="closeCreateWo" type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Batal</button>
          <button wire:click="submitCreateWo" type="button" class="px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors">Simpan Work Order</button>
        </div>
      </div>
    </div>
  @endif

  {{-- Modal 2: Assign Teknisi --}}
  @if ($showAssignModal)
    <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div x-show="show" class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
          <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">Tugaskan Teknisi</h3>
          <button wire:click="cancelBulkAssign" class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="p-5 space-y-3">
          <p class="text-xs text-slate-500 dark:text-slate-400">Pilih teknisi yang akan menangani penugasan instalasi ini:</p>
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Teknisi</label>
            <select wire:model="assignTechId" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-slate-100 py-2 px-3 text-sm focus:ring-blue-500">
              <option value="">-- Pilih Teknisi --</option>
              @foreach(($filterOptions['technicians'] ?? []) as $tid => $tname)
                <option value="{{ $tid }}">{{ $tname }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2">
          <button wire:click="cancelBulkAssign" type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Batal</button>
          <button wire:click="handleBulkAction('assign', {{ json_encode($selected) }})" type="button" class="px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors">Tugaskan Sekarang</button>
        </div>
      </div>
    </div>
  @endif

  @include('partials.enterprise.confirm-modal')
</div>
