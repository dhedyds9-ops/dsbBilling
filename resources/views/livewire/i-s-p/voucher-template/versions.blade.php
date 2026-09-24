<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Riwayat Versi: {{ $template->name }}
            </h2>
            <a href="{{ route('isp.voucher-templates.edit', $template->id) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:bg-gray-900/50">
                Kembali
            </a>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="relative border-l border-gray-200 dark:border-gray-700 ml-3">
                        @foreach($template->versions as $version)
                            <div class="mb-10 ml-6">
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 dark:bg-blue-900/50 rounded-full -left-3 ring-8 ring-white">
                                    <span class="text-xs font-bold text-blue-800">v{{ $version->version }}</span>
                                </span>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-bold text-gray-900 dark:text-gray-100">{{ $version->settings['changelog'] ?? 'Tidak ada catatan' -: 'Tidak ada catatan perubahan' }}</h3>
                                            <time class="block mb-2 text-sm font-normal leading-none text-gray-400">
                                                {{ $version->created_at->format('d M Y H:i') }} &bull; oleh {{ $version->createdBy?->name ?? 'System' }}
                                            </time>
                                        </div>
                                        @if($loop->first)
                                            <x-ui.badge variant="success">Current Version</x-ui.badge>
                                        @else
                                            <button wire:click="rollback({{ $version->id }})" wire:confirm="Yakin ingin melakukan rollback ke versi ini- Versi saat ini akan disimpan sebagai riwayat." class="text-xs bg-white dark:bg-slate-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-900/50 px-3 py-1 rounded">
                                                Rollback ke versi ini
                                            </button>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <details class="text-sm">
                                            <summary class="cursor-pointer text-primary-600 font-medium">Lihat Source Code</summary>
                                            <div class="mt-2 p-3 bg-gray-800 text-gray-100 rounded overflow-x-auto whitespace-pre font-mono text-xs">
                                                {{ $version->template_code }}
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>







