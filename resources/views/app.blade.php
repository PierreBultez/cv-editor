<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Les pages publiques portent des données personnelles : pas d'indexation
         par défaut, l'utilisateur doit l'activer explicitement. --}}
    @if (! ($allowIndexing ?? false))
        <meta name="robots" content="noindex, nofollow">
    @endif

    <title inertia>{{ config('app.name', 'CV Studio') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="h-full bg-default text-default antialiased">
@inertia
</body>
</html>
