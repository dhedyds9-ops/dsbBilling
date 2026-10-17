<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                {{ __('Template Voucher') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('isp.voucher-templates.import') }}" class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">Import</a>
                <a href="{{ route('isp.voucher-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">Buat Template</a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">{{ $template->name }}</h3>
                                @if($template->is_default)
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-semibold rounded-full flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">star</span> Default
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">
                                {{ $template->category instanceof \BackedEnum ? $template->category->value : $template->category }}
                            </p>
                        </div>
                        @if($template->is_system)
                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs rounded-full">System</span>
                        @endif
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-xs font-medium">
                            <a href="{{ route('isp.voucher-templates.preview', $template->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-semibold flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-xs">visibility</span> Preview
                            </a>
                            <a href="{{ route('isp.voucher-templates.edit', $template->id) }}" class="text-slate-600 dark:text-slate-300 hover:text-primary-600">
                                Edit
                            </a>
                            <button wire:click="duplicate({{ $template->id }})" class="text-slate-600 dark:text-slate-300 hover:text-primary-600">
                                Duplikat
                            </button>
                            <a href="{{ route('isp.voucher-templates.versions', $template->id) }}" class="text-slate-600 dark:text-slate-300 hover:text-primary-600">
                                Versi
                            </a>
                            @if(!$template->is_system)
                                <button wire:click="delete({{ $template->id }})" wire:confirm="Yakin ingin menghapus template ini?" class="text-red-500 hover:text-red-700">
                                    Hapus
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            @if(!$template->is_system)
                                <button wire:click="toggleActive({{ $template->id }})" class="{{ $template->is_active ? 'text-green-600' : 'text-slate-400' }}" title="Toggle Aktif">
                                    <span class="material-symbols-outlined text-base">{{ $template->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                </button>
                            @endif
                            @if(!$template->is_default && $template->is_active)
                                <button wire:click="setAsDefault({{ $template->id }})" class="text-amber-500 hover:text-amber-600" title="Jadikan Default">
                                    <span class="material-symbols-outlined text-base">star</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 p-8 text-center text-slate-500 dark:text-slate-400 rounded-lg shadow space-y-3">
                <p class="text-base font-medium text-slate-700 dark:text-slate-300">Belum ada template voucher yang terpasang di sistem.</p>
                <p class="text-xs text-slate-400">Klik tombol di bawah ini untuk memasang template bawaan (Kartu Modern A4, Struk Kasir Thermal, & Hemat Tinta).</p>
                <div class="pt-2">
                    <button wire:click="seedDefaultTemplates" type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs rounded-lg transition-all shadow-md hover:shadow-lg">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">magic_button</span> Pasang Template Default Sekarang
                    </button>
                </div>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</div>
