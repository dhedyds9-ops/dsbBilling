<div class="max-w-7xl mx-auto p-3">
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

    <div class="space-y-6">
        <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.vendors.index') }}" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                        {{ substr($vendor->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                @if($vendor->status === 'active') bg-green-100 dark:bg-green-900/50 text-green-600
                                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                @endif">
                                {{ ucfirst($vendor->status) }}
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $vendor->code }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('isp.vendors.edit', $vendor->id) }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                    Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi Vendor</h3>
                </x-slot>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kode</label>
                            <p class="text-slate-900 dark:text-slate-100 font-mono">{{ $vendor->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Nama</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $vendor->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kontak Person</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $vendor->contact_person ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Telepon</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $vendor->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Email</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $vendor->email ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Alamat</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $vendor->address ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Deskripsi</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $vendor->description ?? '-' }}</p>
                    </div>
                </div>
            </x-base.card>

            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Statistik Perangkat</h3>
                </x-slot>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">OLT</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->olts->count() }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">ONU</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->onus->count() }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Splitter</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->splitters->count() }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Router</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->routers->count() }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Switch</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->switches->count() }}</p>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Access Point</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendor->accessPoints->count() }}</p>
                    </div>
                </div>
            </x-base.card>
        </div>
    </div>
</div>
