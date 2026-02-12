<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-white to-zinc-50 text-zinc-900">
<div class="min-h-screen flex flex-col">
<header class="sticky top-0 z-50 border-b border-zinc-200/70 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70" x-data="{ open: false }">
    @php
        $locale = app()->getLocale();
        $locales = ['en', 'de', 'ua'];
        $segments = request()->segments();
        $query = request()->getQueryString();
        $currentHasLocale = isset($segments[0]) && in_array($segments[0], $locales, true);
        $current = request()->route()?->getName();
    @endphp

    <div class="mx-auto h-16 max-w-6xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-zinc-700">
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
                    class="px-2 py-1 rounded-md {{ $loc === $locale ? 'font-semibold text-zinc-900 underline underline-offset-8 decoration-2' : 'hover:text-zinc-900 hover:bg-zinc-100' }}"
                >
                    {{ strtoupper($loc) }}
                </a>
                @if ($index < 2)
                    <span class="text-zinc-400">|</span>
                @endif
            @endforeach
        </div>

        <nav class="hidden sm:flex items-center gap-6 text-sm">
            <a
                href="{{ route('home', ['locale' => $locale]) }}"
                class="px-2 py-1 rounded-md {{ $current === 'home' ? 'font-semibold text-zinc-900 underline underline-offset-8 decoration-2' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' }}"
            >
                {{ __('ui.home') }}
            </a>
            <a
                href="{{ route('gallery', ['locale' => $locale]) }}"
                class="px-2 py-1 rounded-md {{ $current === 'gallery' ? 'font-semibold text-zinc-900 underline underline-offset-8 decoration-2' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' }}"
            >
                {{ __('ui.gallery') }}
            </a>
            <a
                href="{{ route('contacts', ['locale' => $locale]) }}"
                class="px-2 py-1 rounded-md {{ $current === 'contacts' ? 'font-semibold text-zinc-900 underline underline-offset-8 decoration-2' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' }}"
            >
                {{ __('ui.contacts') }}
            </a>
            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.techniques') }}" class="px-2 py-1 rounded-md text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100">Admin</a>
                @endif
            @endauth
        </nav>

        <button
            type="button"
            class="sm:hidden p-2 rounded-md hover:bg-zinc-100"
            @click="open = !open"
            aria-label="Toggle menu"
        >
            ☰
        </button>
    </div>

    <div
        class="sm:hidden border-t border-zinc-100 bg-white"
        x-cloak
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
    >
        <a
            href="{{ route('home', ['locale' => $locale]) }}"
            class="block px-4 py-3 text-sm {{ $current === 'home' ? 'font-semibold text-zinc-900 bg-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}"
        >
            {{ __('ui.home') }}
        </a>
        <a
            href="{{ route('gallery', ['locale' => $locale]) }}"
            class="block px-4 py-3 text-sm {{ $current === 'gallery' ? 'font-semibold text-zinc-900 bg-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}"
        >
            {{ __('ui.gallery') }}
        </a>
        <a
            href="{{ route('contacts', ['locale' => $locale]) }}"
            class="block px-4 py-3 text-sm {{ $current === 'contacts' ? 'font-semibold text-zinc-900 bg-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}"
        >
            {{ __('ui.contacts') }}
        </a>
        @auth
            @if (auth()->user()->is_admin)
                <a
                    href="{{ route('admin.techniques') }}"
                    class="block px-4 py-3 text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900"
                >
                    Admin
                </a>
            @endif
        @endauth
    </div>
</header>

<main class="mx-auto max-w-6xl flex-1 px-6 pt-8 pb-14 sm:pt-10 lg:px-8">
    {{ $slot }}
</main>

<footer class="border-t border-zinc-100">
    <div class="mx-auto max-w-6xl px-6 py-6 text-xs text-zinc-500 flex justify-between lg:px-8">
        <div>© 2026</div>
        <div class="hidden sm:block">Portfolio</div>
    </div>
</footer>
</div>
</body>
</html>
