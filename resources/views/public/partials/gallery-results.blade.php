<div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($works as $work)
        @php
            $query = array_filter([
                'technique' => $filters['technique'] ?? null,
                'genre' => $filters['genre'] ?? null,
                'surface' => $filters['surface'] ?? null,
                'year' => $filters['year'] ?? null,
            ]);
            $categoryLabel = collect([
                $work->technique?->name($locale),
                $work->genre?->name($locale),
                $work->surface?->name($locale),
            ])->filter()->implode(' · ');
            $link = route('gallery.show', ['locale' => $locale, 'work' => $work->id]);
            if (!empty($query)) {
                $link .= '?' . http_build_query($query);
            }
            $priceLabel = !$work->price_cents || !$work->currency
                ? ($locale === 'de' ? 'Preis auf Anfrage' : ($locale === 'ua' ? 'Ціна за запитом' : 'Price on request'))
                : $work->currency . ' ' . number_format($work->price_cents / 100, 0, '.', ',');
        @endphp
        <a
            href="{{ $link }}"
            class="group block rounded-2xl text-left no-underline hover:no-underline focus:no-underline focus-visible:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white select-none"
        >
            <div class="h-[240px] sm:h-[260px] lg:h-[360px] overflow-hidden rounded-2xl bg-zinc-100 shadow-sm ring-1 ring-zinc-200 transition-shadow duration-200 group-hover:shadow-md flex items-center justify-center p-2 lg:p-3">
                <img
                    src="{{ $work->mainImageUrl() }}"
                    alt="{{ $categoryLabel }}"
                    class="h-full w-full max-h-full max-w-full object-contain select-none"
                    draggable="false"
                    loading="lazy"
                >
            </div>
            <div class="mt-3 text-sm text-zinc-500">{{ $categoryLabel !== '' ? $categoryLabel : '—' }}</div>
            <div class="mt-1 text-lg font-semibold text-zinc-900">
                {{ $work->year ?? '—' }} · {{ $work->size_label ?? '—' }}
            </div>
            <div class="mt-1 text-sm text-zinc-600">{{ $priceLabel }}</div>
        </a>
    @empty
        <div class="text-sm text-zinc-500">
            {{ $locale === 'de' ? 'Keine Werke gefunden.' : ($locale === 'ua' ? 'Робіт не знайдено.' : 'No works found.') }}
        </div>
    @endforelse
</div>

<div class="mt-10">
    {{ $works->links() }}
</div>
