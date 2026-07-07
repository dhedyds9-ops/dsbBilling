<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                {{ $internet_status['status'] === 'online' ? '🟢' : '🔴' }}
                {{ $internet_status['message'] }}
            </div>
            <div class="text-gray-600 text-sm">
                Status Koneksi Internet
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                Rp {{ number_format($total_outstanding, 2, ',', '.') }}
            </div>
            <div class="text-gray-600 text-sm">
                Tagihan Outstanding
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                {{ count($active_invoices) }}
            </div>
            <div class="text-gray-600 text-sm">
                Tagihan Aktif
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                {{ count($customer_services) }}
            </div>
            <div class="text-gray-600 text-sm">
                Layanan Aktif
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tagihan Terbaru</h2>
            <ul>
                @foreach($recent_invoices as $invoice)
                <li class="border-b border-gray-200 py-3 flex justify-between">
                    <div>
                        <div class="font-medium">{{ $invoice->invoice_number }}</div>
                        <div class="text-sm text-gray-600">{{ $invoice->due_date?->format('d/m/Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium">Rp {{ number_format($invoice->total_amount, 2, ',', '.') }}</div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Layanan Aktif</h2>
            <ul>
                @foreach($customer_services as $service)
                <li class="border-b border-gray-200 py-3">
                    <div class="font-medium">{{ $service->serviceInstance?->serviceProfile?->name ?? 'Layanan' }}</div>
                    <div class="text-sm text-gray-600">Status: {{ ucfirst($service->status) }}</div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

