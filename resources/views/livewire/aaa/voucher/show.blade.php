<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    <div class="flex items-center gap-4">
        <a href="{{ route('aaa.vouchers.index') }}" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    {{ substr($voucher->code, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $voucher->code }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            @if($voucher->status === 'available') bg-green-100 dark:bg-green-900/50 text-green-600
                            @elseif($voucher->status === 'used') bg-blue-100 dark:bg-blue-900/50 text-blue-600
                            @else bg-red-100 dark:bg-red-900/50 text-red-600
                            @endif">
                            {{ ucfirst($voucher->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('aaa.vouchers.edit', $voucher->id) }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi Voucher</h3>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kode</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $voucher->code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Voucher Pool</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $voucher->voucherPool?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ ucfirst($voucher->status) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Dibuat Oleh</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $voucher->createdBy?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Tanggal Kadaluarsa</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $voucher->expires_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </x-base.card>
    </div>
</div>
