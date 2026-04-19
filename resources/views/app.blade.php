<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- SEO Primary Meta Tags -->
        <meta name="title" content="Fotsport - Fotos de Eventos Esportivos e Corridas">
        <meta name="description" content="A maior plataforma de fotos esportivas. Encontre suas fotos de corridas, triatlo, ciclismo e eventos esportivos pelo seu CPF ou reconhecimento facial.">
        <meta name="keywords" content="fotos de corrida, fotografia esportiva, fotsport, fotos maratona, reconhecimento facial fotos, fotos ciclismo, fotos triatlo, comprar fotos esportivas">
        <meta name="author" content="Fotsport">
        <meta name="robots" content="index, follow">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="Fotsport - Fotos de Eventos Esportivos e Corridas">
        <meta property="og:description" content="Encontre e compre suas fotos de eventos esportivos com a melhor tecnologia de reconhecimento facial.">
        <meta property="og:image" content="{{ asset('favicon.png') }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="Fotsport - Fotos de Eventos Esportivos e Corridas">
        <meta property="twitter:description" content="Encontre e compre suas fotos de eventos esportivos com a melhor tecnologia de reconhecimento facial.">
        <meta property="twitter:image" content="{{ asset('favicon.png') }}">

        <title inertia inertia-title="Fotsport">{{ config('app.name', 'Fotsport') }}</title>
        <link rel="icon" type="image/png" href="/favicon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
