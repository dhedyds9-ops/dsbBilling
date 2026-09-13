<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'position' => 'top-right'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'position' => 'top-right'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div
    x-data="toastManager({ position: '<?php echo e($position); ?>' })"
    class="fixed z-[100] flex flex-col gap-3 pointer-events-none w-full max-w-sm px-4 sm:px-0 transition-all duration-300"
    :class="{
        'top-4 right-4 items-end': position === 'top-right',
        'top-4 left-4 items-start': position === 'top-left',
        'top-4 left-1/2 -translate-x-1/2 items-center': position === 'top-center',
        'bottom-4 right-4 items-end flex-col-reverse': position === 'bottom-right',
        'bottom-4 left-4 items-start flex-col-reverse': position === 'bottom-left',
        'bottom-4 left-1/2 -translate-x-1/2 items-center flex-col-reverse': position === 'bottom-center',
    }"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @mouseenter="pauseToast(toast.id)"
            @mouseleave="resumeToast(toast.id)"
            class="relative flex items-start gap-3 w-full p-4 rounded-xl shadow-xl pointer-events-auto overflow-hidden bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/60 dark:border-slate-700/60"
        >
            <div class="flex-shrink-0">
                <template x-if="toast.type === 'success'">
                    <div class="p-1 rounded-full bg-emerald-100/50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'error' || toast.type === 'danger'">
                    <div class="p-1 rounded-full bg-red-100/50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="p-1 rounded-full bg-amber-100/50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </template>
                <template x-if="toast.type === 'info'">
                    <div class="p-1 rounded-full bg-blue-100/50 dark:bg-blue-900/30 text-primary-600 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </template>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <template x-if="toast.title">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="toast.title"></p>
                </template>
                <p class="text-sm text-slate-600 dark:text-slate-300" :class="toast.title ? 'mt-0.5' : ''" x-html="toast.message"></p>
            </div>
            <button type="button" @click="removeToast(toast.id)" class="flex-shrink-0 p-1 rounded-md text-slate-400 hover:text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-300 transition-colors focus:outline-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="absolute bottom-0 left-0 h-1 bg-slate-100 dark:bg-slate-800 w-full overflow-hidden">
                <div class="h-full rounded-r-full transition-all duration-100 ease-linear"
                     :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-red-500': toast.type === 'error' || toast.type === 'danger',
                        'bg-amber-500': toast.type === 'warning',
                        'bg-blue-500': toast.type === 'info'
                     }"
                     :style="'width: ' + toast.progress + '%'"
                ></div>
            </div>
        </div>
    </template>
</div>

<?php if (! $__env->hasRenderedOnce('950f0dbb-ce57-4f1b-bb27-d8ae97c890e2')): $__env->markAsRenderedOnce('950f0dbb-ce57-4f1b-bb27-d8ae97c890e2');
$__env->startPush('scripts'); ?>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toastManager', (config) => ({
            toasts: [],
            position: config.position || 'top-right',
            init() {
                if (window.serverFlashes) {
                    window.serverFlashes.forEach(flash => this.addToast(flash));
                    window.serverFlashes = [];
                }
                Livewire.on('toast', (event) => {
                    let data = Array.isArray(event) ? event[0] : event;
                    this.addToast(data);
                });
            },
            addToast(toast) {
                const id = Date.now() + Math.random().toString(36).substr(2, 9);
                const duration = toast.duration || 4000;
                const newToast = {
                    id, type: toast.type || 'info', title: toast.title || null, message: toast.message || '',
                    duration, timeLeft: duration, progress: 100, visible: true, timer: null, lastTick: Date.now()
                };
                if (this.position.includes('bottom')) { this.toasts.push(newToast); } else { this.toasts.unshift(newToast); }
                this.startTimer(id);
            },
            removeToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].visible = false;
                    clearInterval(this.toasts[index].timer);
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
                }
            },
            startTimer(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index === -1) return;
                let toast = this.toasts[index];
                toast.lastTick = Date.now();
                toast.timer = setInterval(() => {
                    const now = Date.now();
                    const delta = now - toast.lastTick;
                    toast.lastTick = now;
                    toast.timeLeft -= delta;
                    toast.progress = Math.max(0, (toast.timeLeft / toast.duration) * 100);
                    if (toast.timeLeft <= 0) {
                        clearInterval(toast.timer);
                        this.removeToast(id);
                    }
                }, 16);
            },
            pauseToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) { clearInterval(this.toasts[index].timer); }
            },
            resumeToast(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].lastTick = Date.now();
                    this.startTimer(id);
                }
            }
        }));
    });
</script>
<?php $__env->stopPush(); endif; ?>






<?php /**PATH D:\dsBilling\resources\views/components/ui/toast-manager.blade.php ENDPATH**/ ?>