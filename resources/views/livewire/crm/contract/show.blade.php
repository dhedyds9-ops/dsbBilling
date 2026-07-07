<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('crm.contracts.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-slate-900">{{ $contract->customer_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($contract->status === 'draft') bg-slate-100 text-slate-600
                    @elseif($contract->status === 'active') bg-green-100 text-green-600
                    @elseif($contract->status === 'expired') bg-yellow-100 text-yellow-600
                    @else bg-red-100 text-red-600
                    @endif
                ">
                    {{ ucfirst($contract->status) }}
                </span>
                <span class="text-sm text-slate-500">{{ $contract->start_date?->format('d/m/Y') }} - {{ $contract->end_date?->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('crm.contracts.edit', $contract->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Informasi Contract</h3>
                </x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Nama Pelanggan</label>
                        <p class="text-slate-900">{{ $contract->customer_name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Mulai</label>
                            <p class="text-slate-900">{{ $contract->start_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Selesai</label>
                            <p class="text-slate-900">{{ $contract->end_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Catatan</label>
                        <p class="text-slate-900">{{ $contract->notes ?? '-' }}</p>
                    </div>
                </div>
            </x-base.card>

            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Timeline</h3>
                </x-slot>
                <div class="space-y-6">
                    @foreach($timeline as $item)
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full 
                                    @if($item['type'] === 'create') bg-primary-500
                                    @else bg-slate-400
                                    @endif
                                "></div>
                                @if(!$loop->last)
                                    <div class="w-0.5 flex-1 bg-slate-200"></div>
                                @endif
                            </div>
                            <div class="flex-1 pb-6">
                                <p class="text-xs text-slate-500 mb-1">{{ $item['date']->format('d/m/Y H:i') }}</p>
                                <p class="font-medium text-slate-900">{{ $item['title'] }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $item['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-base.card>
        </div>

        <div class="space-y-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h3>
                </x-slot>
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-slate-600">{{ substr($activity['user'], 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900">
                                    <span class="font-medium">{{ $activity['user'] }}</span> {{ $activity['action'] }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-full text-xs">{{ $activity['module'] }}</span>
                                    {{ $activity['time'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-base.card>
        </div>
    </div>
</div>
