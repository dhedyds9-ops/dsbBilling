<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('billing.invoices.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_number }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            @if($invoice->status === 'paid') bg-green-100 text-green-600
                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-600
                            @elseif($invoice->status === 'overdue') bg-red-100 text-red-600
                            @else bg-slate-100 text-slate-600
                            @endif
                        ">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('billing.invoices.edit', $invoice->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Detail Invoice</h3>
                </x-slot>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Customer</label>
                            <p class="text-slate-900">{{ $invoice->customer?->name ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->customer?->email ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Invoice</label>
                            <p class="text-slate-900">{{ $invoice->issue_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Jatuh Tempo</label>
                            <p class="text-slate-900">{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Total Amount</label>
                            <p class="text-slate-900 font-bold text-xl">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </x-base.card>

            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Items</h3>
                </x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Deskripsi</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Harga Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-900">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-900 text-right font-medium">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50">
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Total</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-base.card>

            @if($invoice->payments->count() > 0)
                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Pembayaran</h3>
                    </x-slot>
                    <div class="space-y-4">
                        @foreach($invoice->payments as $payment)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $payment->reference_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $payment->paid_at?->format('d/m/Y') ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-600">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-base.card>
            @endif

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
                                <p class="text-xs text-slate-500 mb-1">{{ $item['date']?->format('d/m/Y H:i') ?? '-' }}</p>
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
