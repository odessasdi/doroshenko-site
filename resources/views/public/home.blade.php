<x-layouts.public :title="__('home.title')">
    <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
            <h1 class="text-4xl font-semibold tracking-tight">{{ __('home.title') }}</h1>
            <p class="mt-2 text-lg text-zinc-600">{{ __('home.subtitle') }}</p>
            <p class="mt-4 max-w-2xl text-zinc-600">
                {{ __('home.bio') }}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a
                    href="{{ route('gallery', ['locale' => app()->getLocale()]) }}"
                    class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm hover:bg-zinc-50"
                >
                    {{ __('home.cta_gallery') }}
                </a>
            </div>
        </div>
        <div class="order-first lg:order-last">
            <div class="overflow-hidden rounded-3xl bg-zinc-100 shadow-sm ring-1 ring-zinc-200">
                <img
                    src="{{ asset('images/artist.jpg') }}"
                    alt="{{ __('home.title') }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</x-layouts.public>
