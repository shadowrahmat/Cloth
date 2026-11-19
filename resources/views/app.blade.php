@php($site = \App\Support\SiteConfig::get())
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $site['seo']['title'] ?: $site['branding']['site_name'] }}</title>
    <meta name="description" content="{{ $site['seo']['description'] }}">
    <meta property="og:site_name" content="{{ $site['branding']['site_name'] }}">
    <meta property="og:title" content="{{ $site['seo']['title'] ?: $site['branding']['site_name'] }}">
    <meta property="og:description" content="{{ $site['seo']['description'] }}">
    <meta name="theme-color" content="#F7F5F0">
    <link rel="icon" href="{{ $site['branding']['favicon'] ?: '/images/logo-transparent.png' }}">

    <!-- Fonts: Playfair Display + Inter for Latin, Noto Serif Bengali + Hind Siliguri for Bangla -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Storefront content managed from Admin → সাইট কাস্টমাইজ (no flash of default content) -->
    <script>window.__SITE__ = @json($site);</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F7F5F0] text-[#111111] antialiased selection:bg-[#111111] selection:text-white min-h-screen flex flex-col">
    <div id="app" class="flex-1 flex flex-col"></div>
</body>
</html>
