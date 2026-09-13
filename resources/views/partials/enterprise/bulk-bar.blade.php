{{--
Bulk Action Bar SSOT
--}}
@props(['bulkActions' => []])
@if (isset($this) && $this->selected && count($this->selected) > 0)
<div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/30 border-b border-blue-100 dark:border-blue-800">
    <div class="text-sm text-blue-900 dark:text-blue-100">
        <span class="font-semibold">{{ count($this->selected) }}</span> data terpilih
    </div>
    <div class="flex flex-wrap items-center gap-1.5">
        @foreach ($bulkActions as $a)
            <button wire:click="applyBulk('{{ $a['key'] }}')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md
                {{ $a['variant'] ?? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700' }}">
                {{ $a['label'] }}
            </button>
        @endforeach
        <button wire:click="$set('selected', [])" class="px-2.5 py-1 text-xs rounded-md text-slate-600 hover:bg-white dark:bg-slate-800/70 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
    </div>
</div>
@endif
