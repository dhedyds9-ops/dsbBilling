<?php

namespace App\Volt\Components\Admin;

use Livewire\Volt\Component;

new class extends Component {
    public bool $darkMode = false;

    public function mount()
    {
        $this->darkMode = $this->isDarkMode();

        $this->listeners = [
            'toggle-dark-mode' => 'toggle',
        ];
    }

    public function toggle()
    {
        $this->darkMode = !$this->darkMode;
        $this->dispatch('dark-mode-toggled', $this->darkMode);
    }

    public function isDarkMode(): bool
    {
        if (session()->has('dark_mode')) {
            return session('dark_mode') === true;
        }

        return request()->cookie('dark_mode', false) ||
               (request()->hasHeader('sec-prefers-color-scheme') &&
                request()->header('sec-prefers-color-scheme') === 'dark');
    }
};

?>

<div x-data="{ darkMode: @entangle('darkMode') }">
    <button
        @click="darkMode = !darkMode; $dispatch('dark-mode-toggled', darkMode)"
        class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        {{-- Sun Icon (shown in dark mode) --}}
        <svg
            x-show="darkMode"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 rotate-90 scale-75"
            x-transition:enter-end="opacity-100 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 -rotate-90 scale-75"
            class="w-5 h-5 text-amber-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>

        {{-- Moon Icon (shown in light mode) --}}
        <svg
            x-show="!darkMode"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 rotate-90 scale-75"
            x-transition:enter-end="opacity-100 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 -rotate-90 scale-75"
            class="w-5 h-5 text-slate-600 dark:text-slate-300"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
</div>
