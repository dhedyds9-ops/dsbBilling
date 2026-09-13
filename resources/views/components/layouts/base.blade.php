@props([
    'title' => null,
    'htmlClass' => '',
    'htmlAttributes' => '',
    'bodyClass' => '',
    'bodyAttributes' => '',
    'head' => null,
    'scripts' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $htmlClass }}" {!! $htmlAttributes !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $title ?? config('app.name', 'dsBilling'))</title>

    {{-- Favicon --}}
    <link rel="icon" href="/favicon.png?v=1" type="image/png">

    {{-- Preconnect --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.bunny.net">

    {{-- Portal specific head injection --}}
    {{ $head ?? '' }}
    @stack('head')

    {{-- Global Styles & Scripts --}}
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="{{ $bodyClass }}" {!! $bodyAttributes !!}>
    
    {{ $slot }}

    {{-- Global Scripts --}}
    @livewireScripts
    
    {{-- Portal specific scripts injection --}}
    {{ $scripts ?? '' }}
    
    @stack('scripts')
</body>
</html>






