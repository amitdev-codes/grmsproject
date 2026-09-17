<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/favicon.png">

    @fonts
    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])

    <meta name="description" content="{{ $seo['description'] ?? config('app.description', 'GRMS') }}">
    <meta property="og:title" content="{{ $seo['title'] ?? config('app.name', 'GRMS') }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    <meta property="og:image" content="{{ $seo['image'] ?? asset('images/og-default.png') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    <x-inertia::head>
        <title>{{ config('app.name', 'GRMS') }}</title>
    </x-inertia::head>
</head>
<body class="font-sans antialiased">
<x-inertia::app />
</body>
</html>
