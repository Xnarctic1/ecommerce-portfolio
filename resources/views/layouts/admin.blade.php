<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Admin - STORE' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @fluxAppearance
</head>

<body class="admin-layout" data-page="{{ request()->route()->getName() }}">

    {{-- ADMIN NAVIGATION --}}
    <x-admin-navigation />

    {{-- ADMIN PAGE CONTENT --}}
    {{ $slot }}

    @livewireScripts
    @fluxScripts

</body>

</html>