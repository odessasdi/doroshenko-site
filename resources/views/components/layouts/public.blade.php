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
        @php
            $locale = app()->getLocale();
            $locales = ['en', 'de', 'ua'];
            $segments = request()->segments();
            $query = request()->getQueryString();
            $currentHasLocale = isset($segments[0]) && in_array($segments[0], $locales, true);
        @endphp

        <div class="flex items-center gap-2 text-sm">
            @foreach ($locales as $index => $loc)
                @php
                    $newSegments = $segments;
                    if ($currentHasLocale) {
                        $newSegments[0] = $loc;
                    } else {
                        array_unshift($newSegments, $loc);
                    }
                    $path = '/' . implode('/', $newSegments);
                    $href = $query ? $path . '?' . $query : $path;
                @endphp
                <a
                    href="{{ $href }}"
                    class="{{ $loc === $locale ? 'font-semibold underline text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' }}"
                >
                    {{ strtoupper($loc) }}
                </a>
                @if ($index < 2)
                    <span class="text-zinc-400">|</span>
                @endif
            @endforeach
        </div>

        <nav class="flex items-center gap-6 text-sm">
        @php
            $locale = app()->getLocale();
            $current = request()->route()?->getName();
        @endphp
        <a
            href="{{ route('home', ['locale' => $locale]) }}"
            class="{{ $current === 'home' ? 'font-semibold text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-600 hover:text-zinc-900' }}"
        >
            {{ __('ui.home') }}
        </a>
        <a
            href="{{ route('gallery', ['locale' => $locale]) }}"
            class="{{ $current === 'gallery' ? 'font-semibold text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-600 hover:text-zinc-900' }}"
        >
            {{ __('ui.gallery') }}
        </a>
        <a
            href="{{ route('contacts', ['locale' => $locale]) }}"
            class="{{ $current === 'contacts' ? 'font-semibold text-zinc-900 border-b-2 border-zinc-900' : 'text-zinc-600 hover:text-zinc-900' }}"
        >
            {{ __('ui.contacts') }}
        </a>
        @auth
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.techniques') }}">Admin</a>
            @endif
        @endauth
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
