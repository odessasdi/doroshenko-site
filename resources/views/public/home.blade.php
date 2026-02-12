<x-layouts.public :title="__('home.title')">
    <div class="relative">
        <div class="pointer-events-none absolute -top-24 right-0 h-72 w-72 rounded-full bg-zinc-200/60 blur-3xl"></div>
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 sm:text-5xl">{{ __('home.title') }}</h1>
                <p class="mt-3 text-base text-zinc-600">{{ __('home.subtitle') }}</p>
                <p class="mt-6 text-base leading-relaxed text-zinc-700">
                    {{ __('home.bio') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a
                        href="{{ route('gallery', ['locale' => app()->getLocale()]) }}"
                        class="inline-flex items-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-medium text-white shadow hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900/20"
                    >
                        {{ __('home.cta_gallery') }} →
                    </a>
                </div>
            </div>
            <div class="order-first lg:order-last lg:col-span-7">
                <div class="overflow-hidden rounded-3xl bg-zinc-100 shadow-2xl ring-1 ring-zinc-200">
                    <img
                        src="{{ asset('images/artist.jpg') }}"
                        alt="{{ __('home.title') }}"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
