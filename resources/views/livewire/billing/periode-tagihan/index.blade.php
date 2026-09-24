@php
  $sortIcon = function($field) {
    $dir = $this->sortField === $field ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '';
    return $dir ? " <span class='text-blue-600'>{$dir}</span>" : '';
  };
  $rupiah = function($n) {
    return 'Rp ' . number_format((float)($n ?? 0), 0, ',', '.');
  };
  $periodeKey = function($tahun, $bulan) {
    return "{$tahun}-{$bulan}";
  };
@endphp
<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <span class="material-symbols-outlined notranslate" translate="no">date_range</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Periode Tagihan</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Rekapitulasi tagihan bulanan dan proses generate massal</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800/60 shadow-sm bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/50 dark:to-slate-800 group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-500 to-slate-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-2">Total Tagihan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ $rupiah($this->summary['total_tagihan'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-2">Belum Bayar</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ $rupiah($this->summary['belum_bayar'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">Sudah Dibayar</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ $rupiah($this->summary['sudah_bayar'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-sm bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-md transition-all">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-2">Jatuh Tempo</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ $rupiah($this->summary['overdue'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative max-w-md flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari periode..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                </div>
                <select wire:model.live="filters.tahun" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Tahun</option>
                    @foreach(range(date('Y')-2, date('Y')+1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="exportCsv" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> Export
                </button>
                <button wire:click="kirimWaSemua" class="px-4 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-lg text-sm font-medium hover:bg-green-100 dark:bg-green-900/50 dark:hover:bg-green-900/40 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span> WhatsApp
                </button>
                <button wire:click="openRegenerate" class="px-4 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-sm font-medium hover:bg-amber-100 dark:bg-amber-900/50 dark:hover:bg-amber-900/40 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">repeat</span> Regenerate
                </button>
                <button wire:click="openGenerate" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span> Generate Manual
                </button>
            </div>
        </div>

        {{-- ERROR ALERT --}}
        @if ($this->errorMessage)
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                {{ $this->errorMessage }}
            </div>
        @endif

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto relative">
            @if($this->loading)
            <div class="absolute inset-0 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 rounded-lg shadow-lg">
                    <span class="material-symbols-outlined notranslate animate-spin text-blue-600" translate="no">refresh</span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Memproses...</span>
                </div>
            </div>
            @endif

            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800" wire:click="sortBy('tahun')">
                            Periode {!! $sortIcon('tahun') !!}
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800" wire:click="sortBy('jumlah_invoice')">
                            Jumlah Invoice {!! $sortIcon('jumlah_invoice') !!}
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800" wire:click="sortBy('total_tagihan')">
                            Total Tagihan {!! $sortIcon('total_tagihan') !!}
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800" wire:click="sortBy('sudah_dibayar')">
                            Sudah Dibayar {!! $sortIcon('sudah_dibayar') !!}
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800" wire:click="sortBy('belum_dibayar')">
                            Sisa Tagihan {!! $sortIcon('belum_dibayar') !!}
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($rows as $row)
                        @php $key = $periodeKey($row->tahun, $row->bulan); @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <input type="checkbox" wire:model.live="selected" value="{{ $key }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:bg-slate-900 dark:text-slate-100">
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                {{ date('F', mktime(0, 0, 0, $row->bulan, 10)) }} {{ $row->tahun }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 rounded-full">
                                    {{ number_format($row->jumlah_invoice) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-blue-600 dark:text-blue-400">
                                {{ $rupiah($row->total_tagihan) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                {{ $rupiah($row->sudah_dibayar) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-red-600 dark:text-red-400">
                                {{ $rupiah($row->belum_dibayar) }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button wire:click="kirimWaPeriode('{{ $row->tahun }}', '{{ $row->bulan }}')" onclick="confirm('Kirim pesan tagihan (WA) untuk periode ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-green-50 hover:bg-green-100 text-green-600 dark:bg-green-900/20 dark:hover:bg-green-900/40 dark:text-green-400 transition-colors" title="Kirim WA Tagihan">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span>
                                </button>
                                <a href="{{ route('billing.invoices.index') }}?filters[tahun]={{ $row->tahun }}&filters[bulan]={{ $row->bulan }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Lihat Invoice">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">date_range</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Tidak Ada Data</p>
                                    <p class="text-sm mt-1">Belum ada periode tagihan yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($rows->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODALS --}}
    @if ($this->showGenerateModal || $this->showRegenerateModal)
        @php $modalTitle = $this->showRegenerateModal ? 'Regenerate Periode Tagihan' : 'Generate Periode Tagihan'; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $modalTitle }}</h3>
                    <button wire:click="closeGenerate" class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tahun</label>
                        <select wire:model="generateTahun" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Pilih Tahun</option>
                            @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bulan</label>
                        <select wire:model="generateBulan" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Pilih Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-2">
                    <button wire:click="closeGenerate" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors">Batal</button>
                    @if ($this->showRegenerateModal)
                        <button wire:click="submitRegenerate" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition-colors">Eksekusi Regenerate</button>
                    @else
                        <button wire:click="submitGenerate" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">Mulai Generate</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>