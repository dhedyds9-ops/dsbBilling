<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('billing.payments.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $payment->reference_number }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            @if($payment->status === 'success') bg-green-100 text-green-600
                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-600
                            @elseif($payment->status === 'failed') bg-red-100 text-red-600
                            @else bg-slate-100 text-slate-600
                            @endif
                        ">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('billing.payments.edit', $payment->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Detail Payment</h3>
                </x-slot>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Customer</label>
                            <p class="text-slate-900">{{ $payment->customer?->name ?? '-' }}</p>
                            <p class="text-sm text-slate-500">{{ $payment->customer?->email ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Amount</label>
                            <p class="text-slate-900 font-bold text-xl">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Metode</label>
                            <p class="text-slate-900">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Payment</label>
                            <p class="text-slate-900">{{ $payment->paid_at?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Gateway</label>
                            <p class="text-slate-900">{{ ucfirst($payment->gateway) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Currency</label>
                            <p class="text-slate-900">{{ $payment->currency }}</p>
                        </div>
                    </div>
                </div>
            </x-base.card>

            @if($payment->invoices->count() > 0)
                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Invoice Terkait</h3>
                    </x-slot>
                    <div class="space-y-4">
                        @foreach($payment->invoices as $invoice)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $invoice->issue_date?->format('d/m/Y') ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($invoice->status === 'paid') bg-green-100 text-green-600
                                        @else bg-yellow-100 text-yellow-600
                                        @endif
                                    ">
                                        {{ ucfirst($invoice->status) }}
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
