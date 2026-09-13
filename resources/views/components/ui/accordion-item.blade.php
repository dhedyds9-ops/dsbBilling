{{--
/**
 * Accordion Item Component
 */
--}}

@props([
    'value' => null,
    'title' => null,
    'disabled' => false,
    'icon' => null,
])

<div class="border-b border-slate-200 dark:border-slate-700" {{ $attributes }}>
    <button
        type="button"
        @click="$parent.selected = {{ json_encode($value) }}; $parent.multiple && ($parent.selected.includes({{ json_encode($value) }}) ? $parent.selected = $parent.selected.filter(v => v !== {{ json_encode($value) }}) : $parent.selected.push({{ json_encode($value) }})) : ($parent.selected === {{ json_encode($value) }} ? $parent.selected = null : $parent.selected = {{ json_encode($value) }})"
        class="flex items-center justify-between w-full px-4 py-4 text-left hover:bg-slate-50 dark:bg-slate-900/50 transition-colors"
        :class="{{ $disabled }} ? 'opacity-50 cursor-not-allowed' : ''"
        @if ($disabled) disabled @endif
    >
        <div class="flex items-center gap-3">
            @if ($icon)
                <x-icon :name="$icon" class="w-5 h-5 text-slate-400" />
            @endif
            <span class="font-medium text-slate-900 dark:text-slate-100">{{ $title }}</span>
        </div>

        <svg
            class="w-5 h-5 text-slate-400 transition-transform duration-200"
            :class="$parent.selected === {{ json_encode($value) }} ? 'rotate-180' : ''"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-show="$parent.selected === {{ json_encode($value) }}"
        x-collapse
        class="px-4 pb-4"
    >
        <div class="text-slate-600 dark:text-slate-400">
            {{ $slot }}
        </div>
    </div>
</div>






