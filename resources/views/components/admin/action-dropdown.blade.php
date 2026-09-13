{{--
/**
 * Action Dropdown Component
 * Standard dropdown for table row actions
 */
--}}
<x-base.dropdown>
    <x-slot:trigger>
        <button class="p-1 text-slate-400 hover:text-slate-600 dark:text-slate-400 rounded-lg hover:bg-slate-100 dark:bg-slate-800 transition-colors">
            <x-icon name="more-horizontal" class="w-5 h-5" />
        </button>
    </x-slot:trigger>
    
    <div class="py-1">
        {{ $slot }}
    </div>
</x-base.dropdown>






