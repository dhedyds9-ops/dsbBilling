{{--
/**
 * Avatar Group Component
 *
 * Stack multiple avatars with overlap
 */
--}}

@props([
    'avatars' => [],
    'max' => 4,
    'size' => 'md',
])

@php
$displayed = array_slice($avatars, 0, $max);
$remaining = count($avatars) - $max;
@endphp

<div class="flex items-center -space-x-3" {{ $attributes }}>
    @foreach ($displayed as $avatar)
        <x-data-display.avatar
            :src="$avatar['src'] ?? null"
            :name="$avatar['name'] ?? null"
            :size="$size"
            class="ring-2 ring-white"
        />
    @endforeach

    @if ($remaining > 0)
        <div class="relative inline-block">
            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-medium text-slate-600 dark:text-slate-400 ring-2 ring-white">
                +{{ $remaining }}
            </div>
        </div>
    @endif
</div>







