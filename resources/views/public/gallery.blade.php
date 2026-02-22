<x-layouts.public :title="__('ui.gallery')">
    @php
        $locale = app()->getLocale();
        $priceLabel = function ($work) use ($locale) {
            if (!$work->price_cents || !$work->currency) {
                return $locale === 'de' ? 'Preis auf Anfrage' : ($locale === 'ua' ? 'Ціна за запитом' : 'Price on request');
            }
            $amount = number_format($work->price_cents / 100, 0, '.', ',');
            return $work->currency . ' ' . $amount;
        };
    @endphp

    <div class="flex items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">{{ __('ui.gallery') }}</h1>
            <p class="mt-2 text-sm text-zinc-600">
                {{ $locale === 'de' ? 'Verfügbare Werke' : ($locale === 'ua' ? 'Доступні роботи' : 'Available works') }}
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('gallery', ['locale' => $locale]) }}" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                {{ $locale === 'de' ? 'Technik' : ($locale === 'ua' ? 'Техніка' : 'Technique') }}
            </label>
            <select name="technique" class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                <option value="">
                    {{ $locale === 'de' ? 'Alle Techniken' : ($locale === 'ua' ? 'Усі техніки' : 'All techniques') }}
                </option>
                @foreach ($techniques as $technique)
                    <option value="{{ $technique->id }}" @selected(($filters['technique'] ?? '') == $technique->id)>
                        {{ $technique->name($locale) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                {{ $locale === 'de' ? 'Jahr' : ($locale === 'ua' ? 'Рік' : 'Year') }}
            </label>
            <select name="year" class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                <option value="">
                    {{ $locale === 'de' ? 'Alle Jahre' : ($locale === 'ua' ? 'Усі роки' : 'All years') }}
                </option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-3">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-zinc-900 text-white hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
            >
                {{ __('ui.filter') }}
            </button>
            <a
                href="{{ route('gallery', ['locale' => $locale]) }}"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium border border-zinc-300 bg-white text-zinc-900 hover:bg-zinc-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
            >
                {{ __('ui.reset') }}
            </a>
        </div>
    </form>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($works as $work)
            @php
                $query = array_filter([
                    'technique' => $filters['technique'] ?? null,
                    'year' => $filters['year'] ?? null,
                ]);
                $link = route('gallery.show', ['locale' => $locale, 'work' => $work->id]);
                if (!empty($query)) {
                    $link .= '?' . http_build_query($query);
                }
            @endphp
            <a
                href="{{ $link }}"
                class="group block rounded-2xl text-left no-underline hover:no-underline focus:no-underline focus-visible:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white select-none"
            >
                <div class="h-[240px] sm:h-[260px] lg:h-[360px] overflow-hidden rounded-2xl bg-zinc-100 shadow-sm ring-1 ring-zinc-200 transition-shadow duration-200 group-hover:shadow-md flex items-center justify-center p-2 lg:p-3">
                    <img
                        src="{{ $work->mainImageUrl() }}"
                        alt="{{ $work->technique?->name($locale) ?? '' }}"
                        class="h-full w-full max-h-full max-w-full object-contain select-none"
                        draggable="false"
                        loading="lazy"
                    >
                </div>
                <div class="mt-3 text-sm text-zinc-500">{{ $work->technique?->name($locale) ?? '—' }}</div>
                <div class="mt-1 text-lg font-semibold text-zinc-900">
                    {{ $work->year ?? '—' }} · {{ $work->size_label ?? '—' }}
                </div>
                <div class="mt-1 text-sm text-zinc-600">{{ $priceLabel($work) }}</div>
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
</x-layouts.public>
