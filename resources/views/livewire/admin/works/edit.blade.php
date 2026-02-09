<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Edit Work</h1>
            <p class="mt-1 text-sm text-zinc-600">Update work details and images.</p>
        </div>
        <a
            href="/admin/works"
            class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
        >
            Back to list
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-900">Details</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="technique_id">Technique</label>
                        <select
                            id="technique_id"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model="technique_id"
                        >
                            <option value="">Select technique</option>
                            @foreach ($techniques as $technique)
                                <option value="{{ $technique->id }}">{{ $technique->name_en }}</option>
                            @endforeach
                        </select>
                        @error('technique_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="year">Year</label>
                        <input
                            id="year"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="year"
                        >
                        @error('year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="size_w_mm">Width (mm)</label>
                        <input
                            id="size_w_mm"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="size_w_mm"
                        >
                        @error('size_w_mm') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="size_h_mm">Height (mm)</label>
                        <input
                            id="size_h_mm"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="size_h_mm"
                        >
                        @error('size_h_mm') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="price">Price</label>
                        <input
                            id="price"
                            type="text"
                            placeholder="1200.00"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="price"
                        >
                        @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="currency">Currency</label>
                        <select
                            id="currency"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="currency"
                        >
                            <option value="">Select currency</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="UAH">UAH</option>
                        </select>
                        @error('currency') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="sort_order">Sort order</label>
                        <input
                            id="sort_order"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="sort_order"
                        >
                        @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2 sm:mt-6">
                        <input
                            id="is_published"
                            type="checkbox"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="is_published"
                        >
                        <label class="text-sm font-medium text-zinc-700" for="is_published">Published</label>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="description_en">Description (EN)</label>
                        <textarea
                            id="description_en"
                            rows="3"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="description_en"
                        ></textarea>
                        @error('description_en') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                    <label class="text-sm font-medium text-zinc-700" for="description_de">Description (DE)</label>
                    <textarea
                        id="description_de"
                        rows="3"
                        class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                        wire:model.defer="description_de"
                    ></textarea>
                    @error('description_de') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="description_ua">Description (UA)</label>
                        <textarea
                            id="description_ua"
                            rows="3"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="description_ua"
                        ></textarea>
                        @error('description_ua') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-900">Main image</h2>
                <div class="mt-4 space-y-3">
                    <div class="overflow-hidden rounded-xl border border-zinc-200">
                        <img src="{{ $work->main_image_url }}" alt="" class="h-48 w-full object-cover">
                    </div>

                    <input
                        id="main_image"
                        type="file"
                        accept="image/*"
                        class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
                        wire:model="main_image"
                    >
                    @error('main_image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                    @if ($main_image)
                        <div class="overflow-hidden rounded-xl border border-zinc-200">
                            <img src="{{ $main_image->temporaryUrl() }}" alt="" class="h-48 w-full object-cover">
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-900">Extra images</h2>
                <p class="mt-1 text-sm text-zinc-500">Up to 3 images total. Remaining: {{ $remainingExtra }}</p>

                @if ($workImages->count())
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach ($workImages as $image)
                            <div class="group relative overflow-hidden rounded-lg border border-zinc-200">
                                <img src="{{ $image->url }}" alt="" class="h-20 w-full object-cover">
                                <button
                                    type="button"
                                    class="absolute right-1 top-1 rounded-full bg-black/60 px-2 py-1 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                    wire:click="deleteExtraImage({{ $image->id }})"
                                    onclick="if (!confirm('Delete this image?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                                >
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4 space-y-3">
                    <input
                        id="extra_images"
                        type="file"
                        multiple
                        accept="image/*"
                        class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
                        wire:model="extra_images"
                        @if ($remainingExtra === 0) disabled @endif
                    >
                    @error('extra_images') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    @error('extra_images.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                    @if ($extra_images)
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($extra_images as $image)
                                <div class="overflow-hidden rounded-lg border border-zinc-200">
                                    <img src="{{ $image->temporaryUrl() }}" alt="" class="h-20 w-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a
                    href="/admin/works"
                    class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    Back
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    Save
                </button>
            </div>
        </div>
    </form>
</div>
