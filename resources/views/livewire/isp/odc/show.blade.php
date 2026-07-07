<div class="max-w-7xl mx-auto p-3">
    <!-- Breadcrumb -->
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    <!-- Success/Error Flash Message -->
    <div class="mb-3">
        @if(session()->has('success'))
            <x-feedback.alert variant="success">
                {{ session('success') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('error'))
            <x-feedback.alert variant="danger">
                {{ session('error') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('warning'))
            <x-feedback.alert variant="warning">
                {{ session('warning') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('info'))
            <x-feedback.alert variant="info">
                {{ session('info') }}
            </x-feedback.alert>
        @endif
    </div>

    <div class="flex items-center gap-4 mb-3">
        <a href="{{ route('isp.odcs.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    {{ substr($odc->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $odc->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($odc->status === 'active') bg-green-100 text-green-600
                                    @else bg-slate-100 text-slate-600
                                    @endif">
                                    {{ ucfirst($odc->status) }}
                                </span>
                        <span class="text-sm text-slate-500 font-mono">{{ $odc->code }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.odcs.edit', $odc->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                    Edit
                </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-slate-900">Informasi ODC</h3>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Kode</label>
                        <p class="text-slate-900 font-mono">{{ $odc->code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Nama</label>
                        <p class="text-slate-900">{{ $odc->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">OLT</label>
                        <p class="text-slate-900">{{ $odc->olt->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">POP</label>
                        <p class="text-slate-900">{{ $odc->pop->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Jumlah Port</label>
                        <p class="text-slate-900">{{ $odc->port_count ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Latitude</label>
                        <p class="text-slate-900 font-mono">{{ $odc->latitude ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">Longitude</label>
                        <p class="text-slate-900 font-mono">{{ $odc->longitude ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Alamat</label>
                    <p class="text-slate-900">{{ $odc->address ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 mb-1">Deskripsi</label>
                    <p class="text-slate-900">{{ $odc->description ?? '-' }}</p>
                </div>
            </div>
        </x-base.card>

        <x-base.card>
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-slate-900">Statistik</h3>
            </x-slot>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <p class="text-sm text-slate-500">ODP</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $odc->odps->count() }}</p>
                    </div>
                </div>
            </div>
        </x-base.card>
    </div>
</div>
