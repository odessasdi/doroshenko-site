<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Роботи</h1>
            <p class="mt-1 text-sm text-zinc-600">Керування каталогом робіт.</p>
        </div>
        <a
            href="/admin/works/create"
            class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
        >
            Додати роботу
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr_0.6fr_auto]">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Пошук</label>
                <input
                    type="text"
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    placeholder="ID або опис"
                    wire:model.live.debounce.300ms="q"
                >
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Техніка</label>
                <select
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="techniqueId"
                >
                    <option value="">Усі техніки</option>
                    @foreach ($techniques as $technique)
                        <option value="{{ $technique->id }}">{{ $technique->name_ua ?? $technique->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Рік</label>
                <input
                    type="number"
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="year"
                >
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Опубліковано</label>
                <select
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="published"
                >
                    <option value="all">Усі</option>
                    <option value="published">Опубліковані</option>
                    <option value="draft">Чернетки</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">На сторінці</label>
                <select
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="perPage"
                >
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="flex items-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    wire:click="resetFilters"
                >
                    Скинути
                </button>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2 text-xs text-zinc-600">
            @if ($q !== '')
                <span class="rounded-full bg-zinc-100 px-3 py-1">Пошук: "{{ $q }}"</span>
            @endif
            @if ($techniqueId)
                <span class="rounded-full bg-zinc-100 px-3 py-1">
                    Техніка: {{ $techniques->firstWhere('id', $techniqueId)?->name_ua ?? $techniques->firstWhere('id', $techniqueId)?->name_en ?? '—' }}
                </span>
            @endif
            @if ($year)
                <span class="rounded-full bg-zinc-100 px-3 py-1">Рік: {{ $year }}</span>
            @endif
            @if ($published !== 'all')
                <span class="rounded-full bg-zinc-100 px-3 py-1">
                    Опубліковано: {{ $published === 'published' ? 'Опубліковані' : 'Чернетки' }}
                </span>
            @endif
            @if ($perPage !== 20)
                <span class="rounded-full bg-zinc-100 px-3 py-1">На сторінці: {{ $perPage }}</span>
            @endif
            @if ($q === '' && !$techniqueId && !$year && $published === 'all' && $perPage === 20)
                <span class="text-zinc-400">Немає активних фільтрів</span>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('id')">
                            ID
                            @if ($sortBy === 'id')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">Мініатюра</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('year')">
                            Рік
                            @if ($sortBy === 'year')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">Техніка</th>
                    <th class="px-4 py-3">Розмір</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('price')">
                            Ціна
                            @if ($sortBy === 'price')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">Опубліковано</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('sort_order')">
                            Порядок
                            @if ($sortBy === 'sort_order')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('created_at')">
                            Створено
                            @if ($sortBy === 'created_at')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right">Дії</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @forelse ($works as $work)
                    <tr class="text-zinc-800" wire:key="work-{{ $work->id }}">
                        <td class="px-4 py-3">{{ $work->id }}</td>
                        <td class="px-4 py-3">
                            <div class="h-16 w-24 overflow-hidden rounded-lg bg-zinc-100">
                                <img
                                    src="{{ $work->main_image_url }}"
                                    alt=""
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $work->year ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $work->technique?->name_ua ?? $work->technique?->name_en ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $work->size_label ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $work->price_label ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $work->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $work->is_published ? 'Так' : 'Ні' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $work->sort_order }}</td>
                        <td class="px-4 py-3">{{ $work->created_at?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="/admin/works/{{ $work->id }}/edit"
                                    class="rounded-lg border border-zinc-300 px-3 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                >
                                    Редагувати
                                </a>
                                <button
                                    type="button"
                                    class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                    wire:click="delete({{ $work->id }})"
                                    onclick="if (!confirm('Видалити цю роботу?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                                >
                                    Видалити
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="10">Поки що робіт немає.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-zinc-600">
            Показано {{ $works->firstItem() ?? 0 }}-{{ $works->lastItem() ?? 0 }} із {{ $works->total() }} результатів
        </p>
        <div class="flex items-center gap-2">
            @if ($works->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-400">Попередня</span>
            @else
                <a href="{{ $works->previousPageUrl() }}" class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm text-zinc-700 transition hover:bg-zinc-50">Попередня</a>
            @endif

            @if ($works->hasMorePages())
                <a href="{{ $works->nextPageUrl() }}" class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm text-zinc-700 transition hover:bg-zinc-50">Наступна</a>
            @else
                <span class="inline-flex items-center rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-400">Наступна</span>
            @endif
        </div>
    </div>
</div>
