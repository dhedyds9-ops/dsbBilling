{{--
Enterprise SSOT List Layout Partial
Usage:
@include('partials.enterprise.list-toolbar', [
  'title' => '...',
  'primaryLabel' => 'Buat',
  'primaryAction' => "window.location='".route('x.create')."'",
  'actions' => [['label'=>'Export','icon'=>'download','action'=>'$wire.exportCsv()']],
  'searchPlaceholder' => 'Cari...',
  'showFiltersToggle' => true,
])
--}}
@props([
  'title',
  'primaryLabel' => 'Buat',
  'primaryAction' => null,
  'actions' => [],
  'searchPlaceholder' => 'Cari...',
  'showFiltersToggle' => true,
])
<div class="flex flex-col lg:flex-row gap-3 p-2 lg:p-3 items-stretch lg:items-center justify-between border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <div class="flex items-center gap-3 min-w-0">
        <h2 class="text-base lg:text-lg font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">{{ $title }}</h2>
        @if (isset($tabs) && is_array($tabs) && count($tabs) > 0)
            <div class="flex items-center gap-1 overflow-x-auto whitespace-nowrap">
                @foreach ($tabs as $k => $label)
                    @php
                        $count = is_array($label) ? ($label['count'] ?? null) : null;
                        $labelText = is_array($label) ? ($label['label'] ?? $k) : $label;
                    @endphp
                    <button wire:click="setActiveTab('{{ $k }}')" class="px-3 py-1.5 text-xs lg:text-sm rounded-md border transition-colors
                        {{ $activeTab === $k
                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 dark:hover:bg-slate-700' }}">
                        {{ $labelText }}@if ($count !== null) <span class="ml-1 opacity-80">({{ $count }})</span>@endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @if ($primaryAction !== null)
            <button wire:click="{{ $primaryAction }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ $primaryLabel }}
            </button>
        @endif
        @foreach ($actions as $a)
            <button wire:click="{{ $a['action'] }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                @if (!empty($a['icon']))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if ($a['icon']==='download')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>@endif
                        @if ($a['icon']==='upload')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>@endif
                        @if ($a['icon']==='refresh-cw')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>@endif
                        @if ($a['icon']==='printer')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>@endif
                        @if ($a['icon']==='send')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>@endif
                        @if ($a['icon']==='repeat')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>@endif
                        @if ($a['icon']==='play')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>@endif
                    </svg>
                @endif
                {{ $a['label'] }}
            </button>
        @endforeach

        <div class="relative">
            <input
                wire:model.live="search"
                type="search"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full sm:w-64 pl-9 pr-3 py-1.5 text-sm rounded-md border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
            <svg class="w-4 h-4 absolute left-2.5 top-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        @if ($showFiltersToggle)
            <button wire:click="$toggle('showFilters')" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 {{ $showFilters ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter @if (isset($this) && method_exists($this,'hasFilter') && $this->hasFilter())<span class="ml-1 inline-flex items-center justify-center px-1.5 text-[10px] rounded-full bg-blue-600 text-white">Aktif</span>@endif
            </button>
        @endif
    </div>
</div>
