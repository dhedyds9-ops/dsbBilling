@php
  $sortIcon = fn($f) => $this->sortField === $f ? ' <span class="text-blue-600">'.($this->sortDirection==='asc'?'↑':'↓').'</span>' : '';
  $statusBadge = fn($s) => match(strtolower($s)){
    'pending' => ['bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200','Pending'],
    'scheduled' => ['bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300','Dijadwalkan'],
    'in_progress' => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300','Dikerjakan'],
    'completed' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300','Selesai'],
    'cancelled' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300','Dibatalkan'],
    default => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', $s ?: '-'],
  };
@endphp
<div>
  @include('partials.enterprise.list-toolbar', [
    'title' => 'Support Instalasi',
    'primaryLabel' => 'WO Baru',
    'primaryAction' => 'openCreateWo',
    'actions' => [
      ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
      ['label' => 'Bulk Assign', 'icon' => 'user-plus', 'action' => 'showBulkAssign=true'],
    ],
    'searchPlaceholder' => 'Cari WO / pelanggan / alamat / catatan...',
    'showFiltersToggle' => true,
  ])

  @include('partials.enterprise.summary-cards', [
    'items' => [
      ['label' => 'Total WO', 'value' => number_format($summary['total_wo'] ?? 0), 'color' => 'slate', 'icon' => 'clipboard-list'],
      ['label' => 'Pending', 'value' => number_format($summary['pending'] ?? 0), 'color' => 'slate', 'icon' => 'clock'],
      ['label' => 'Jadwal Hari Ini', 'value' => number_format($summary['today_scheduled'] ?? 0), 'color' => 'blue', 'icon' => 'calendar'],
      ['label' => 'Dikerjakan', 'value' => number_format($summary['in_progress'] ?? 0), 'color' => 'amber', 'icon' => 'loader'],
      ['label' => 'Selesai', 'value' => number_format($summary['completed'] ?? 0), 'color' => 'emerald', 'icon' => 'check-circle'],
    ],
  ])

  @if ($showFilters)
    @include('partials.enterprise.filters', ['filters' => $filterConfig])
  @endif

  <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <nav class="flex items-center gap-1 text-sm font-medium overflow-x-auto">
      @foreach(['workorder'=>'Work Order','schedule'=>'Jadwal','technician'=>'Teknisi','checklist'=>'Checklist'] as $k=>$l)
        <button wire:click="setActiveTab('{{$k}}')" class="whitespace-nowrap px-3 py-1.5 rounded-md transition-colors {{ $activeTab===$k ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">{{ $l }}</button>
      @endforeach
    </nav>
  </div>

  @include('partials.enterprise.bulk-bar', [
    'bulkActions' => [
      ['key' => 'assign', 'label' => 'Assign Teknisi'],
      ['key' => 'complete', 'label' => 'Selesaikan'],
      ['key' => 'export', 'label' => 'Export'],
      ['key' => 'cancel', 'label' => 'Batal', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
    ],
  ])

  <div class="relative overflow-auto bg-white dark:bg-slate-800">
    @if ($loading)
      <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-slate-900/60 backdrop-blur-[1px]">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 border border-blue-100 dark:border-blue-800">
          <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
          Memuat data...
        </div>
      </div>
    @endif

    @if ($errorMessage)
      <div class="mx-3 mt-3 p-3 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
        {{ $errorMessage }}
      </div>
    @endif

    @if ($activeTab === 'workorder')
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/40 border-y border-slate-200 dark:border-slate-700">
          <tr>
            <th class="w-10 px-3 py-2 text-left"><input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('code')">Kode WO{!! $sortIcon('code') !!}</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pelanggan</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Paket / Alamat</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Teknisi</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('scheduled_date')">Jadwal{!! $sortIcon('scheduled_date') !!}</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('priority')">Prioritas{!! $sortIcon('priority') !!}</th>
            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('status')">Status{!! $sortIcon('status') !!}</th>
            <th class="w-36 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/70">
          @forelse($rows as $r)
            @php [$sc, $sl] = $statusBadge($r->status ?? 'pending'); @endphp
            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/40">
              <td class="px-3 py-2"><input type="checkbox" value="{{ $r->id }}" wire:model.live="selected" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"></td>
              <td class="px-3 py-2 font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $r->code ?? 'WO-'.$r->id }}</td>
              <td class="px-3 py-2">
                <div class="font-medium text-slate-800 dark:text-slate-100">{{ $r->customer_name ?? '-' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $r->customer_phone ?? '' }}</div>
              </td>
              <td class="px-3 py-2">
                <div class="text-slate-700 dark:text-slate-200">{{ $r->package_name ?? '-' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[280px]">{{ $r->installation_address ?? $r->notes ?? '' }}</div>
              </td>
              <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $r->technician_name ?? '-' }}</td>
              <td class="px-3 py-2 whitespace-nowrap text-slate-600 dark:text-slate-300 text-xs">
                {{ $r->scheduled_date ? \Carbon\Carbon::parse($r->scheduled_date)->format('d/m/Y H:i') : '-' }}
              </td>
              <td class="px-3 py-2">
                @php
                  $p = strtolower($r->priority ?? 'medium');
                  $pc = ['low'=>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-200','medium'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300','high'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300','critical'=>'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'];
                @endphp
                <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full {{ $pc[$p] ?? $pc['medium'] }}">{{ ucfirst($p) }}</span>
              </td>
              <td class="px-3 py-2"><span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full {{ $sc }}">{{ $sl }}</span></td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-1">
                  <button wire:click="rowDetail({{$r->id}})" title="Detail" class="p-1.5 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 dark:text-slate-400 dark:hover:bg-blue-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                  <button wire:click="rowAssign({{$r->id}})" title="Assign" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-indigo-900/30 dark:text-slate-400 dark:hover:bg-indigo-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></button>
                  @if(($r->status??'pending')==='scheduled' || ($r->status??'pending')==='pending')
                    <button wire:click="rowStart({{$r->id}})" title="Start" class="p-1.5 rounded text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:bg-amber-900/30 dark:text-slate-400 dark:hover:bg-amber-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                  @endif
                  @if(($r->status??'pending')==='in_progress')
                    <button wire:click="confirmRowAction('complete', {{$r->id}})" title="Selesai" class="p-1.5 rounded text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 dark:text-slate-400 dark:hover:bg-emerald-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
                  @endif
                  <button wire:click="confirmRowAction('cancel', {{$r->id}})" title="Batal" class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/30 dark:text-slate-400 dark:hover:bg-red-900/30"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="px-3 py-16 text-center">
              <div class="inline-flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <div class="text-sm font-medium">Belum ada Work Order Instalasi</div>
                <div class="text-xs opacity-80">Klik WO Baru untuk membuat.</div>
              </div>
            </td></tr>
          @endforelse
        </tbody>
      </table>
    @elseif($activeTab === 'schedule')
      <div class="p-4 space-y-3">
        <div class="grid grid-cols-7 gap-1 text-xs">
          @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d)
            <div class="px-2 py-1 font-semibold text-center text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">{{$d}}</div>
          @endforeach
          @if(isset($calendar) && is_array($calendar))
            @foreach($calendar as $cell)
              <div class="min-h-[84px] border border-slate-100 dark:border-slate-700/60 rounded-md p-1 text-slate-700 dark:text-slate-200 {{ $cell['is_today']??false ? 'bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-200 dark:ring-blue-800' : '' }}">
                <div class="text-right text-[10px] text-slate-500 dark:text-slate-400">{{ $cell['day'] }}</div>
                @foreach($cell['events'] ?? [] as $ev)
                  <div class="mt-0.5 text-[10px] truncate rounded px-1 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300" title="{{$ev['title']}}">{{$ev['title']}}</div>
                @endforeach
              </div>
            @endforeach
          @endif
        </div>
      </div>
    @elseif($activeTab === 'technician')
      <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($technicianLoad ?? [] as $t)
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50/50 dark:bg-slate-900/20">
            <div class="flex items-center justify-between mb-2">
              <div>
                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $t['name'] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $t['role'] ?? 'Teknisi' }}</div>
              </div>
              <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold text-sm">
                {{ strtoupper(substr($t['name'],0,1)) }}
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="rounded bg-slate-100 dark:bg-slate-700 p-1.5"><div class="font-bold text-slate-900 dark:text-slate-100">{{$t['pending']??0}}</div><div class="text-slate-500 dark:text-slate-400">Pending</div></div>
              <div class="rounded bg-amber-100 dark:bg-amber-900/40 p-1.5"><div class="font-bold text-amber-700 dark:text-amber-300">{{$t['in_progress']??0}}</div><div class="text-amber-600/80">Proses</div></div>
              <div class="rounded bg-emerald-100 dark:bg-emerald-900/40 p-1.5"><div class="font-bold text-emerald-700 dark:text-emerald-300">{{$t['completed']??0}}</div><div class="text-emerald-600/80">Selesai</div></div>
            </div>
          </div>
        @endforeach
        @if(empty($technicianLoad))
          <div class="col-span-full text-center py-12 text-slate-500 dark:text-slate-400">Data beban teknisi belum tersedia.</div>
        @endif
      </div>
    @elseif($activeTab === 'checklist')
      <div class="p-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-900 dark:text-slate-100">Template Checklist Instalasi</div>
            <div class="p-4 space-y-2">
              @foreach($checklistTemplate ?? [] as $c)
                <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                  <input type="checkbox" disabled checked class="mt-0.5 rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                  <span>{{ $c['label'] }}</span>
                </label>
              @endforeach
              @if(empty($checklistTemplate))
                <div class="text-slate-500 dark:text-slate-400 text-sm">Template checklist belum tersedia.</div>
              @endif
            </div>
          </div>
          <div class="border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="px-4 py-2.5 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-900 dark:text-slate-100">Checklist Material Standard</div>
            <div class="p-4 space-y-2 text-sm">
              <div class="flex justify-between text-slate-700 dark:text-slate-200"><span>Patch Cord SC/UPC 3m</span><span class="font-mono">2 pc</span></div>
              <div class="flex justify-between text-slate-700 dark:text-slate-200"><span>Drop Cable 1 Core</span><span class="font-mono">± 50m</span></div>
              <div class="flex justify-between text-slate-700 dark:text-slate-200"><span>Fast Connector / Splice</span><span class="font-mono">2 set</span></div>
              <div class="flex justify-between text-slate-700 dark:text-slate-200"><span>Cable Ties + Velcro</span><span class="font-mono">pack</span></div>
              <div class="flex justify-between text-slate-700 dark:text-slate-200"><span>Label kabel + identitas</span><span class="font-mono">2 pc</span></div>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>

  <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    {{ $rows->links('livewire::simple-tailwind') }}
  </div>

  @include('partials.enterprise.confirm-modal')
</div>
