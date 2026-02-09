<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Works</h1>
            <p class="mt-1 text-sm text-zinc-600">Manage works catalog.</p>
        </div>
        <a
            href="/admin/works/create"
            class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
        >
            Add Work
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
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Search</label>
                <input
                    type="text"
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    placeholder="ID or description"
                    wire:model.live.debounce.300ms="q"
                >
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Technique</label>
                <select
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="techniqueId"
                >
                    <option value="">All techniques</option>
                    @foreach ($techniques as $technique)
                        <option value="{{ $technique->id }}">{{ $technique->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Year</label>
                <input
                    type="number"
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="year"
                >
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Published</label>
                <select
                    class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                    wire:model.live="published"
                >
                    <option value="all">All</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Per page</label>
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
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
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
                    <th class="px-4 py-3">Thumbnail</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('year')">
                            Year
                            @if ($sortBy === 'year')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">Technique</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('price')">
                            Price
                            @if ($sortBy === 'price')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">Published</th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('sort_order')">
                            Sort
                            @if ($sortBy === 'sort_order')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3">
                        <button type="button" class="inline-flex items-center gap-1" wire:click="sort('created_at')">
                            Created
                            @if ($sortBy === 'created_at')
                                <span>{{ $sortDir === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right">Actions</th>
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
                        <td class="px-4 py-3">{{ $work->technique?->name_en ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $work->size_label ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $work->price_label ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $work->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $work->is_published ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $work->sort_order }}</td>
                        <td class="px-4 py-3">{{ $work->created_at?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a
                                    href="/admin/works/{{ $work->id }}/edit"
                                    class="rounded-lg border border-zinc-300 px-3 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                >
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                    wire:click="delete({{ $work->id }})"
                                    onclick="if (!confirm('Delete this work?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="10">No works yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $works->links() }}
    </div>
</div>
