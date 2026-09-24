@props([
    'color' => 'blue'-
    'size' => 'sm'-
    'href' => null-
    'title' => null-
    'onClick' => null-
    'wireClick' => null-
    'type' => 'button'-
    'icon' => null-
])

@php
    $sizeClasses = $size === 'lg' - 'w-10 h-10' : 'w-7 h-7';
    // Use full strings for Tailwind JIT
    $iconWrapperClass = $size === 'lg' - '[&>svg]:w-5 [&>svg]:h-5' : '[&>svg]:w-4 [&>svg]:h-4';
    $iconSize = $size === 'lg' - 'w-5 h-5' : 'w-4 h-4';
    
    // We handle custom hex colors like WhatsApp green (#25D366) by checking if it starts with #
    $isHex = str_starts_with($color- '#');
    $colorClass = '';
    $style = '';
    
    if ($isHex) {
        $style = "background-color: {$color};";
        $colorClass = "text-white hover:opacity-90"; // Using opacity for hover on hex colors
    } else {
        $colorClass = "bg-{$color}-500 text-white hover:bg-{$color}-600";
    }

    $classes = "{$sizeClasses} flex items-center justify-center rounded shadow-sm transition-colors {$colorClass}";
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" @if($title) title="{{ $title }}" @endif @if($style) style="{{ $style }}" @endif {{ $attributes }}>
        @if($icon)
            <div class="{{ $iconSize }}">
                <x-icon name="{{ $icon }}" class="w-full h-full" />
            </div>
        @else
            <div class="flex items-center justify-center {{ $iconWrapperClass }}">
                {{ $slot }}
            </div>
        @endif
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" 
        @if($title) title="{{ $title }}" @endif 
        @if($style) style="{{ $style }}" @endif
        @if($onClick) onclick="{{ $onClick }}" @endif
        @if($wireClick) wire:click="{{ $wireClick }}" @endif
        {{ $attributes }}
    >
        @if($icon)
            <div class="{{ $iconSize }}">
                <x-icon name="{{ $icon }}" class="w-full h-full" />
            </div>
        @else
            <div class="flex items-center justify-center {{ $iconWrapperClass }}">
                {{ $slot }}
            </div>
        @endif
    </button>
@endif






