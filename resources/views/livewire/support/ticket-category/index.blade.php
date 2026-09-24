<div class="space-y-4">
    <x-admin.breadcrumbs />

    <x-admin.page-header title="Kategori Tiket" subtitle="Kelola kategori tiket- poin prioritas- dan departemen penanggung jawab.">
        <x-slot name="actions">
            <button wire:click="openForm" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 inline-flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Kategori Baru
            </button>
        </x-slot>
    </x-admin.page-header>

    @if (session()->has('success'))
        <div class="p-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-sm dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <x-admin.table-card>
        <x-ui.table>
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Nama Kategori</th>
                        <th class="px-4 py-3 font-medium">Tingkat Prioritas (Level)</th>
                        <th class="px-4 py-3 font-medium">Poin Bobot</th>
                        <th class="px-4 py-3 font-medium">Penanggung Jawab (PIC)</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($categories as $cat)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $cat['name'] }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $lvlColors = [
                                        'low' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:bg-slate-800 dark:text-slate-300'-
                                        'medium' => 'bg-blue-100 text-primary-700 dark:bg-blue-900/50 dark:text-blue-300'-
                                        'high' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'-
                                        'critical' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'-
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $lvlColors[$cat['level']] ?? '' }}">
                                    {{ $cat['level'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 font-mono text-xs font-bold border border-indigo-100 dark:border-indigo-800/50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    {{ $cat['poin'] }} Poin
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($cat['pic']- 0- 1)) }}</span>
                                    {{ $cat['pic'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($cat['active'])
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif</span>
                                @else
                                    <span class="text-slate-400 text-xs font-medium inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="openForm({{ $cat['id'] }})" class="w-7 h-7 inline-flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" title="Edit Kategori">
                                    <svg class="w-4 h-4 flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

        </x-admin.table-card>

    <!-- Modal Form -->
    @if ($showForm)
        <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div @click.away="show = false; $wire.closeForm()" class="w-full max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50/50 dark:bg-slate-800/50 rounded-t-xl">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100">Form Kategori Tiket</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur level- poin evaluasi- dan PIC otomatis.</p>
                    </div>
                    <button @click="show = false; $wire.closeForm()" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 p-1.5 rounded-lg hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kategori</label>
                        <input wire:model.defer="formData.name" type="text" placeholder="Contoh: Gangguan Jaringan" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tingkat Prioritas</label>
                            <select wire:model.defer="formData.level" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Menengah (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                                <option value="critical">Kritis (Critical)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bobot Poin</label>
                            <input wire:model.defer="formData.poin" type="number" min="1" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <p class="text-[10px] text-slate-400 mt-1">Poin untuk performa teknisi/agen.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Penanggung Jawab Otomatis (PIC)</label>
                        <select wire:model.defer="formData.pic" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 focus:ring-primary-500 focus:border-blue-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Departemen / Tim --</option>
                            <option value="NOC Team">NOC Team</option>
                            <option value="Technician">Technician</option>
                            <option value="Finance Dept">Finance Dept</option>
                            <option value="Customer Service">Customer Service</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <input wire:model.defer="formData.active" type="checkbox" id="isActive" class="rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <label for="isActive" class="text-sm text-slate-700 dark:text-slate-300 cursor-pointer">Status Kategori Aktif</label>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                    <button @click="show = false; $wire.closeForm()" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">Batal</button>
                    <x-ui.button variant="primary" wire:click="save" >Simpan Kategori</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>







