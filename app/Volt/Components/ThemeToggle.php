<?php

namespace App\Volt\Components;

use Livewire\Volt\Component;

new class extends Component {
    public bool $darkMode = false;
    public string $sidebarCollapsed = 'false';
    
    public function mount()
    {
        $this->darkMode = session('dark_mode', false);
        $this->sidebarCollapsed = session('sidebar_collapsed', 'false');
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        session(['dark_mode' => $this->darkMode]);
        
        $this->dispatch('dark-mode-toggled', $this->darkMode)->to('dark-mode-toggle');
    }

    public function toggleSidebar()
    {
        $this->sidebarCollapsed = $this->sidebarCollapsed === 'true' ? 'false' : 'true';
        session(['sidebar_collapsed' => $this->sidebarCollapsed]);
        
        $this->dispatch('sidebar-toggled', $this->sidebarCollapsed === 'true')->to('sidebar-toggle');
    }
};
?>

<div class="flex items-center gap-2">
    <!-- Dark Mode Toggle -->
    <button 
        wire:click="toggleDarkMode"
        class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        title="Toggle dark mode"
    >
        @if($darkMode)
        <!-- Sun Icon -->
        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        @else
        <!-- Moon Icon -->
        <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        @endif
    </button>

    <!-- Sidebar Toggle (Mobile) -->
    <button 
        wire:click="toggleSidebar"
        class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        title="Toggle sidebar"
    >
        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
</div>
