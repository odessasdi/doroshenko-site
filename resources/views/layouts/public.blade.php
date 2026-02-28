<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="min-h-screen bg-white text-zinc-900">
<header class="border-b border-zinc-200">
    <div class="mx-auto max-w-6xl px-6 py-5 flex items-center justify-between">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="font-semibold tracking-tight">
            {{ config('app.name', 'Artist') }}
        </a>

        <nav class="flex items-center gap-6 text-sm">
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="hover:underline underline-offset-4">Home</a>
            <a href="{{ route('gallery', ['locale' => app()->getLocale()]) }}" class="hover:underline underline-offset-4">Gallery</a>
            <a href="{{ route('contacts', ['locale' => app()->getLocale()]) }}" class="hover:underline underline-offset-4">Contacts</a>
        </nav>
    </div>
</header>

<main class="mx-auto max-w-6xl px-6 py-10">
    {{ $slot }}
</main>

<footer class="border-t border-zinc-200">
    <div class="mx-auto max-w-6xl px-6 py-6 text-sm text-zinc-500 flex justify-between">
        <div>© {{ date('Y') }}</div>
        <div class="hidden sm:block">Portfolio</div>
    </div>
</footer>
</body>
</html>
