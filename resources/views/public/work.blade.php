<x-layouts.public :title="__('ui.gallery')">
    @php
        $locale = app()->getLocale();
        $queryBase = array_filter([
            'technique' => $filters['technique'] ?? null,
            'year' => $filters['year'] ?? null,
        ]);
        $priceText = function () use ($work, $locale) {
            if (!$work->price_cents || !$work->currency) {
                return $locale === 'de' ? 'Preis auf Anfrage' : ($locale === 'ua' ? 'Ціна за запитом' : 'Price on request');
            }
            $amount = number_format($work->price_cents / 100, 0, '.', ',');
            return $work->currency . ' ' . $amount;
        };
        $buildLink = function ($id) use ($locale, $queryBase) {
            $query = $queryBase;
            $url = route('gallery.show', ['locale' => $locale, 'work' => $id]);
            return !empty($query) ? $url . '?' . http_build_query($query) : $url;
        };
    @endphp

    <div x-data="{ activeIndex: 0, images: @js($imageUrls), activeSrc: '' }" x-init="activeSrc = images[activeIndex] || ''">
        <div class="flex items-center justify-between">
            <a href="{{ route('gallery', array_merge(['locale' => $locale], $queryBase)) }}" class="text-sm text-zinc-600 hover:text-zinc-900">
                {{ $locale === 'de' ? 'Zur Galerie' : ($locale === 'ua' ? 'До галереї' : 'Back to gallery') }}
            </a>
            <div class="flex items-center gap-2">
                @if ($prevId)
                    <a href="{{ $buildLink($prevId) }}" class="text-sm text-zinc-600 hover:text-zinc-900">←</a>
                @endif
                @if ($nextId)
                    <a href="{{ $buildLink($nextId) }}" class="text-sm text-zinc-600 hover:text-zinc-900">→</a>
                @endif
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="overflow-hidden rounded-3xl bg-zinc-100 shadow-sm ring-1 ring-zinc-200 aspect-[3/4] max-h-[55vh] sm:max-h-[68vh] lg:max-h-[72vh] flex items-center justify-center px-4 py-6">
                    <img
                        :src="activeSrc"
                        :alt="activeSrc ? '{{ $work->technique?->name($locale) ?? '' }}' : ''"
                        class="h-full w-full object-contain max-h-full max-w-full"
                    >
                </div>

                    @if (count($imageUrls) > 1)
                    <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
                        @foreach ($imageUrls as $index => $url)
                            <button
                                type="button"
                                class="h-16 w-20 shrink-0 overflow-hidden rounded-lg border border-zinc-200"
                                :class="activeIndex === {{ $index }} ? 'ring-2 ring-zinc-900' : 'opacity-70 hover:opacity-100'"
                                @click="activeIndex = {{ $index }}; activeSrc = images[activeIndex] || ''"
                            >
                                <img src="{{ $url }}" alt="" class="h-16 w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5">
                <div class="text-sm text-zinc-500">
                    {{ $work->technique?->name($locale) ?? '—' }}
                </div>
                <h1 class="mt-2 text-2xl font-semibold text-zinc-900">
                    {{ $work->year ?? '—' }} · {{ $work->size_label ?? '—' }}
                </h1>
                <div class="mt-3 text-lg text-zinc-700">{{ $priceText() }}</div>
                <p class="mt-6 text-base leading-relaxed text-zinc-700">
                    {{ $work->description($locale) }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.public>
