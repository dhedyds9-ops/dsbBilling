@section('page_title')
<div class="flex items-center gap-3">
  <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
  <span class="text-lg">Daftar Mikrotik (NAS)</span>
</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

<div class="space-y-5 pb-10">
    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Total --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dns</span>
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total Router</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3">{{ number_format($summary['total'] ?? 0) }}</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                <div class="text-xs text-slate-500 dark:text-slate-400">Semua router yang terdaftar</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        
        {{-- Aktif --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Router Aktif</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3">{{ number_format($summary['active'] ?? 0) }}</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                <div class="text-xs text-slate-500 dark:text-slate-400">Router dalam status beroperasi</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

        {{-- Nonaktif / Offline --}}
        <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-md bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-500 to-gray-400 rounded-t-xl"></div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="absolute top-3 right-3 opacity-10 text-slate-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">offline_bolt</span>
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nonaktif / Offline</h3>
                <div class="text-4xl font-black text-slate-700 dark:text-slate-300 mb-3">{{ number_format($summary['inactive'] ?? 0) }}</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                <div class="text-xs text-slate-500 dark:text-slate-400">Router yang dinonaktifkan</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('isp.routers.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add</span>
                Tambah Router
            </a>
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama atau IP..."
                       class="w-full pl-11 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 text-left font-semibold">Nama & Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Lokasi POP</th>
                        <th class="px-4 py-3 text-left font-semibold">IP Address</th>
                        <th class="px-4 py-3 text-left font-semibold">Sesi Aktif</th>
                        <th class="px-4 py-3 text-left font-semibold">Status & Live</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    @forelse($routers as $router)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    {{ $router->name }}
                                    @if($router->is_default)
                                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 text-[10px] uppercase font-bold rounded-full">Default</span>
                                    @endif
                                </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                                <div class="text-xs text-slate-500 dark:text-slate-400">Kode: {{ $router->kode_router ?? '-' }}</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 dark:text-slate-300">{{ $router->lokasi_pop ?? '-' }}</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-700 dark:text-slate-300">
                                {{ $router->ip_address }}<span class="text-slate-400">:{{ $router->port }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">people</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ number_format($router->active_sessions_count) }}</span>
                                </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($router->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                        </span>
                                    @endif

                                    @if($router->status === 'active')
                                        @if($router->is_online === true)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400" title="API Terhubung">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi</span> Terhubung
                                            </span>
                                        @elseif($router->is_online === false)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400" title="API Terputus">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi_off</span> Terputus
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400" title="Checking...">
                                                <span class="material-symbols-outlined notranslate text-[12px] animate-spin" translate="no">sync</span> Cek...
                                            </span>
                                        @endif
                                    @endif
                                </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="checkConnection({{ $router->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Check Connection">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">network_ping</span>
                                    </button>
                                                                          <button wire:click="generateProvisioningToken({{ $router->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-900/50 text-purple-600 dark:text-purple-400 transition-colors" title="Skrip Auto Config (API & Radius)">
                                          <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">terminal</span>
                                      </button>
                                      <a href="{{ route('isp.routers.edit', $router->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="deleteRouter({{ $router->id }})" wire:confirm="Yakin ingin menghapus router ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-5xl mb-3 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no">router</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Belum ada router</p>
                                <p class="text-sm mt-1">Silakan tambahkan router Mikrotik pertama Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        @if($routers->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $routers->links() }}
            </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
        @endif
    </div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
    @if($showProvisioningModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="closeProvisioningModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modal-title">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">terminal</span>
                        Skrip Auto Config Mikrotik
                    </h3>
                    <button wire:click="closeProvisioningModal" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Jalankan perintah ini di Terminal (New Terminal) pada router Mikrotik Anda. Skrip ini akan secara otomatis mengunduh konfigurasi API dan Radius dari dsBilling dan menerapkannya ke router.
                        </p>
                        <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-[16px]" translate="no">warning</span>
                                Token ini hanya berlaku 1 kali dan kedaluwarsa dalam {{ $provisioningExpires }}.
                            </p>
                        </div>
                    </div>
                    <div class="relative bg-slate-900 rounded-xl p-4 overflow-hidden border border-slate-700 group">
                        <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-slate-400 text-xs font-mono ml-2">RouterOS Terminal</span>
                        </div>
                        <code class="text-emerald-400 text-sm font-mono break-all whitespace-pre-wrap select-all">/tool fetch url="{{ url('/api/v1/provision/router') }}" http-header-field="Authorization: Bearer {{ $provisioningToken }}" dst-path="provision.rsc"; /import provision.rsc; /file remove provision.rsc;</code>
                        
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="navigator.clipboard.writeText('/tool fetch url=\'{{ url('/api/v1/provision/router') }}\' http-header-field=\'Authorization: Bearer {{ $provisioningToken }}\' dst-path=\'provision.rsc\'; /import provision.rsc; /file remove provision.rsc;'); alert('Skrip disalin!');" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg shadow-sm border border-slate-600 transition-colors" title="Copy to clipboard">
                                <span class="material-symbols-outlined notranslate text-[18px]" translate="no">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                    <button type="button" wire:click="closeProvisioningModal" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

