<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Template Voucher') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('isp.voucher-templates.import') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Import Legacy
                </a>
                <a href="{{ route('isp.voucher-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">
                    + Template Baru
                </a>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 mb-6 rounded-lg shadow flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px]">
                    <x-text-input wire:model.live.debounce.300ms="search" placeholder="Cari template..." class="w-full"/>
                </div>
                <div>
                    <select wire:model.live="category" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Kategori</option>
                        <option value="wifi">WiFi / Hotspot</option><option value="classic">Classic</option><option value="modern">Modern</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="type" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Tipe</option>
                        <option value="system">System (Bawaan)</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($templates as $template)
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg flex flex-col relative">
                        @if($template->is_system)
                            <div class="absolute top-0 right-0 bg-gray-800 text-white text-xs px-2 py-1 rounded-bl-lg z-10">
                                System
                            </div>
                        @endif
                        @if($template->is_default)
                            <div class="absolute top-0 left-0 bg-primary-600 text-white text-xs px-2 py-1 rounded-br-lg z-10">
                                Default
                            </div>
                        @endif
                        
                        <div class="p-4 bg-gray-100 dark:bg-gray-800 flex items-center justify-center min-h-[150px] relative overflow-hidden">
                            @if($template->preview_image)
                                <img src="{{ Storage::url($template->preview_image) }}" alt="Preview" class="max-w-full max-h-full object-contain">
                            @else
                                <div class="text-gray-400 text-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">receipt_long</span>
                                    Tidak ada preview
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                <a href="{{ route('isp.voucher-templates.preview', $template->id) }}" class="bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200 px-4 py-2 rounded font-bold text-sm">Preview</a>
                            </div>
                        </div>

                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200 mb-1">{{ $template->name }}</h3>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span class="capitalize">{{ $template->category->value }}</span> &bull; 
                                v{{ $template->latestVersion?->version ?? 1 }}
                            </div>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-400 flex-1 line-clamp-2">{{ $template->description }}</p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <div class="flex space-x-2">
                                    @if(!$template->is_system)
                                        <a href="{{ route('isp.voucher-templates.edit', $template->id) }}" class="text-primary-600 hover:text-blue-900" title="Edit">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </a>
                                        <button wire:click="delete({{ $template->id }})" wire:confirm="Yakin ingin menghapus template ini ?? " class="text-red-600 hover:text-red-900" title="Hapus">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                        <a href="{{ route('isp.voucher-templates.versions', $template->id) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-100" title="Riwayat Versi">
                                            <span class="material-symbols-outlined text-sm">history</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs italic">System (Read-only)</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="duplicate({{ $template->id }})" class="text-gray-600 hover:text-gray-900 dark:text-gray-100" title="Duplikasi">
                                        <span class="material-symbols-outlined text-sm">content_copy</span>
                                    </button>
                                    @if(!$template->is_system)
                                        <button wire:click="toggleActive({{ $template->id }})" class="{{ $template->is_active ?'text-green-600' : 'text-gray-400' }}" title="Toggle Aktif">
                                            <span class="material-symbols-outlined text-sm">{{ $template->is_active ?'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    @endif
                                    @if(!$template->is_default && $template->is_active)
                                        <button wire:click="setAsDefault({{ $template->id }})" class="text-primary-600 hover:text-blue-900" title="Jadikan Default">
                                            <span class="material-symbols-outlined text-sm">star</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-slate-800 p-8 text-center text-gray-500 dark:text-gray-400 rounded-lg shadow">
                        Tidak ada template yang ditemukan.
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</div>









