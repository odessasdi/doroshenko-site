<x-layouts.public :title="__('ui.gallery')">
    @php
        $locale = app()->getLocale();
        $queryBase = array_filter([
            'technique' => $filters['technique'] ?? null,
            'genre' => $filters['genre'] ?? null,
            'surface' => $filters['surface'] ?? null,
            'year' => $filters['year'] ?? null,
        ]);
        $cleanUtf8 = fn ($value) => mb_scrub((string) $value, 'UTF-8');
        $imageUrls = array_map($cleanUtf8, $imageUrls);
        $description = trim($cleanUtf8($work->description($locale)));
        $descriptionParts = explode("\n", str_replace(["\r\n", "\r"], "\n", $description), 2);
        $descriptionTitle = trim($descriptionParts[0] ?? '');
        $fallbackTitle = $cleanUtf8($work->genre?->name($locale) ?? $work->technique?->name($locale) ?? __('ui.artwork'));
        $displayTitle = $cleanUtf8($descriptionTitle !== '' ? $descriptionTitle : $fallbackTitle);

        $priceText = function () use ($work) {
            if (!$work->price_cents) {
                return __('ui.price_on_request');
            }

            $amount = number_format($work->price_cents / 100, 0, '.', ',');

            return trim(($work->currency ? $work->currency . ' ' : '') . $amount);
        };

        $availabilityText = $work->price_cents ? __('ui.available') : __('ui.price_on_request');

        $buildLink = function ($id) use ($locale, $queryBase) {
            $query = $queryBase;
            $url = route('gallery.show', ['locale' => $locale, 'work' => $id]);

            return !empty($query) ? $url . '?' . http_build_query($query) : $url;
        };

        $inquireLink = route('contacts', ['locale' => $locale]) . '?' . http_build_query([
            'work' => $work->id,
            'subject' => $displayTitle,
        ]);

        $detailRows = array_values(array_filter([
            ['label' => __('ui.technique'), 'value' => $work->technique?->name($locale)],
            ['label' => __('ui.genre'), 'value' => $work->genre?->name($locale)],
            ['label' => __('ui.surface'), 'value' => $work->surface?->name($locale)],
            ['label' => __('ui.year'), 'value' => $work->year],
            ['label' => __('ui.size'), 'value' => $work->size_label],
            ['label' => __('ui.price'), 'value' => $priceText()],
            ['label' => __('ui.availability'), 'value' => $availabilityText],
        ], fn($row) => filled($row['value'])));
    @endphp

    <div
        x-data="{
            activeIndex: 0,
            images: @js($imageUrls),
            activeSrc: '',
            lightboxOpen: false,
            bodyOverflow: '',
            init() {
                this.activeSrc = this.images[this.activeIndex] || '';
                this.$watch('lightboxOpen', (open) => {
                    if (open) {
                        this.bodyOverflow = document.body.style.overflow;
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = this.bodyOverflow;
                    }
                });
            },
            setActive(index) {
                if (!this.images.length) {
                    this.activeIndex = 0;
                    this.activeSrc = '';
                    return;
                }

                this.activeIndex = index;
                this.activeSrc = this.images[this.activeIndex] || this.images[0] || '';
            },
            prevImage() {
                if (this.images.length < 2) {
                    return;
                }

                const nextIndex = this.activeIndex === 0 ? this.images.length - 1 : this.activeIndex - 1;
                this.setActive(nextIndex);
            },
            nextImage() {
                if (this.images.length < 2) {
                    return;
                }

                const nextIndex = this.activeIndex === this.images.length - 1 ? 0 : this.activeIndex + 1;
                this.setActive(nextIndex);
            },
            openLightbox(index = null) {
                if (index !== null) {
                    this.setActive(index);
                }

                this.lightboxOpen = true;
                this.$nextTick(() => this.$refs.lightboxClose?.focus());
            },
            closeLightbox() {
                this.lightboxOpen = false;
            }
        }"
        @keydown.escape.window="if (lightboxOpen) closeLightbox()"
        @keydown.arrow-left.window="if (lightboxOpen) { $event.preventDefault(); prevImage(); }"
        @keydown.arrow-right.window="if (lightboxOpen) { $event.preventDefault(); nextImage(); }"
        class="mx-auto max-w-6xl"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('gallery', array_merge(['locale' => $locale], $queryBase)) }}"
                class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
            >
                {{ __('ui.back_to_gallery') }}
            </a>
            <div class="flex items-center gap-2">
                @if ($prevId)
                    <a
                        href="{{ $buildLink($prevId) }}"
                        class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                        aria-label="{{ __('ui.previous') }}"
                    >
                        ← {{ __('ui.previous') }}
                    </a>
                @endif
                @if ($nextId)
                    <a
                        href="{{ $buildLink($nextId) }}"
                        class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                        aria-label="{{ __('ui.next') }}"
                    >
                        {{ __('ui.next') }} →
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-12 md:gap-12">
            <div class="md:col-span-7">
                <div
                    class="relative flex aspect-[3/4] max-h-[75vh] items-center justify-center overflow-hidden rounded-3xl bg-zinc-100 px-4 py-6 shadow-sm ring-1 ring-zinc-200"
                >
                    <button
                        type="button"
                        class="group relative block h-full w-full cursor-zoom-in"
                        @click="openLightbox()"
                        aria-label="{{ __('ui.artwork') }}"
                    >
                        <img
                            :src="activeSrc"
                            :alt="@js($displayTitle)"
                            class="h-full w-full max-h-full max-w-full object-contain"
                        >
                    </button>
                </div>

                @if (count($imageUrls) > 1)
                    <div class="mt-5 -mx-2 flex gap-4 overflow-x-auto overflow-y-visible px-2 py-2">
                        @foreach ($imageUrls as $index => $url)
                            <button
                                type="button"
                                class="h-32 w-40 shrink-0 rounded-xl border border-zinc-200 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                                :class="activeIndex === {{ $index }} ? 'ring-2 ring-zinc-900 ring-offset-2 ring-offset-white' : 'opacity-70 hover:opacity-100'"
                                @click="setActive({{ $index }})"
                                @keydown.enter.prevent="setActive({{ $index }})"
                                @keydown.space.prevent="setActive({{ $index }})"
                                aria-label="{{ __('ui.artwork') }} {{ $index + 1 }}"
                            >
                                <span class="block h-full w-full overflow-hidden rounded-lg">
                                    <img src="{{ $url }}" alt="" class="h-full w-full object-cover" draggable="false">
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="md:col-span-5">
                <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl">
                    {{ $displayTitle }}
                </h1>
                <p class="mt-2 text-base text-zinc-600">
                    {{ $work->year ?? '—' }} · {{ $work->size_label ?? '—' }}
                </p>

                <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('ui.price') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ $priceText() }}</div>

                    <div class="mt-4 flex items-center gap-2 text-sm text-zinc-700">
                        <span class="inline-flex rounded-full border border-zinc-300 bg-zinc-50 px-3 py-1 font-medium">
                            {{ $availabilityText }}
                        </span>
                    </div>

                    <a
                        href="{{ $inquireLink }}"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/30 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                    >
                        {{ __('ui.inquire') }}
                    </a>
                </div>

                @if ($description !== '')
                    <section class="mt-7">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">{{ __('ui.about_this_work') }}</h2>
                        <p class="mt-2 whitespace-pre-line text-base leading-relaxed text-zinc-700">{{ $description }}</p>
                    </section>
                @endif

                @if (!empty($detailRows))
                    <section class="mt-7">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">{{ __('ui.details') }}</h2>
                        <dl class="mt-3 divide-y divide-zinc-200 rounded-2xl border border-zinc-200 bg-white">
                            @foreach ($detailRows as $row)
                                <div class="grid grid-cols-2 gap-3 px-4 py-3 text-sm">
                                    <dt class="text-zinc-500">{{ $row['label'] }}</dt>
                                    <dd class="text-right font-medium text-zinc-900">{{ $row['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>

        @if ($moreWorks->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ __('ui.more_works') }}</h2>

                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($moreWorks as $moreWork)
                        @php
                            $link = route('gallery.show', ['locale' => $locale, 'work' => $moreWork->id]);
                            if (!empty($queryBase)) {
                                $link .= '?' . http_build_query($queryBase);
                            }
                            $morePrice = $moreWork->price_cents
                                ? trim(($moreWork->currency ? $moreWork->currency . ' ' : '') . number_format($moreWork->price_cents / 100, 0, '.', ','))
                                : __('ui.price_on_request');
                            $moreCategoryLabel = collect([
                                $moreWork->technique?->name($locale),
                                $moreWork->genre?->name($locale),
                                $moreWork->surface?->name($locale),
                            ])->filter()->implode(' · ');
                        @endphp
                        <a
                            href="{{ $link }}"
                            class="group block rounded-2xl text-left no-underline hover:no-underline focus:no-underline focus-visible:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900/20 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
                        >
                            <div class="flex h-[240px] items-center justify-center overflow-hidden rounded-2xl bg-zinc-100 p-3 shadow-sm ring-1 ring-zinc-200 transition-shadow duration-200 group-hover:shadow-md">
                                <img
                                    src="{{ $moreWork->mainImageUrl() }}"
                                    alt="{{ $moreCategoryLabel !== '' ? $moreCategoryLabel : __('ui.artwork') }}"
                                    class="h-full w-full max-h-full max-w-full object-contain"
                                    loading="lazy"
                                >
                            </div>
                            <div class="mt-3 text-sm text-zinc-500">{{ $moreCategoryLabel !== '' ? $moreCategoryLabel : '—' }}</div>
                            <div class="mt-1 text-lg font-semibold text-zinc-900">{{ $moreWork->year ?? '—' }} · {{ $moreWork->size_label ?? '—' }}</div>
                            <div class="mt-1 text-sm text-zinc-600">{{ $morePrice }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div
            x-cloak
            x-show="lightboxOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/90 px-4 py-6"
            @click.self="closeLightbox()"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('ui.artwork') }}"
        >
            <div class="relative flex h-full w-full max-w-6xl items-center justify-center">
                <button
                    x-ref="lightboxClose"
                    type="button"
                    class="absolute right-2 top-2 rounded-full border border-zinc-500/50 bg-zinc-900/70 p-2 text-white transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                    @click="closeLightbox()"
                    aria-label="Close"
                >
                    <span aria-hidden="true">✕</span>
                </button>

                <img
                    :src="activeSrc"
                    :alt="@js($displayTitle)"
                    class="max-h-full max-w-full object-contain"
                >

                <template x-if="images.length > 1">
                    <div>
                        <button
                            type="button"
                            class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full border border-zinc-500/50 bg-zinc-900/70 px-3 py-2 text-sm text-white transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                            @click="prevImage()"
                            aria-label="{{ __('ui.previous') }}"
                        >
                            ← {{ __('ui.previous') }}
                        </button>
                        <button
                            type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full border border-zinc-500/50 bg-zinc-900/70 px-3 py-2 text-sm text-white transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                            @click="nextImage()"
                            aria-label="{{ __('ui.next') }}"
                        >
                            {{ __('ui.next') }} →
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-layouts.public>
