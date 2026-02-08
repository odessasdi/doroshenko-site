<x-layouts.public title="Home">
    <h1 class="text-4xl font-semibold tracking-tight">Artist portfolio</h1>
    <p class="mt-4 max-w-2xl text-zinc-600">
        A minimal, gallery-style website. We’ll replace this text with the real bio later.
    </p>

    <div class="mt-8">
        <a href="{{ route('gallery', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm hover:bg-zinc-50">
            View gallery →
        </a>
    </div>
</x-layouts.public>
