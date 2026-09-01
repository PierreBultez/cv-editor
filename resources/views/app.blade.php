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

    <title inertia>{{ config('app.name', 'Civi') }}</title>

    {{-- Le favicon historique de Laravel reste servi pour les agents qui
         demandent /favicon.ico sans lire le document. --}}
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#6c3cff">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="h-full bg-default text-default antialiased">
@inertia
</body>
</html>
