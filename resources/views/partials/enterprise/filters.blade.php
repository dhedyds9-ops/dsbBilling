{{--
Filter Grid SSOT - drop-in filter panel
Usage:
@include('partials.enterprise.filters', [
  'filters' => [
    ['key'=>'router_id','label'=>'Router','type'=>'select','options'=>$routers],
    ['key'=>'status','label'=>'Status','type'=>'select','options'=>['active'=>'Aktif','nonactive'=>'Nonaktif']],
    ['key'=>'start_date','label'=>'Mulai','type'=>'date'],
    ['key'=>'end_date','label'=>'Selesai','type'=>'date'],
  ]
])
--}}
@props(['filters' => []])
@if (count($filters) > 0)
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 p-2 lg:p-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
    @foreach ($filters as $f)
        <div class="min-w-0">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">{{ $f['label'] }}</label>
            @if (($f['type'] ?? 'select') === 'select')
                <select wire:model.live="filters.{{ $f['key'] }}" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2">
                    <option value="">Semua</option>
                    @foreach ($f['options'] ?? [] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            @elseif (($f['type'] ?? '') === 'date')
                <input wire:model.live="filters.{{ $f['key'] }}" type="date" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2">
            @elseif (($f['type'] ?? '') === 'number')
                <input wire:model.live="filters.{{ $f['key'] }}" type="number" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2">
            @elseif (($f['type'] ?? '') === 'text')
                <input wire:model.live="filters.{{ $f['key'] }}" type="text" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2">
            @endif
        </div>
    @endforeach
    <div class="flex items-end gap-2">
        <button wire:click="resetFilters" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200 text-slate-700">Reset</button>
    </div>
</div>
@endif
