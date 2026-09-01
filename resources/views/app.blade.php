<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Refus d'indexation par défaut : la plupart des pages portent des données
         personnelles — un CV, un éditeur, une liste de CV. Seules celles qui
         l'annoncent explicitement sont référençables : la page d'accueil, et un
         CV dont l'auteur a coché la case. --}}
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

    {{-- Aperçu de partage. Les valeurs viennent de App\Support\SocialCard ;
         celles par défaut couvrent les pages qui n'en fournissent pas. --}}
    @php
        $ogTitle ??= config('app.name', 'Civi');
        $ogDescription ??= 'Faites votre CV, pas votre mise en page. Gratuit, sans compte.';
        $ogImage = \App\Support\SocialCard::imageUrl();
    @endphp

    <meta name="description" content="{{ $ogDescription }}">

    <meta property="og:site_name" content="Civi">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Civi — le générateur de CV">

    @if ($appId = config('cv.facebook_app_id'))
        <meta property="fb:app_id" content="{{ $appId }}">
    @endif

    {{-- `summary_large_image` affiche la vignette en pleine largeur ;
         `summary` la réduirait à une petite miniature carrée. --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead

    {{-- Open Analytics. La clé est publique par conception : c'est un
         identifiant d'ingestion en écriture seule. --}}
    <script
        async
        src="https://c.getopen.so/oa.js"
        data-key="oa_pk_3VWvbDhP5A26F4hGMjsvyWoILlN_SMN5"
        data-collector="https://c.getopen.so"
    ></script>
</head>
<body class="h-full bg-default text-default antialiased">
@inertia
</body>
</html>
