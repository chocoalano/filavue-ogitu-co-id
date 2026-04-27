<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="theme-color" content="#18181b" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Default SEO fallback (overridden per-page via @inertiaHead) -->
        <meta name="robots" content="index, follow" />
        <meta property="og:type" content="website" />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:site_name" content="{{ config('app.name') }}" />

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @inertia
    </body>
</html>
