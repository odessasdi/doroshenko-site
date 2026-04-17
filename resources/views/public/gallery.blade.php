<x-layouts.public :title="__('ui.gallery')">
    @php
        $locale = app()->getLocale();
    @endphp

    <div class="flex items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">{{ __('ui.gallery') }}</h1>
            <p class="mt-2 text-sm text-zinc-600">
                {{ $locale === 'de' ? 'Verfügbare Werke' : ($locale === 'ua' ? 'Доступні роботи' : 'Available works') }}
            </p>
        </div>
    </div>

    <form id="gallery-filter-form" method="GET" action="{{ route('gallery', ['locale' => $locale]) }}" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                {{ $locale === 'de' ? 'Genre' : ($locale === 'ua' ? 'Жанр' : 'Genre') }}
            </label>
            <select name="genre" class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                <option value="">
                    {{ $locale === 'de' ? 'Alle Genres' : ($locale === 'ua' ? 'Усі жанри' : 'All genres') }}
                </option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->id }}" @selected(($filters['genre'] ?? '') == $genre->id)>
                        {{ $genre->name($locale) }}
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
        <div>
            <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                {{ __('ui.surface') }}
            </label>
            <select name="surface" class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900">
                <option value="">
                    {{ $locale === 'de' ? 'Alle Bildträger' : ($locale === 'ua' ? 'Усі основи' : 'All surfaces') }}
                </option>
                @foreach ($surfaces as $surface)
                    <option value="{{ $surface->id }}" @selected(($filters['surface'] ?? '') == $surface->id)>
                        {{ $surface->name($locale) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-3">
            <a
                href="{{ route('gallery', ['locale' => $locale]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-zinc-50 px-4 py-2 text-sm font-semibold text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-400 hover:bg-white hover:shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
            >
                <span aria-hidden="true">↺</span>
                {{ __('ui.reset') }}
            </a>
            <noscript>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-zinc-900 text-white hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
                >
                    {{ __('ui.filter') }}
                </button>
            </noscript>
        </div>
    </form>

    <div class="relative">
        <div id="gallery-loading" class="pointer-events-none absolute inset-0 z-10 hidden items-center justify-center">
            <div class="flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm text-zinc-700 shadow">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-700"></span>
                <span>Завантаження...</span>
            </div>
        </div>
        <div id="gallery-results" class="transition-opacity duration-200">
            @include('public.partials.gallery-results', [
                'works' => $works,
                'filters' => $filters,
                'locale' => $locale,
            ])
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('gallery-filter-form');
            const results = document.getElementById('gallery-results');
            const loading = document.getElementById('gallery-loading');

            if (!form || !results || !loading) {
                return;
            }

            let debounceTimer = null;
            let currentRequest = null;

            const setLoading = (isLoading) => {
                results.classList.toggle('opacity-50', isLoading);
                loading.classList.toggle('hidden', !isLoading);
                loading.classList.toggle('flex', isLoading);
            };

            const syncFormFromUrl = (url) => {
                const parsed = new URL(url, window.location.origin);
                Array.from(form.elements).forEach((element) => {
                    if (!element.name) return;
                    const value = parsed.searchParams.get(element.name) ?? '';
                    if (element.type === 'checkbox') {
                        element.checked = parsed.searchParams.has(element.name);
                    } else {
                        element.value = value;
                    }
                });
            };

            const buildUrlFromForm = () => {
                const url = new URL(form.action, window.location.origin);
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.append(key, value);
                    }
                }
                return url;
            };

            const updateResults = async (url, { push = true } = {}) => {
                if (currentRequest) {
                    currentRequest.abort();
                }
                currentRequest = new AbortController();

                setLoading(true);
                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        signal: currentRequest.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    const html = await response.text();
                    results.innerHTML = html;

                    if (push) {
                        window.history.pushState({}, '', url.toString());
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        form.submit();
                    }
                } finally {
                    setLoading(false);
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                updateResults(buildUrlFromForm());
            });

            form.addEventListener('change', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement || target instanceof HTMLTextAreaElement)) {
                    return;
                }
                updateResults(buildUrlFromForm());
            });

            form.addEventListener('input', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) {
                    return;
                }
                if (!['text', 'search', 'email', 'number', 'url', 'tel'].includes(target.type)) {
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    updateResults(buildUrlFromForm());
                }, 300);
            });

            results.addEventListener('click', (event) => {
                const link = event.target.closest('a');
                if (!link) return;
                if (!link.href || link.target === '_blank' || link.hasAttribute('download')) return;
                if (!link.closest('nav[role="navigation"]')) return;
                if (link.origin !== window.location.origin) return;
                if (!link.pathname.includes('/gallery')) return;

                event.preventDefault();
                const url = new URL(link.href);
                syncFormFromUrl(url.toString());
                updateResults(url);
            });

            window.addEventListener('popstate', () => {
                const currentUrl = new URL(window.location.href);
                syncFormFromUrl(currentUrl.toString());
                updateResults(currentUrl, { push: false });
            });
        })();
    </script>
</x-layouts.public>
