@section('header_title', 'Buat Tiket Baru')
@section('header_right')
    <a href="{{ route('customer-portal.support.ticket-list') }}" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 transition-colors">
        <span class="material-symbols-outlined text-[22px]">close</span>
    </a>
@endsection

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Laporkan Kendala</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sampaikan keluhan atau permintaan bantuan kepada tim teknis kami.</p>
    </div>

    @if($message)
        <div class="mb-5 p-4 rounded-xl flex items-start gap-3 {{ $messageType === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 border border-emerald-100' : 'bg-red-50 dark:bg-red-900/30 text-red-700 border border-red-100' }}">
            <span class="material-symbols-outlined shrink-0">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
            <div class="text-sm">{{ $message }}</div>
        </div>
    @endif

    <form wire:submit="createTicket" class="space-y-4">
        <!-- Judul -->
        <div>
            <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Topik Permasalahan</label>
            <input type="text" id="title" wire:model="title" placeholder="Contoh: Internet Mati Total, atau Ganti Password" 
                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow py-3 dark:bg-slate-900 dark:text-slate-100" required>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Kategori -->
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                <div class="relative">
                    <select id="category" wire:model="category" class="w-full appearance-none rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow py-3 pl-4 pr-10 dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </div>

            <!-- Prioritas -->
            <div>
                <label for="priority" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tingkat Urgensi</label>
                <div class="relative">
                    <select id="priority" wire:model="priority" class="w-full appearance-none rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow py-3 pl-4 pr-10 dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="">Pilih Prioritas</option>
                        @foreach($priorities as $prio)
                            <option value="{{ $prio }}">{{ ucfirst($prio) }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deskripsi -->
        <div>
            <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jelaskan Kendala Anda</label>
            <textarea id="description" wire:model="description" rows="5" placeholder="Tuliskan secara detail apa yang terjadi..." 
                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow p-3 resize-none dark:bg-slate-900 dark:text-slate-100" required></textarea>
        </div>

        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all active:scale-[0.98] shadow-sm shadow-indigo-600/20 mt-4">
            <span class="material-symbols-outlined text-[18px]">send</span>
            Kirim Tiket
            <div wire:loading wire:target="createTicket" class="ml-2 w-4 h-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></div>
        </button>
    </form>
</div>
