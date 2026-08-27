{{--
Enterprise Confirmation Modal SSOT — auto wired to BaseEnterpriseList
--}}
<div
    x-data="{ show: false }"
    x-init="$watch('show', v => v ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))"
    x-on:open-modal.window="if ($event.detail.name === 'confirm' || $event.detail === 'confirm') show = true"
    x-on:close-modal.window="if ($event.detail.name === 'confirm' || $event.detail === 'confirm') show = false"
    x-show="show" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" role="dialog" aria-modal="true">
    <div x-show="show" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         class="w-full max-w-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $this->confirmTitle ?? 'Konfirmasi' }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">{{ $this->confirmMessage ?? 'Lanjutkan aksi?' }}</p>
            </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-end gap-2 bg-slate-50 dark:bg-slate-800/70 rounded-b-xl">
            <button @click="show = false" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700">Batal</button>
            <button wire:click="handleConfirm" @click="show = false" class="px-4 py-1.5 text-sm font-medium rounded-md text-white {{ $this->confirmBtnClass ?? 'bg-red-600 hover:bg-red-700' }}">
                {{ $this->confirmBtnText ?? 'Ya' }}
            </button>
        </div>
    </div>
</div>
