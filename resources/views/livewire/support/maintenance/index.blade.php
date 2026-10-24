@php
  $sortIcon = fn($f) => $this->sortField === $f ? ' <span class="text-indigo-600 font-bold">'.($this->sortDirection==='asc'?'↑':'↓').'</span>' : '';
  
  $statusBadge = fn($s) => match(strtolower((string)$s)){
    'completed'   => ['bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800', 'Selesai'],
    'in_progress' => ['bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800', 'Dikerjakan'],
    'cancelled'   => ['bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800', 'Dibatalkan'],
    default       => ['bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800', 'Dijadwalkan'],
  };

  $typeBadge = fn($t) => match(strtolower((string)$t)){
    'preventive' => ['bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border-sky-200 dark:border-sky-800', 'Preventive'],
    'corrective' => ['bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 border-orange-200 dark:border-orange-800', 'Corrective'],
    'predictive' => ['bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border-teal-200 dark:border-teal-800', 'Predictive'],
    'emergency'  => ['bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800', 'Emergency'],
    default      => ['bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700', ucfirst((string)$t)],
  };
@endphp

<div class="space-y-6">
  {{-- Toolbar Header --}}
  <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Pemeliharaan & Maintenance Jaringan</h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Jadwal pencegahan (preventive), perbaikan perangkat (corrective), monitoring teknisi dan stok material</p>
    </div>
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 sm:pb-0 w-full sm:w-auto">
      <button wire:click="exportCsv" class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-xs gap-2 whitespace-nowrap">
        <span class="material-symbols-outlined notranslate text-lg" translate="no">download</span>
        Ekspor CSV
      </button>
      <button wire:click="openUsageModal" class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-xs gap-2 whitespace-nowrap">
        <span class="material-symbols-outlined notranslate text-lg text-amber-500" translate="no">inventory_2</span>
        Catat Material
      </button>
      <button wire:click="openCreate" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow gap-2 whitespace-nowrap">
        <span class="material-symbols-outlined notranslate text-lg" translate="no">add_circle</span>
        Jadwal Baru
      </button>
    </div>
  </div>

  {{-- Summary Cards --}}
  @include('partials.enterprise.summary-cards', ['items' => $this->getSummaryItems()])

  {{-- Search & Tab Navigation --}}
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 shadow-xs space-y-3">
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
      {{-- Tabs --}}
      <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1 gap-1 overflow-x-auto">
        @foreach($tabs as $tabKey => $tabLabel)
          <button wire:click="setActiveTab('{{ $tabKey }}')"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ $activeTab === $tabKey ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
            {{ $tabLabel }}
          </button>
        @endforeach
      </div>

      {{-- Search & Filter Toggle --}}
      <div class="flex items-center gap-2">
        <div class="relative w-full sm:w-72">
          <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" translate="no" style="font-size:18px">search</span>
          <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari jadwal / perangkat / lokasi..."
                 class="w-full pl-9 pr-3.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button wire:click="$toggle('showFilters')" class="p-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition-colors" title="Filter Tambahan">
          <span class="material-symbols-outlined notranslate text-lg" translate="no">tune</span>
        </button>
      </div>
    </div>

    @if($showFilters)
      <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
        @include('partials.enterprise.filters', ['filters' => $this->getFilterConfig()])
      </div>
    @endif
  </div>

  {{-- Tab Contents --}}
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
    
    {{-- TAB 1: KALENDER --}}
    @if ($activeTab === 'calendar')
      <div class="p-4 space-y-4">
        {{-- Month Navigator --}}
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <button wire:click="prevMonth" class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
              <span class="material-symbols-outlined notranslate text-base" translate="no">chevron_left</span>
            </button>
            <span class="text-base font-bold text-slate-800 dark:text-slate-100 font-mono">
              {{ \Carbon\Carbon::parse(($calendarMonth ?: now()->format('Y-m')) . '-01')->translatedFormat('F Y') }}
            </span>
            <button wire:click="nextMonth" class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
              <span class="material-symbols-outlined notranslate text-base" translate="no">chevron_right</span>
            </button>
          </div>
          <div class="flex items-center gap-3 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Preventive</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Corrective</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Emergency</span>
          </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-1 text-xs">
          @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $dayName)
            <div class="py-2 text-center font-bold text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/50 rounded-lg uppercase tracking-wider text-[11px]">
              {{ $dayName }}
            </div>
          @endforeach

          @if(isset($calendar) && is_array($calendar))
            @foreach($calendar as $cell)
              <div class="min-h-[105px] border border-slate-100 dark:border-slate-800 rounded-xl p-2 flex flex-col justify-between transition-all {{ $cell['is_today'] ? 'bg-indigo-50/50 dark:bg-indigo-950/20 ring-1 ring-indigo-500' : ($cell['is_current_month'] ? 'bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/40' : 'bg-slate-50/50 dark:bg-slate-900/40 opacity-40') }}">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold {{ $cell['is_today'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300' }}">{{ $cell['day'] }}</span>
                  @if($cell['is_today'])
                    <span class="text-[10px] font-semibold px-1.5 py-0.2 bg-indigo-600 text-white rounded-full">Hari Ini</span>
                  @endif
                </div>

                <div class="space-y-1 mt-1 flex-1 overflow-y-auto max-h-[80px]">
                  @foreach($cell['events'] ?? [] as $ev)
                    @php
                      $cBg = match($ev['type'] ?? '') {
                        'preventive' => 'bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-300 dark:border-sky-800',
                        'emergency'  => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800',
                        'corrective' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border border-orange-300 dark:border-orange-800',
                        default      => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
                      };
                    @endphp
                    <div wire:click="rowDetail({{ $ev['id'] }})"
                         class="text-[10.5px] truncate rounded-md px-1.5 py-0.5 font-medium cursor-pointer transition-transform hover:scale-[1.02] {{ $cBg }}" 
                         title="{{ $ev['title'] }}">
                      {{ $ev['title'] }}
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          @endif
        </div>
      </div>

    {{-- TAB 2: RIWAYAT & DAFTAR JADWAL --}}
    @elseif($activeTab === 'history')
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
          <thead>
            <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
              {{-- Aksi Column on LEFT --}}
              <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap w-28">
                Aksi
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                ID
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap cursor-pointer" wire:click="sortBy('scheduled_date')">
                Tgl Jadwal & Jam {!! $sortIcon('scheduled_date') !!}
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                Judul & Perangkat
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                Jenis
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                Teknisi Bertugas
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                Lokasi / POP
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap cursor-pointer" wire:click="sortBy('priority')">
                Prioritas {!! $sortIcon('priority') !!}
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap cursor-pointer" wire:click="sortBy('status')">
                Status {!! $sortIcon('status') !!}
              </th>
              <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                Selesai
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
            @forelse($rows as $r)
              @php 
                [$sc, $sl] = $statusBadge($r->status ?? 'scheduled'); 
                [$tc, $tl] = $typeBadge($r->type ?? 'preventive'); 
              @endphp
              <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                {{-- Action buttons on LEFT --}}
                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-1.5">
                    <button wire:click="rowDetail({{ $r->id }})" title="Lihat Detail" class="p-1.5 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                      <span class="material-symbols-outlined notranslate text-[18px]" translate="no">visibility</span>
                    </button>
                    @if(($r->status ?? 'scheduled') === 'scheduled')
                      <button wire:click="rowStart({{ $r->id }})" title="Mulai Pekerjaan" class="p-1.5 text-slate-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">play_arrow</span>
                      </button>
                    @endif
                    @if(($r->status ?? 'scheduled') === 'in_progress')
                      <button wire:click="rowComplete({{ $r->id }})" title="Tandai Selesai" class="p-1.5 text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">check_circle</span>
                      </button>
                    @endif
                    <button wire:click="rowDelete({{ $r->id }})" title="Hapus Jadwal" class="p-1.5 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                      <span class="material-symbols-outlined notranslate text-[18px]" translate="no">delete</span>
                    </button>
                  </div>
                </td>

                {{-- ID --}}
                <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-slate-500 dark:text-slate-400">
                  #{{ $r->id }}
                </td>

                {{-- Tgl Jadwal --}}
                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-700 dark:text-slate-300">
                  <div class="font-medium text-slate-800 dark:text-slate-100">
                    {{ !empty($r->scheduled_date) ? \Carbon\Carbon::parse($r->scheduled_date)->translatedFormat('d M Y') : '-' }}
                  </div>
                  <div class="text-[11px] text-slate-400 font-mono">
                    {{ $r->scheduled_time ?? '09:00' }} WIB
                  </div>
                </td>

                {{-- Judul & Perangkat --}}
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $r->title ?? '-' }}</div>
                  @if(!empty($r->device_name) || !empty($r->device_type))
                    <div class="text-[11px] text-indigo-600 dark:text-indigo-400">
                      {{ $r->device_name ?: '-' }} ({{ ucfirst($r->device_type ?? 'Perangkat') }})
                    </div>
                  @endif
                </td>

                {{-- Jenis --}}
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $tc }}">
                    {{ $tl }}
                  </span>
                </td>

                {{-- Teknisi --}}
                <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-800 dark:text-slate-200">
                  {{ $r->technician_name ?? '-' }}
                </td>

                {{-- Lokasi / POP --}}
                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600 dark:text-slate-400">
                  {{ $r->location_name ?: '-' }}
                </td>

                {{-- Prioritas --}}
                <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold capitalize">
                  @php
                    $pCol = match($r->priority ?? 'medium') {
                      'critical' => 'text-rose-600 dark:text-rose-400',
                      'high'     => 'text-amber-600 dark:text-amber-400',
                      'low'      => 'text-slate-400',
                      default    => 'text-slate-700 dark:text-slate-300',
                    };
                  @endphp
                  <span class="{{ $pCol }}">{{ $r->priority ?? 'medium' }}</span>
                </td>

                {{-- Status --}}
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sc }}">
                    {{ $sl }}
                  </span>
                </td>

                {{-- Selesai --}}
                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                  {{ !empty($r->completed_at) ? \Carbon\Carbon::parse($r->completed_at)->translatedFormat('d M Y H:i') : '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                  <span class="material-symbols-outlined notranslate text-4xl mb-2 text-slate-300 dark:text-slate-600" translate="no">build</span>
                  <p class="text-sm font-medium">Belum ada jadwal pemeliharaan jaringan</p>
                  <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Jadwal Baru" di atas untuk menambahkan tugas maintenance</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($rows->hasPages())
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
          {{ $rows->links() }}
        </div>
      @endif

    {{-- TAB 3: BEBAN TEKNISI --}}
    @elseif($activeTab === 'technician')
      <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($technicianLoad ?? [] as $t)
          <div class="bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                  {{ strtoupper(substr($t['name'] ?? 'T', 0, 1)) }}
                </div>
                <div>
                  <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $t['name'] }}</h3>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $t['specialization'] ?? 'Teknisi Jaringan' }}</p>
                </div>
              </div>
              <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ $t['rate'] ?? 0 }}% Selesai</span>
            </div>

            {{-- Metric Counts --}}
            <div class="grid grid-cols-4 gap-2 text-center text-xs pt-1">
              <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/40">
                <div class="font-bold text-blue-700 dark:text-blue-300 text-sm font-mono">{{ $t['scheduled'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-500">Jadwal</div>
              </div>
              <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/40">
                <div class="font-bold text-amber-700 dark:text-amber-300 text-sm font-mono">{{ $t['in_progress'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-500">Proses</div>
              </div>
              <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/40">
                <div class="font-bold text-emerald-700 dark:text-emerald-300 text-sm font-mono">{{ $t['completed'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-500">Selesai</div>
              </div>
              <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/40">
                <div class="font-bold text-rose-700 dark:text-rose-300 text-sm font-mono">{{ $t['overdue'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-500">Telat</div>
              </div>
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
              <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: {{ $t['rate'] ?? 0 }}%"></div>
            </div>
          </div>
        @empty
          <div class="col-span-full text-center py-12 text-slate-400">
            Belum ada data penugasan teknisi.
          </div>
        @endforelse
      </div>

    {{-- TAB 4: MATERIAL & STOK --}}
    @elseif($activeTab === 'material')
      <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Stok Material --}}
        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
          <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span class="material-symbols-outlined notranslate text-indigo-600 text-base" translate="no">inventory</span>
              Stok Material Pemeliharaan
            </h3>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">Tersedia</span>
          </div>
          <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
            @foreach($stock ?? [] as $stk)
              <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                <div>
                  <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $stk['name'] }}</div>
                  <div class="text-xs text-slate-400 font-mono">{{ $stk['sku'] }} · {{ $stk['location'] ?? 'Gudang' }}</div>
                </div>
                <div class="text-right">
                  <div class="font-mono font-bold text-sm text-slate-900 dark:text-slate-100">{{ $stk['qty'] }} {{ $stk['unit'] }}</div>
                  <div class="text-[10px] text-slate-400">Min. {{ $stk['min_stock'] }} {{ $stk['unit'] }}</div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Log Pemakaian Material --}}
        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
          <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span class="material-symbols-outlined notranslate text-amber-500 text-base" translate="no">history</span>
              Catatan Pemakaian Terbaru
            </h3>
            <button wire:click="openUsageModal" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">+ Catat Baru</button>
          </div>
          <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
            @forelse($usage ?? [] as $usg)
              <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                <div>
                  <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $usg['material'] }}</div>
                  <div class="text-xs text-slate-400">Teknisi: {{ $usg['technician'] }} · Ref: {{ $usg['wo_ref'] }}</div>
                </div>
                <div class="text-right">
                  <div class="font-mono font-bold text-sm text-amber-600">-{{ $usg['qty'] }} {{ $usg['unit'] }}</div>
                  <div class="text-[10px] text-slate-400">{{ $usg['date'] }}</div>
                </div>
              </div>
            @empty
              <div class="p-8 text-center text-slate-400 text-sm">Belum ada riwayat pemakaian material.</div>
            @endforelse
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- MODAL TAMBAH JADWAL BARU --}}
  @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Tambah Jadwal Pemeliharaan Baru</h3>
          <button wire:click="closeCreate" class="text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined notranslate" translate="no">close</span>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Judul Pekerjaan Maintenance *</label>
            <input type="text" wire:model="newMaint.title" placeholder="Contoh: Pembersihan OLT & Pengecekan Power Laser" 
                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Pemeliharaan</label>
              <select wire:model="newMaint.type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                <option value="preventive">Preventive (Pencegahan)</option>
                <option value="corrective">Corrective (Perbaikan)</option>
                <option value="predictive">Predictive (Prediksi)</option>
                <option value="emergency">Emergency (Darurat)</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tingkat Prioritas</label>
              <select wire:model="newMaint.priority" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                <option value="low">Rendah (Low)</option>
                <option value="medium">Normal (Medium)</option>
                <option value="high">Tinggi (High)</option>
                <option value="critical">Kritis (Critical)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe Perangkat</label>
              <select wire:model="newMaint.device_type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
                <option value="router">Router / Mikrotik</option>
                <option value="olt">OLT</option>
                <option value="onu">ONU / ONT</option>
                <option value="odp">ODP</option>
                <option value="odc">ODC</option>
                <option value="pop">POP</option>
                <option value="tower">Tower</option>
                <option value="server">Server</option>
                <option value="other">Lainnya</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Perangkat / ID</label>
              <input type="text" wire:model="newMaint.device_name" placeholder="Contoh: CCR1036-Core-01" 
                     class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Lokasi / POP Terkait</label>
            <input type="text" wire:model="newMaint.location_name" placeholder="Contoh: POP Cibadak - Rak A" 
                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Jadwal *</label>
              <input type="date" wire:model="newMaint.scheduled_date" 
                     class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono">
            </div>
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jam *</label>
              <input type="time" wire:model="newMaint.scheduled_time" 
                     class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono">
            </div>
          </div>

          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Teknisi Bertugas</label>
            <select wire:model="newMaint.technician_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100">
              <option value="">-- Pilih Teknisi Lapangan --</option>
              @foreach($filterOptions['technicians'] ?? [] as $tId => $tName)
                <option value="{{ $tId }}">{{ $tName }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Pekerjaan</label>
            <textarea wire:model="newMaint.notes" rows="2" placeholder="Catatan teknis atau prosedur pelaksanaan..." 
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100"></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <button wire:click="closeCreate" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
          <button wire:click="submitCreate" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold">Simpan Jadwal</button>
        </div>
      </div>
    </div>
  @endif

  {{-- MODAL PENCATATAN MATERIAL --}}
  @if($showUsageModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Catat Pemakaian Material</h3>
          <button wire:click="closeUsageModal" class="text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined notranslate" translate="no">close</span>
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Material *</label>
            <input type="text" wire:model="usageForm.material_name" placeholder="Contoh: Patch Cord SC/UPC 3m" 
                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah *</label>
              <input type="number" wire:model="usageForm.quantity" min="1" 
                     class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-slate-100">
            </div>
            <div>
              <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Satuan</label>
              <input type="text" wire:model="usageForm.unit" placeholder="pcs / meter / roll" 
                     class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100">
            </div>
          </div>
          <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Tambahan</label>
            <textarea wire:model="usageForm.notes" rows="2" placeholder="Digunakan untuk perbaikan link fiber..." 
                      class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100"></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
          <button wire:click="closeUsageModal" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">Batal</button>
          <button wire:click="submitUsage" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold">Simpan Pemakaian</button>
        </div>
      </div>
    </div>
  @endif

  @include('partials.enterprise.confirm-modal')
</div>
