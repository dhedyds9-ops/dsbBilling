<div>
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                    @if($template)
                        {{ __('Edit Template') }}: <span class="text-primary-600">{{ $template->name }}</span>
                    @else
                        {{ __('Buat Template Voucher Baru') }}
                    @endif
                </h2>
                @if($is_system)
                    <span class="px-2 py-0.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-full">
                        Sistem (Read-Only)
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Kustomisasi tata letak, gaya visual, dan variabel data voucher untuk cetak A4 maupun thermal POS.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('isp.voucher-templates.index') }}" class="inline-flex items-center px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50">
                Kembali
            </a>

            @if($template)
                <a href="{{ route('isp.voucher-templates.preview', $template->id) }}" class="inline-flex items-center px-3.5 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 dark:bg-indigo-900/30 dark:border-indigo-700 dark:text-indigo-300 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-100">
                    <span class="material-symbols-outlined text-sm mr-1">visibility</span> Preview
                </a>
                <a href="{{ route('isp.voucher-templates.versions', $template->id) }}" class="inline-flex items-center px-3.5 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-slate-200">
                    <span class="material-symbols-outlined text-sm mr-1">history</span> Riwayat Versi
                </a>
            @endif

            @if($is_system)
                <button wire:click="duplicateTemplate" type="button" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 shadow-sm">
                    <span class="material-symbols-outlined text-sm mr-1">content_copy</span> Duplikat Salinan
                </button>
            @else
                <button wire:click="save" type="button" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 shadow-sm">
                    <span class="material-symbols-outlined text-sm mr-1">save</span> Simpan Template
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-green-600">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200 text-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-red-600">error</span>
            {{ session('error') }}
        </div>
    @endif

    @if($is_system)
        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-200 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined text-amber-600 mt-0.5">lock</span>
            <div>
                <p class="font-bold">Template Bawaan Sistem (Read-Only)</p>
                <p class="text-xs mt-0.5 text-amber-700 dark:text-amber-300">
                    Template ini dilindungi agar selalu tersedia sebagai template rujukan standar. Untuk memodifikasi kode atau tata letaknya, silakan klik tombol <strong>"Duplikat Salinan"</strong> di atas.
                </p>
            </div>
        </div>
    @endif

    @if(!empty($validationErrors))
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 rounded-lg">
            <div class="flex items-center gap-2 text-red-800 dark:text-red-300 font-bold text-sm mb-2">
                <span class="material-symbols-outlined text-sm">warning</span>
                <span>Kesalahan Validasi Sintaks Ditemukan:</span>
            </div>
            <ul class="list-disc pl-5 text-xs text-red-700 dark:text-red-300 space-y-1">
                @foreach($validationErrors as $error)
                    <li>{{ is_array($error) ? ($error['message'] ?? json_encode($error)) : $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Template Metadata & Editor (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Metadata Card -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                    Informasi Template
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" :value="__('Nama Template')" />
                        <x-text-input id="name" type="text" class="mt-1 block w-full text-sm" wire:model="name" :disabled="$is_system" placeholder="Contoh: Voucher Modern A4 Biru" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="category" :value="__('Kategori Format')" />
                        <select id="category" wire:model="category" @disabled($is_system) class="mt-1 block w-full text-sm border-slate-300 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-900 dark:text-slate-100">
                            <option value="wifi">WiFi / Hotspot</option>
                            <option value="classic">Klasik</option>
                            <option value="modern">Modern</option>
                            <option value="thermal">Thermal (Struk Kasir 58/80mm)</option>
                            <option value="qr">Fokus QR Code</option>
                            <option value="ink_saver">Hemat Tinta (Monokrom)</option>
                            <option value="premium">Premium</option>
                            <option value="gaming">Gaming</option>
                            <option value="custom">Custom</option>
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="description" :value="__('Deskripsi Singkat')" />
                        <x-text-input id="description" type="text" class="mt-1 block w-full text-sm" wire:model="description" :disabled="$is_system" placeholder="Deskripsi pemakaian, ukuran kertas, atau tujuan template..." />
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    @if($template && !$is_system)
                        <div class="sm:col-span-2">
                            <x-input-label for="changelog" :value="__('Catatan Perubahan Versi Baru (Opsional)')" />
                            <x-text-input id="changelog" type="text" class="mt-1 block w-full text-sm" wire:model="changelog" placeholder="Misal: Penyesuaian ukuran font dan logo perusahaan" />
                            <p class="text-xs text-slate-400 mt-1">Mengisi catatan ini akan membuat nomor revisi versi baru pada riwayat template.</p>
                        </div>
                    @endif

                    <div class="sm:col-span-2 pt-1 flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_active" @disabled($is_system) class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-600">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300 font-medium">Aktifkan template ini dalam sistem</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Code Editor Card -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3 pb-2 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Source Code HTML & CSS Template
                        </h3>
                        <p class="text-xs text-slate-500">Mendukung tag HTML, inline CSS, serta syntax logic dsBilling / Smarty.</p>
                    </div>

                    <button wire:click="validateContent" type="button" class="inline-flex items-center text-xs px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded hover:bg-slate-200 dark:hover:bg-slate-600 font-medium">
                        <span class="material-symbols-outlined text-xs mr-1">check_circle</span> Validasi Syntax
                    </button>
                </div>

                <!-- Code Editor Area with tab support -->
                <div class="border rounded-md border-slate-300 dark:border-slate-600 overflow-hidden" 
                     x-data="{
                         insertTag(text) {
                             const el = document.getElementById('templateCodeArea');
                             if (!el) return;
                             const start = el.selectionStart;
                             const end = el.selectionEnd;
                             el.value = el.value.substring(0, start) + text + el.value.substring(end);
                             el.selectionStart = el.selectionEnd = start + text.length;
                             el.dispatchEvent(new Event('input'));
                             el.focus();
                         }
                     }">
                    
                    <!-- Quick Toolbar -->
                    <div class="bg-slate-100 dark:bg-slate-900/70 px-3 py-2 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-slate-500 font-medium mr-1">Sisip Cepat:</span>
                        <button type="button" @click="insertTag('@{{voucher.code}}')" class="px-2 py-0.5 bg-white dark:bg-slate-800 border rounded hover:bg-slate-50 text-indigo-600 dark:text-indigo-400 font-mono text-xs">&#123;&#123;voucher.code&#125;&#125;</button>
                        <button type="button" @click="insertTag('@{{voucher.password}}')" class="px-2 py-0.5 bg-white dark:bg-slate-800 border rounded hover:bg-slate-50 text-indigo-600 dark:text-indigo-400 font-mono text-xs">&#123;&#123;voucher.password&#125;&#125;</button>
                        <button type="button" @click="insertTag('@{{voucher.price}}')" class="px-2 py-0.5 bg-white dark:bg-slate-800 border rounded hover:bg-slate-50 text-indigo-600 dark:text-indigo-400 font-mono text-xs">&#123;&#123;voucher.price&#125;&#125;</button>
                        <button type="button" @click="insertTag('{qr(voucher.code)}')" class="px-2 py-0.5 bg-white dark:bg-slate-800 border rounded hover:bg-slate-50 text-purple-600 dark:text-purple-400 font-mono text-xs">&#123;qr(...)&#125;</button>
                        <button type="button" @click="insertTag('{if $price eq \'2000\'}\n\n{/if}')" class="px-2 py-0.5 bg-white dark:bg-slate-800 border rounded hover:bg-slate-50 text-slate-700 dark:text-slate-300 font-mono text-xs">&#123;if&#125;</button>
                    </div>

                    <textarea id="templateCodeArea"
                              wire:model.defer="template_code"
                              @disabled($is_system)
                              @keydown.tab.prevent="
                                  const start = $el.selectionStart;
                                  const end = $el.selectionEnd;
                                  $el.value = $el.value.substring(0, start) + '    ' + $el.value.substring(end);
                                  $el.selectionStart = $el.selectionEnd = start + 4;
                                  $el.dispatchEvent(new Event('input'));
                              "
                              class="w-full h-[520px] p-4 font-mono text-xs leading-relaxed bg-slate-900 text-slate-100 border-0 focus:ring-0 focus:outline-none resize-y"
                              spellcheck="false"
                              placeholder="Masukkan kode HTML template voucher di sini..."></textarea>
                </div>
                <x-input-error :messages="$errors->get('template_code')" class="mt-2" />
            </div>
        </div>

        <!-- Right: Variables Cheatsheet & Documentation (1 Col) -->
        <div class="space-y-6">
            <!-- Cheatsheet Card -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700"
                 x-data="{ filter: '' }">
                <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm text-indigo-600">code</span>
                        Kamus Variabel
                    </h3>
                    <span class="text-xs text-slate-400">Klik untuk salin</span>
                </div>

                <div class="mb-3">
                    <input type="text" 
                           x-model="filter" 
                           placeholder="Cari variabel (misal: code, price)..." 
                           class="w-full text-xs rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-1.5 px-2.5">
                </div>

                <div class="space-y-4 max-h-[580px] overflow-y-auto pr-1">
                    @foreach($variables as $categoryName => $vars)
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                                {{ ucfirst($categoryName) }}
                            </h4>
                            <div class="space-y-1.5">
                                @foreach($vars as $v)
                                    <div x-show="filter === '' || '{{ strtolower($v['key']) }}'.includes(filter.toLowerCase()) || '{{ strtolower($v['description']) }}'.includes(filter.toLowerCase())"
                                         @click="
                                             navigator.clipboard.writeText(String.fromCharCode(123, 123) + '{{ $v['key'] }}' + String.fromCharCode(125, 125));
                                             $dispatch('notify', {type: 'success', message: 'Tersalin: {{ $v['key'] }}'});
                                         "
                                         class="p-2 bg-slate-50 dark:bg-slate-900/60 rounded border border-slate-200 dark:border-slate-700 hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-slate-800 cursor-pointer transition-all">
                                        <div class="font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-400">
                                            &#123;&#123; {{ $v['key'] }} &#125;&#125;
                                        </div>
                                        @if(!empty($v['description']))
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ $v['description'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <!-- Helpers & Functions -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Helper & QR Generator
                        </h4>
                        <div class="space-y-1.5 text-xs">
                            <div class="p-2 bg-slate-50 dark:bg-slate-900/60 rounded border border-slate-200 dark:border-slate-700">
                                <div class="font-mono text-xs font-semibold text-purple-700 dark:text-purple-400">
                                    &lt;img src="{qr(voucher.code, 120)}" /&gt;
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Membuat QR Code SVG langsung dari kode voucher</div>
                            </div>

                            <div class="p-2 bg-slate-50 dark:bg-slate-900/60 rounded border border-slate-200 dark:border-slate-700">
                                <div class="font-mono text-xs font-semibold text-purple-700 dark:text-purple-400">
                                    &#123;&#123; format_currency(voucher.price) &#125;&#125;
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Format mata uang (Rp 5.000)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="bg-indigo-50/70 dark:bg-indigo-950/20 p-4 rounded-lg border border-indigo-100 dark:border-indigo-900 text-xs text-indigo-900 dark:text-indigo-200 space-y-2">
                <p class="font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">lightbulb</span> Tips Template
                </p>
                <p class="text-[11px] leading-relaxed">
                    Gunakan tombol <strong>Preview</strong> untuk menguji rendering kartu secara visual sebelum dicetak. Sistem mendukung uji simulasi harga dan cetak multi-voucher sekaligus.
                </p>
            </div>
        </div>
    </div>
</div>
