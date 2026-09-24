<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public array $toasts = [];
    public int $toastId = 0;

    public function mount()
    {
        $this->listeners = [
            'toast' => 'showToast',
            'toast:success' => 'showSuccessToast',
            'toast:error' => 'showErrorToast',
            'toast:warning' => 'showWarningToast',
            'toast:info' => 'showInfoToast',
        ];
    }

    public function showToast(array $params)
    {
        $this->toastId++;
        $toast = array_merge([
            'id' => $this->toastId,
            'type' => 'info',
            'title' => null,
            'message' => '',
            'duration' => 5000,
        ], $params);

        $this->toasts[] = $toast;

        if ($toast['duration'] > 0) {
            $this->dispatch("remove-toast-{$toast['id']}")->self();
        }
    }

    public function showSuccessToast(string $message, ?string $title = null)
    {
        $this->showToast([
            'type' => 'success',
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function showErrorToast(string $message, ?string $title = null)
    {
        $this->showToast([
            'type' => 'danger',
            'title' => $title ?? 'Error',
            'message' => $message,
            'duration' => 8000,
        ]);
    }

    public function showWarningToast(string $message, ?string $title = null)
    {
        $this->showToast([
            'type' => 'warning',
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function showInfoToast(string $message, ?string $title = null)
    {
        $this->showToast([
            'type' => 'info',
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function removeToast(int $id)
    {
        $this->toasts = array_filter($this->toasts, fn($t) => $t['id'] !== $id);
    }
};

?>

<div class="fixed top-4 right-4 z-[100] space-y-3 max-w-sm w-full" x-data>
    @foreach($toasts as $toast)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => { show = false; $wire.removeToast({{ $toast['id'] }}) }, {{ $toast['duration'] }})"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 p-4 rounded-xl border shadow-soft-lg w-full
                @if($toast['type'] === 'success') bg-success-50 border-success-200 dark:bg-success-900/30 dark:border-success-800 @endif
                @if($toast['type'] === 'danger') bg-danger-50 border-danger-200 dark:bg-danger-900/30 dark:border-danger-800 @endif
                @if($toast['type'] === 'warning') bg-warning-50 border-warning-200 dark:bg-warning-900/30 dark:border-warning-800 @endif
                @if($toast['type'] === 'info') bg-info-50 border-info-200 dark:bg-info-900/30 dark:border-info-800 @endif
            "
        >
            <div class="flex-shrink-0">
                @if($toast['type'] === 'success')
                    <svg class="w-5 h-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($toast['type'] === 'danger')
                    <svg class="w-5 h-5 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($toast['type'] === 'warning')
                    <svg class="w-5 h-5 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                @if($toast['title'])
                    <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $toast['title'] }}</p>
                @endif
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $toast['message'] }}</p>
            </div>

            <button
                type="button"
                @click="show = false; $wire.removeToast({{ $toast['id'] }})"
                class="flex-shrink-0 p-1 rounded hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
            >
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endforeach
</div>
