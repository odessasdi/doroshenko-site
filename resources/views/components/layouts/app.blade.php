<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Панель адміністратора</title>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white text-zinc-900">
    @php
        $isWorks = request()->is('admin/works*');
        $isTechniques = request()->is('admin/techniques*');
        $menuBase = 'px-3 py-2 rounded-lg text-sm';
        $menuDefault = $menuBase . ' text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100';
        $menuActive = $menuBase . ' font-medium text-zinc-900 bg-zinc-200';
    @endphp
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <div class="flex items-center gap-6">
                <div class="text-lg font-semibold tracking-tight">Панель адміністратора</div>
                <nav class="flex items-center gap-2">
                    <a href="/admin/works" class="{{ $isWorks ? $menuActive : $menuDefault }}">Роботи</a>
                    <a href="/admin/techniques" class="{{ $isTechniques ? $menuActive : $menuDefault }}">Техніки</a>
                </nav>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="text-sm text-zinc-700 hover:text-zinc-900">Вийти</button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
