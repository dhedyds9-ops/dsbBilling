<div>
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Import Legacy Template') }}
        </h2>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-slate-900 dark:text-slate-100">
                    
                    <div class="mb-6 bg-blue-50 dark:bg-blue-900/30 p-4 rounded border border-blue-200">
                        <h3 class="text-blue-800 font-bold mb-2">Sistem Migrasi Otomatis</h3>
                        <p class="text-primary-700 text-sm">
                            Paste source code dari template Mikhail atau template radius legacy Anda di sini. 
                            Sistem akan secara otomatis mengubah tag variabel lama (seperti <code>$vs['username']</code>) menjadi sintaks modern dsBilling (<code>$username</code>).
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left: Form -->
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" :value="__('Nama Template')" />
                                <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" placeholder="Misal: Template Biru Lama" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="category" :value="__('Kategori')" />
                                <select id="category" wire:model="category" class="mt-1 block w-full border-slate-300 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                                    <option value="hotspot">Hotspot</option>
                                    <option value="pppoe">PPPoE</option>
                                </select>
                                <x-input-error :messages="$errors->get('category')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="legacyContent" :value="__('Legacy Source Code (HTML/Smarty)')" />
                                <textarea id="legacyContent" wire:model.defer="legacyContent" class="mt-1 block w-full h-64 font-mono text-sm border-slate-300 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Paste kode HTML Anda di sini..."></textarea>
                                <x-input-error :messages="$errors->get('legacyContent')" class="mt-2" />
                            </div>

                            <div class="flex space-x-2">
                                <button wire:click="previewConversion" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                                    Preview Konversi
                                </button>
                                
                                @if($convertedContent)
                                <button wire:click="import" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Import Sekarang
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Preview / Result -->
                        <div>
                            @if(count($warnings) > 0)
                                <div class="mb-4 bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-md border border-yellow-200">
                                    <h4 class="text-yellow-800 font-bold text-sm mb-2">Peringatan Konversi:</h4>
                                    <ul class="list-disc pl-5 text-sm text-yellow-700">
                                        @foreach($warnings as $warning)
                                            <li>{{ $warning }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($convertedContent)
                                <div>
                                    <x-input-label :value="__('Hasil Konversi (Modern Syntax)')" />
                                    <div class="mt-1 p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-md h-64 overflow-auto font-mono text-xs text-slate-800 dark:text-slate-200 whitespace-pre">
                                        {{ $convertedContent }}
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Kode ini yang akan disimpan dan dapat diedit lebih lanjut di Template Editor.</p>
                                </div>
                            @else
                                <div class="h-full flex items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-900/50 p-6 text-center text-slate-500 dark:text-slate-400">
                                    Silakan masukkan source code legacy dan klik "Preview Konversi" untuk melihat hasilnya.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>








