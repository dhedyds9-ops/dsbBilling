<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] relative pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold mb-1 text-slate-800 dark:text-slate-200">Ganti Paket</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Ajukan permintaan ganti paket langganan Anda.</p>
    </div>

    {{-- Current Plans --}}
    @if($currentServices->isNotEmpty())
    <div class="mb-6 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 mb-2 uppercase tracking-wider">Paket Aktif Saat Ini</p>
        @foreach($currentServices as $cs)
        <div class="flex items-center justify-between">
            <div>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $cs->serviceProfile?->name ?? '-' }}</span>
                <x-ui.badge variant="info">
                    {{ $cs->pppoeUser ?'PPPoE' : 'Hotspot' }}
                </x-ui.badge>
            </div>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Rp {{ number_format($cs->serviceProfile?->base_price ?? 0, 0, ',', '.') }}/bln</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Alert --}}
    @if($message)
    <div class="mb-5 p-4 rounded-xl flex items-start gap-3 {{ $messageType === 'success' ?'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400' }}">
        <span class="material-symbols-outlined shrink-0">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
        <div class="text-sm">{{ $message }}</div>
    </div>
    @endif

    @if($availablePlans->isEmpty())
        <div class="text-center text-slate-500 dark:text-slate-400 py-10">Tidak ada paket yang tersedia saat ini.</div>
    @else
    <form wire:submit="submitRequest" class="space-y-5">
        {{-- Plan Selection --}}
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Pilih Paket Baru</label>
            <div class="grid grid-cols-1 gap-3">
                @foreach($availablePlans as $plan)
                @php
                    $currentPlanId = $currentServices->first()?->service_profile_id;
                    $isCurrent = $currentPlanId == $plan->id;
                @endphp
                <label class="relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all
                    {{ $selectedPlanId == $plan->id ?'border-teal-500 bg-teal-50 dark:bg-teal-900/20 dark:border-teal-600' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:border-slate-600 dark:hover:border-slate-600 bg-white dark:bg-slate-800' }}
                    {{ $isCurrent ?'opacity-50 cursor-not-allowed' : '' }}">
                    <input type="radio" wire:model="selectedPlanId" value="{{ $plan->id }}"
                        class="mt-1 text-teal-500" {{ $isCurrent ?'disabled' : '' }}>
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-800 dark:text-slate-200 line-clamp-1 pr-2">{{ $plan->name }}</span>
                            <div class="text-right shrink-0">
                                <span class="font-bold text-teal-600 dark:text-teal-400">Rp {{ number_format($plan->base_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @if($plan->description)
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">{{ $plan->description }}</p>
                        @endif
                        @if($isCurrent)
                        <div class="mt-1"><x-ui.badge variant="success">Paket Saat Ini</x-ui.badge></div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
            @error('selectedPlanId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Reason --}}
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alasan (Opsional)</label>
            <textarea wire:model="reason" rows="3" placeholder="Contoh: Kecepatan saat ini kurang..."
                class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition resize-none dark:bg-slate-900 dark:text-slate-100"></textarea>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full py-3.5 bg-teal-600 text-white font-bold rounded-xl hover:bg-teal-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50">
            <span wire:loading.remove>Kirim Permintaan</span>
            <span wire:loading>Mengirim...</span>
        </button>
    </form>
    @endif
</div>







