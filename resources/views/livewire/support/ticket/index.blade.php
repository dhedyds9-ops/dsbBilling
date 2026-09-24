<?php
/** @var \App\Livewire\Support\Ticket\Index $this */
/** @var mixed $rows */
$summaryItems = $this->getSummaryItems();
$toolbarActions = $this->getToolbarActions();
$bulkActions = $this->getBulkActions();
$filterConfig = $this->getFilterConfig();
$statusBadge = [
    'open' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border-blue-100 dark:border-blue-800',
    'in_progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border-amber-100 dark:border-amber-800',
    'pending_customer' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border-purple-100 dark:border-purple-800',
    'resolved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800',
    'closed' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
];
$priorityBadge = [
    'low' => 'bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    'critical' => 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
$statusLabels = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'pending_customer' => 'Pending Customer',
    'resolved' => 'Resolved',
    'closed' => 'Closed',
];
?>
<div class="flex flex-col h-full min-h-0 bg-slate-50 dark:bg-slate-900">

    @include('partials.enterprise.list-toolbar', [
        'title' => 'Support > Tiket Support',
        'primaryAction' => null,
        'actions' => $toolbarActions,
        'searchPlaceholder' => 'Cari judul, customer, ticket ID...',
        'showFiltersToggle' => true,
        'tabs' => $this->tabs,
    ])

    @include('partials.enterprise.summary-cards', ['items' => $summaryItems])

    @if ($this->showFilters)
        @include('partials.enterprise.filters', ['filters' => $filterConfig])
    @endif

    @include('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions])

    @if ($this->errorMessage)
        <div class="px-3 py-2 bg-red-50 border-b border-red-100 dark:bg-red-900/30 dark:border-red-800 text-red-700 dark:text-red-200 text-sm">{{ $this->errorMessage }}</div>
    @endif

    @if ($this->loading)
        <div class="flex-1 flex items-center justify-center py-16">
            <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Memuat data...
            </div>
        </div>
    @elseif ($this->activeTab === 'kanban')
        <div class="flex-1 overflow-auto min-h-0 p-3">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 min-w-max">
                @foreach (['open', 'in_progress', 'pending_customer', 'resolved', 'closed'] as $status)
                    @php
                        $cards = $this->kanbanData[$status] ?? [];
                        $count = is_countable($cards) ? count($cards) : 0;
                        $colColor = match($status) {
                            'open' => 'border-blue-200 dark:border-blue-800',
                            'in_progress' => 'border-amber-200 dark:border-amber-800',
                            'pending_customer' => 'border-purple-200 dark:border-purple-800',
                            'resolved' => 'border-emerald-200 dark:border-emerald-800',
                            'closed' => 'border-slate-200 dark:border-slate-700',
                        };
                    @endphp
                    <div class="w-72 flex-shrink-0 flex flex-col bg-white dark:bg-slate-800 border {{ $colColor }} rounded-lg">
                        <div class="px-3 py-2 border-b {{ $colColor }} flex items-center justify-between sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <span class="text-xs font-semibold uppercase tracking-wide">{{ $statusLabels[$status] }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700">{{ $count }}</span>
                        </div>
                        <div class="p-2 space-y-2 flex-1 overflow-y-auto max-h-[70vh]">
                            @forelse ($cards as $t)
                                <div class="p-2 rounded-md border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50 hover:shadow-sm cursor-pointer" wire:click="rowDetail({{ $t->id }})">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-mono text-[10px] text-slate-400">#{{ $t->id }} · {{ substr($t->uuid, -6) }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $priorityBadge[$t->priority] ?? '' }}">{{ strtoupper($t->priority) }}</span>
                                    </div>
                                    <div class="font-medium text-sm mt-1 line-clamp-2 text-slate-800 dark:text-slate-100">{{ $t->title }}</div>
                                    <div class="mt-1.5 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500 dark:text-slate-400 truncate max-w-[60%]">{{ $t->customer?->name ?? '-' }}</span>
                                        @if ($t->assignedTo)
                                            <span class="flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                                <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px]">{{ strtoupper(substr($t->assignedTo->name, 0, 1)) }}</span>
                                                <span class="truncate max-w-[60px]">{{ $t->assignedTo->name }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Unassigned</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-[10px] text-slate-400">
                                        {{ \Illuminate\Support\Carbon::parse($t->created_at)->diffForHumans() }}
                                        @if ($t->due_date)
                                            <span class="ml-2 {{ \Illuminate\Support\Carbon::parse($t->due_date)->isPast() ? 'text-red-500 font-medium' : '' }}">Due {{ \Illuminate\Support\Carbon::parse($t->due_date)->format('d/m') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-slate-400 italic">Kosong</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif ($this->activeTab === 'timeline')
        <div class="flex-1 overflow-auto min-h-0 p-3">
            @if ($this->timelineData && $this->timelineTicketId)
                <div class="max-w-3xl mx-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs text-slate-500 dark:text-slate-400">#{{ $this->timelineData['ticket']->id }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $statusBadge[$this->timelineData['ticket']->status] ?? '' }} border">{{ $statusLabels[$this->timelineData['ticket']->status] ?? $this->timelineData['ticket']->status }}</span>
                            </div>
                            <h3 class="font-semibold mt-1">{{ $this->timelineData['ticket']->title }}</h3>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $this->timelineData['ticket']->customer?->name ?? '-' }} · {{ $this->timelineData['ticket']->category }} · Priority <span class="font-medium">{{ $this->timelineData['ticket']->priority }}</span></div>
                        </div>
                        <button wire:click="$set('timelineTicketId', null)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-4">
                        @php
                            $events = $this->timelineData['events'] ?? [];
                        @endphp
                        <div class="relative pl-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-5">
                            @foreach ($events as $idx => $ev)
                                @php
                                    $typeIcon = match($ev['type']) {
                                        'create' => 'text-blue-500 bg-blue-100 dark:bg-blue-900/50',
                                        'assign' => 'text-amber-500 bg-amber-100 dark:bg-amber-900/50',
                                        'comment' => 'text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700',
                                        'resolve' => 'text-emerald-500 bg-emerald-100 dark:bg-emerald-900/50',
                                        'close' => 'text-slate-700 dark:text-slate-300 bg-slate-200 dark:bg-slate-600',
                                        default => 'text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800',
                                    };
                                @endphp
                                <div class="relative">
                                    <div class="absolute -left-[30px] top-1 w-6 h-6 rounded-full flex items-center justify-center {{ $typeIcon }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($ev['type']==='create')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            @elseif($ev['type']==='assign')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            @elseif($ev['type']==='resolve')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            @elseif($ev['type']==='close')<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            @else<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @endif
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-semibold">{{ $ev['title'] }}</span>
                                        <span class="text-slate-400">oleh {{ $ev['by'] }}</span>
                                        <span class="ml-auto text-slate-400">{{ \Illuminate\Support\Carbon::parse($ev['time'])->diffForHumans() }} · {{ \Illuminate\Support\Carbon::parse($ev['time'])->format('d/m H:i') }}</span>
                                    </div>
                                    @if (!empty($ev['detail']))
                                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/40 rounded-md p-2 border border-slate-100 dark:border-slate-700">{{ $ev['detail'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="h-full flex items-center justify-center text-slate-400 text-sm">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Klik ticket di Table/Kanban untuk lihat timeline
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800 sticky top-0 z-10">
                    <tr class="text-slate-600 dark:text-slate-300 text-xs uppercase tracking-wide">
                        <th class="w-10 px-3 py-2"><input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('id')">Ticket ID</th>
                        <th class="px-3 py-2 text-left">Title</th>
                        <th class="px-3 py-2 text-left">Customer</th>
                        <th class="px-3 py-2 text-left">Category</th>
                        <th class="px-3 py-2 text-left">Priority</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Assignee</th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('due_date')">Due</th>
                        <th class="px-3 py-2 text-left cursor-pointer" wire:click="sortBy('updated_at')">Last Update</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @php
                        $pageRows = $rows instanceof \Illuminate\Pagination\LengthAwarePaginator ? $rows->items() : (is_array($rows) ? $rows : $rows->all());
                    @endphp
                    @forelse ($pageRows as $t)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/40">
                            <td class="px-3 py-2"><input type="checkbox" wire:model.live="selected" value="{{ (string) $t->id }}" class="rounded border-slate-300 dark:border-slate-600"></td>
                            <td class="px-3 py-2 font-mono text-xs">
                                <span class="text-slate-500 dark:text-slate-400">#{{ $t->id }}</span>
                                <span class="text-slate-400 block">{{ substr($t->uuid, -6) }}</span>
                            </td>
                            <td class="px-3 py-2 max-w-xs truncate font-medium">{{ $t->title }}</td>
                            <td class="px-3 py-2 text-xs">{{ $t->customer?->name ?? '-' }}</td>
                            <td class="px-3 py-2 text-xs capitalize">{{ $t->category }}</td>
                            <td class="px-3 py-2"><span class="text-[10px] px-2 py-0.5 rounded-full {{ $priorityBadge[$t->priority] ?? '' }}">{{ strtoupper($t->priority) }}</span></td>
                            <td class="px-3 py-2"><span class="text-[10px] px-2 py-0.5 rounded-full border {{ $statusBadge[$t->status] ?? '' }}">{{ $statusLabels[$t->status] ?? $t->status }}</span></td>
                            <td class="px-3 py-2 text-xs">
                                @if ($t->assignedTo)
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px] font-semibold">{{ strtoupper(substr($t->assignedTo->name,0,1)) }}</span>
                                        <span class="truncate max-w-[80px]">{{ $t->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[10px]">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs {{ $t->due_date && \Illuminate\Support\Carbon::parse($t->due_date)->isPast() && !in_array($t->status, ['resolved','closed']) ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                {{ $t->due_date ? \Illuminate\Support\Carbon::parse($t->due_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Carbon::parse($t->updated_at)->diffForHumans() }}</td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-0.5">
                                    <button wire:click="rowDetail({{ $t->id }})" class="p-1 text-slate-500 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded dark:hover:bg-blue-900/30" title="Detail/Timeline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="rowEdit({{ $t->id }})" class="p-1 text-slate-500 dark:text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded dark:hover:bg-blue-900/30" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="rowAssign({{ $t->id }})" class="p-1 text-slate-500 dark:text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:bg-amber-900/30 rounded dark:hover:bg-amber-900/30" title="Assign">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </button>
                                    @if (!in_array($t->status, ['resolved','closed']))
                                        <button wire:click="rowResolve({{ $t->id }})" class="p-1 text-slate-500 dark:text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:bg-emerald-900/30 rounded dark:hover:bg-emerald-900/30" title="Resolve">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @endif
                                    @if ($t->status !== 'closed')
                                        <button wire:click="rowClose({{ $t->id }})" class="p-1 text-slate-500 hover:text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 rounded dark:hover:bg-slate-700" title="Close">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                    <button wire:click="rowDelete({{ $t->id }})" class="p-1 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/30 rounded dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="100" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <div class="font-medium">Tidak ada tiket</div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 flex items-center justify-between text-sm">
                <div class="text-slate-500 dark:text-slate-400 text-xs">Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }}</div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-1 px-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
                    </select>
                    {{ $rows->links('livewire::simple-tailwind') }}
                </div>
            </div>
        @endif
    @endif

    @include('partials.enterprise.confirm-modal')

    @if ($this->showBulkAssign)
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700"><h3 class="font-semibold">Bulk Assign Teknisi</h3><p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ count($this->selected) }} ticket terpilih</p></div>
                <div class="px-5 py-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pilih Petugas</label>
                        <select wire:model.live="bulkAssigneeId" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih --</option>
                            @foreach (($this->filterOptions['assignees'] ?? []) as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2">
                    <button @click="show = false; $wire.cancelBulkAssign()" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Batal</button>
                    <button wire:click="submitBulkAssign" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Assign</button>
                </div>
            </div>
        </div>
    @endif

    @if ($this->showCreateForm)
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div x-show="show" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-[90vh] overflow-auto">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between sticky top-0 bg-white dark:bg-slate-800">
                    <h3 class="font-semibold">Buat Ticket Baru</h3>
                    <button @click="show = false; $wire.closeCreateForm()" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Customer *</label>
                            <select wire:model.live="newTicket.customer_id" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih --</option>
                                @foreach (($this->filterOptions['customers'] ?? []) as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Title *</label>
                            <input wire:model.live="newTicket.title" type="text" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium mb-1">Deskripsi</label>
                            <textarea wire:model.live="newTicket.description" rows="3" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kategori</label>
                            <select wire:model.live="newTicket.category" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                                @foreach (($this->filterOptions['categories'] ?? []) as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Priority</label>
                            <select wire:model.live="newTicket.priority" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                                @foreach (($this->filterOptions['priorities'] ?? []) as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Assignee</label>
                            <select wire:model.live="newTicket.assigned_to" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Opsional --</option>
                                @foreach (($this->filterOptions['assignees'] ?? []) as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Due Date</label>
                            <input wire:model.live="newTicket.due_date" type="date" class="w-full text-sm rounded-md border border-slate-200 dark:border-slate-600 dark:bg-slate-700 py-2 px-3 dark:bg-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-2 sticky bottom-0">
                    <button @click="show = false; $wire.closeCreateForm()" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700">Batal</button>
                    <button wire:click="submitCreate" class="px-4 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Buat Ticket</button>
                </div>
            </div>
        </div>
    @endif
</div>
