<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Neraca Saldo</h1>
            <p class="mt-1 text-sm text-slate-500">Laporan neraca saldo untuk periode tertentu</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </button>
            <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    {{-- Filter Periode --}}
    <x-base.card>
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 md:flex-initial">
                <label class="block text-sm font-medium text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="filters.start_date" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="flex-1 md:flex-initial">
                <label class="block text-sm font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="filters.end_date" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                Tampilkan
            </button>
        </div>
    </x-base.card>

    {{-- Balance Status --}}
    <x-base.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Status Balance</p>
                <p class="text-lg font-semibold {{ $totals['is_balanced'] ? 'text-green-600' : 'text-red-600' }}">
                    {{ $totals['is_balanced'] ? 'Seimbang' : 'Tidak Seimbang' }}
                </p>
            </div>
            <div class="flex gap-8">
                <div class="text-right">
                    <p class="text-sm text-slate-500">Total Debit</p>
                    <p class="text-xl font-bold text-slate-900">Rp {{ number_format($totals['debit'], 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">Total Kredit</p>
                    <p class="text-xl font-bold text-slate-900">Rp {{ number_format($totals['credit'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </x-base.card>

    {{-- Trial Balance Table --}}
    <x-base.card :padding="false">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Kode Akun</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Akun</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($trialBalance as $section)
                        {{-- Section Header --}}
                        <tr class="bg-slate-100">
                            <td colspan="4" class="px-6 py-3">
                                <span class="font-semibold text-slate-900">{{ $section['label'] }}</span>
                            </td>
                        </tr>
                        
                        {{-- Section Items --}}
                        @foreach($section['items'] as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $item['code'] }}</td>
                                <td class="px-6 py-3 text-sm text-slate-900">{{ $item['name'] }}</td>
                                <td class="px-6 py-3 text-sm text-slate-900 text-right">
                                    {{ $item['debit'] > 0 ? 'Rp ' . number_format($item['debit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-900 text-right">
                                    {{ $item['credit'] > 0 ? 'Rp ' . number_format($item['credit'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                        
                        {{-- Section Total --}}
                        <tr class="bg-slate-50 font-semibold">
                            <td colspan="2" class="px-6 py-3 text-sm text-slate-900">Total {{ $section['label'] }}</td>
                            <td class="px-6 py-3 text-sm text-slate-900 text-right">
                                {{ $section['total_debit'] > 0 ? 'Rp ' . number_format($section['total_debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-900 text-right">
                                {{ $section['total_credit'] > 0 ? 'Rp ' . number_format($section['total_credit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                    
                    {{-- Grand Total --}}
                    <tr class="bg-primary-50 font-bold">
                        <td colspan="2" class="px-6 py-4 text-lg text-primary-900">Grand Total</td>
                        <td class="px-6 py-4 text-lg text-primary-900 text-right">
                            Rp {{ number_format($totals['debit'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-lg text-primary-900 text-right">
                            Rp {{ number_format($totals['credit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-base.card>
</div>
