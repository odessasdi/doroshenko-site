<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Techniques</h1>
            <p class="mt-1 text-sm text-zinc-600">Manage the techniques list.</p>
        </div>
        <button
            type="button"
            class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
            wire:click="resetForm"
        >
            New
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Name (EN)</th>
                        <th class="px-4 py-3">Name (DE)</th>
                        <th class="px-4 py-3">Name (UA)</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($techniques as $technique)
                        <tr class="text-zinc-800">
                            <td class="px-4 py-3">{{ $technique->id }}</td>
                            <td class="px-4 py-3">{{ $technique->name_en }}</td>
                        <td class="px-4 py-3">{{ $technique->name_de }}</td>
                            <td class="px-4 py-3">{{ $technique->name_ua }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-zinc-300 px-3 py-1 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                        wire:click="edit({{ $technique->id }})"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                        wire:click="delete({{ $technique->id }})"
                                        onclick="if (!confirm('Delete this technique?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-zinc-500" colspan="5">No techniques yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Edit technique' : 'New technique' }}
            </h2>
            <div class="mt-4 grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700" for="name_en">Name (EN)</label>
                    <input
                        id="name_en"
                        type="text"
                        class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                        wire:model.defer="name_en"
                    >
                    @error('name_en') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700" for="name_de">Name (DE)</label>
                    <input
                        id="name_de"
                        type="text"
                        class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                        wire:model.defer="name_de"
                    >
                    @error('name_de') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700" for="name_ua">Name (UA)</label>
                    <input
                        id="name_ua"
                        type="text"
                        class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                        wire:model.defer="name_ua"
                    >
                    @error('name_ua') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                    wire:click="save"
                >
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    wire:click="resetForm"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>
</div>
