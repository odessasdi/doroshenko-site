<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-zinc-900">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <div class="flex items-center gap-6">
                <div class="text-lg font-semibold tracking-tight">Admin Panel</div>
                <nav class="flex items-center gap-4 text-sm text-zinc-700">
                    <a href="/admin/techniques" class="hover:text-zinc-900">Techniques</a>
                    <a href="/admin/works" class="hover:text-zinc-900">Works</a>
                </nav>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="text-sm text-zinc-700 hover:text-zinc-900">Logout</button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        {{ $slot }}
    </main>
</body>
</html>
