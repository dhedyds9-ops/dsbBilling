<div class="space-y-6">
    <x-admin.page-header title="WhatsApp Message History" subtitle="Riwayat pesan WhatsApp yang dikirim melalui sistem">
        <x-slot name="actions">
            <x-base.button href="{{ route('pengaturan.whatsapp') }}" variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Kembali
            </x-base.button>
        </x-slot>
    </x-admin.page-header>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/30 text-red-700 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <x-base.card>
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pesan atau nomor..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 bg-white dark:bg-slate-800 w-64 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <select wire:model.live="statusFilter" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-90 dark:bg-slate-900 dark:text-slate-1000">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>
                @if(count($selected) > 0)
                    <button wire:click="resendSelected" class="px-3 py-2 text-sm bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Kirim Ulang Terpilih ({{ count($selected) }})
                    </button>
                @endif
            </div>
            <div>
                <button wire:click="openSendModal" class="px-4 py-2 bg-[#25D366] text-white text-sm font-medium rounded-lg hover:bg-[#128C7E] transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Kirim Pesan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600 text-blue-500 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="p-3 font-semibold">Waktu</th>
                        <th class="p-3 font-semibold">Penerima</th>
                        <th class="p-3 font-semibold">Pesan</th>
                        <th class="p-3 font-semibold">Kategori</th>
                        <th class="p-3 font-semibold text-center">Status</th>
                        <th class="p-3 font-semibold">Agen</th>
                        <th class="p-3 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($messages as $msg)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
                            <td class="p-3  text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $msg->id }}" class="rounded border-slate-300 dark:border-slate-600 text-blue-500 focus:ring-primary-500">
                            </td>
                            <td class="p-3  text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $msg->created_at->format('d M Y H:i') }}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $msg->recipient_number }}</div>
                                @if($msg->recipient_name)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $msg->recipient_name }}</div>
                                @endif
                            </td>
                            <td class="p-3  text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $msg->message }}">
                                {{ Str::limit($msg->message- 50) }}
                            </td>
                            <td class="p-3  text-slate-500 dark:text-slate-400 capitalize">{{ $msg->category ?? '-' }}</td>
                            <td class="p-3  text-center">
                                @if($msg->status === 'sent')
                                    <x-ui.badge variant="success">Sent</x-ui.badge>
                                @elseif($msg->status === 'failed')
                                    <x-ui.badge variant="danger">Failed</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3  text-slate-500 dark:text-slate-400 text-xs">
                                @if($msg->assignee)
                                    <span class="inline-flex items-center gap-1">
                                        <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-primary-600 flex items-center justify-center font-bold">
                                            {{ substr($msg->assignee->name- 0- 1) }}
                                        </div>
                                        {{ $msg->assignee->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openDetail({{ $msg->id }})" class="p-1.5 text-primary-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded" title="Lihat Detail / JSON">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="openAssign({{ $msg->id }})" class="p-1.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 rounded" title="Assign Agent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    </button>
                                    @if($msg->status === 'failed')
                                        <button wire:click="resend({{ $msg->id }})" class="p-1.5 text-orange-500 hover:bg-orange-50 rounded" title="Resend">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td colspan="8" class="p-3  text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Belum Ada Riwayat Pesan</p>
                                    <p class="mt-1">Pesan yang dikirim melalui sistem akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $messages->links() }}
        </div>
    </x-base.card>

    {{-- Modal Send --}}
    @if($showSendModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Kirim Pesan Manual</h3>
                    <button wire:click="$set('showSendModal'- false)" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nomor Tujuan *</label>
                        <input type="text" wire:model="newPhone" placeholder="628xxx" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                        @error('newPhone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Isi Pesan *</label>
                        <textarea wire:model="newMessage" rows="4" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 text-sm dark:bg-slate-900 dark:text-slate-100"></textarea>
                        @error('newMessage') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                    <button wire:click="$set('showSendModal'- false)" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-700 rounded-lg transition-colors">Batal</button>
                    <button wire:click="sendManualMessage" class="px-4 py-2 text-sm font-medium bg-[#25D366] text-white rounded-lg hover:bg-[#128C7E] transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Assign --}}
    @if($showAssignModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Assign Agen</h3>
                    <button wire:click="$set('showAssignModal'- false)" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Agen</label>
                    <select wire:model="assigneeId" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Tidak Ada (Unassigned) --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role ?? 'User' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                    <button wire:click="$set('showAssignModal'- false)" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-700 rounded-lg transition-colors">Batal</button>
                    <button wire:click="saveAssign" class="px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Detail --}}
    @if($showDetailModal && $selectedMessage)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Detail Pesan</h3>
                    <button wire:click="closeDetail" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto space-y-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Penerima</span>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $selectedMessage->recipient_number }}</div>
                            @if($selectedMessage->recipient_name)
                                <div class="text-slate-600 dark:text-slate-400">{{ $selectedMessage->recipient_name }}</div>
                            @endif
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Status</span>
                            @if($selectedMessage->status === 'sent')
                                <x-ui.badge variant="success">Sent</x-ui.badge>
                            @elseif($selectedMessage->status === 'failed')
                                <x-ui.badge variant="danger">Failed</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Pending</x-ui.badge>
                            @endif
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Kategori</span>
                            <div class="font-medium text-slate-900 dark:text-slate-100 capitalize">{{ $selectedMessage->category ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Waktu</span>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $selectedMessage->created_at->format('d M Y H:i:s') }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Assigned To</span>
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $selectedMessage->assignee->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div>
                        <span class="block text-slate-500 dark:text-slate-400 mb-2 text-sm font-medium">Isi Pesan</span>
                        <div class="bg-slate-100 dark:bg-slate-700 p-4 rounded-lg text-sm text-slate-800 dark:text-slate-200 whitespace-pre-wrap font-mono">{{ $selectedMessage->message }}</div>
                    </div>

                    @if($selectedMessage->payload || $selectedMessage->error_message)
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <span class="block text-slate-500 dark:text-slate-400 mb-2 text-sm font-medium">API Payload / JSON</span>
                            @if($selectedMessage->error_message)
                                <div class="mb-2 p-3 bg-red-50 dark:bg-red-900/30 text-red-700 rounded-lg text-xs font-mono border border-red-200">
                                    {{ $selectedMessage->error_message }}
                                </div>
                            @endif
                            @if($selectedMessage->payload)
                                <pre class="bg-slate-900 text-emerald-400 p-4 rounded-lg text-xs overflow-x-auto"><code>{{ json_encode($selectedMessage->payload- JSON_PRETTY_PRINT) }}</code></pre>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>






