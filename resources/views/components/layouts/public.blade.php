<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-900">
<header class="border-b border-zinc-200">

<div class="mx-auto max-w-6xl px-6 py-5 flex items-center justify-between">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="font-semibold tracking-tight">
            {{ config('app.name', 'Artist') }}
        </a>
        <!-- TODO ВЫБОР ЯЗЫКА -->
        @php
            $locale = app()->getLocale();
            $current = request()->route()?->getName(); // например 'gallery'
            $params = request()->route()?->parameters() ?? []; // текущие параметры
        @endphp

        <div class="flex items-center gap-2 text-sm text-zinc-600">
            @foreach (['en' => 'EN', 'ru' => 'RU', 'ua' => 'UA'] as $loc => $label)
                <a
                    href="{{ $current ? route($current, array_merge($params, ['locale' => $loc])) : url('/' . $loc) }}"
                    class="rounded-md px-2 py-1 hover:bg-zinc-100 {{ $loc === $locale ? 'bg-zinc-100 text-zinc-900' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <nav class="flex items-center gap-6 text-sm">
        @php($locale = app()->getLocale())
        <a href="{{ route('home', ['locale' => $locale]) }}">{{ __('ui.home') }}</a>
        <a href="{{ route('gallery', ['locale' => $locale]) }}">{{ __('ui.gallery') }}</a>
        <a href="{{ route('about', ['locale' => $locale]) }}">{{ __('ui.about') }}</a>
        <a href="{{ route('contacts', ['locale' => $locale]) }}">{{ __('ui.contacts') }}</a>
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
