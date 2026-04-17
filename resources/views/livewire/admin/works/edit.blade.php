<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">Редагування роботи</h1>
            <p class="mt-1 text-sm text-zinc-600">Оновлення інформації та зображень роботи.</p>
        </div>
        <a
            href="/admin/works"
            class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
        >
            Назад до списку
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900">Деталі</h2>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60"
                        wire:click="generateDescriptions"
                        wire:loading.attr="disabled"
                        wire:target="generateDescriptions"
                        onclick="const hasDescriptions = ['description_ua', 'description_en', 'description_de'].some((id) => document.getElementById(id)?.value.trim() !== ''); if (hasDescriptions && !confirm('Перезаписати поточні описи AI-генерацією?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                    >
                        <span wire:loading.remove wire:target="generateDescriptions">ЗГЕНЕРУВАТИ ОПИС</span>
                        <span wire:loading wire:target="generateDescriptions">Генеруємо опис...</span>
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="technique_id">Техніка</label>
                        <select
                            id="technique_id"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model="technique_id"
                        >
                            <option value="">Оберіть техніку</option>
                            @foreach ($techniques as $technique)
                                <option value="{{ $technique->id }}">{{ $technique->name_ua }}</option>
                            @endforeach
                        </select>
                        @error('technique_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="genre_id">Жанр</label>
                        <select
                            id="genre_id"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model="genre_id"
                        >
                            <option value="">Без жанру</option>
                            @foreach ($genres as $genre)
                                <option value="{{ $genre->id }}">{{ $genre->name_ua }}</option>
                            @endforeach
                        </select>
                        @error('genre_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="surface_id">Основа</label>
                        <select
                            id="surface_id"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model="surface_id"
                        >
                            <option value="">Без основи</option>
                            @foreach ($surfaces as $surface)
                                <option value="{{ $surface->id }}">{{ $surface->name_ua }}</option>
                            @endforeach
                        </select>
                        @error('surface_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="year">Рік</label>
                        <input
                            id="year"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="year"
                        >
                        @error('year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @include('livewire.admin.works.partials.paper-presets', ['confirmOnChange' => true])
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="size_w_mm">Ширина (мм)</label>
                        <input
                            id="size_w_mm"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="size_w_mm"
                        >
                        @error('size_w_mm') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="size_h_mm">Висота (мм)</label>
                        <input
                            id="size_h_mm"
                            type="number"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="size_h_mm"
                        >
                        @error('size_h_mm') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="price">Ціна</label>
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
                        <label class="text-sm font-medium text-zinc-700" for="currency">Валюта</label>
                        <select
                            id="currency"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="currency"
                        >
                            <option value="">Оберіть валюту</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="UAH">UAH</option>
                        </select>
                        @error('currency') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="sort_order">Порядок сортування</label>
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
                        <label class="text-sm font-medium text-zinc-700" for="is_published">Опубліковано</label>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="description_en">Опис (EN)</label>
                        <textarea
                            id="description_en"
                            rows="3"
                            class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                            wire:model.defer="description_en"
                        ></textarea>
                        @error('description_en') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                    <label class="text-sm font-medium text-zinc-700" for="description_de">Опис (DE)</label>
                    <textarea
                        id="description_de"
                        rows="3"
                        class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                        wire:model.defer="description_de"
                    ></textarea>
                    @error('description_de') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="description_ua">Опис (UA)</label>
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
                <h2 class="text-lg font-semibold text-zinc-900">Головне зображення</h2>
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
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">AI-візуалізації інтерʼєру</h2>
                        <p class="mt-1 text-sm text-zinc-500">У бібліотеці: {{ $visualizationCount }} / {{ $visualizationLimit }}</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500">Масштаб у сцені будується за фактичним розміром картини з полів ширини та висоти.</p>
                    </div>
                    <div class="w-full max-w-xs space-y-3">
                        <div>
                            <label class="text-sm font-medium text-zinc-700" for="visualization_preset">Сцена</label>
                            <select
                                id="visualization_preset"
                                class="mt-1 w-full rounded-lg border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900"
                                wire:model="visualization_preset"
                            >
                                @foreach ($visualizationPresets as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('visualization_preset') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60"
                            wire:click="generateVisualization"
                            wire:loading.attr="disabled"
                            wire:target="generateVisualization"
                        >
                            <span wire:loading.remove wire:target="generateVisualization">ЗГЕНЕРУВАТИ ВІЗУАЛІЗАЦІЮ</span>
                            <span wire:loading wire:target="generateVisualization">Генеруємо...</span>
                        </button>
                    </div>
                </div>

                @if ($workImages->count())
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($workImages as $image)
                            <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                                <img src="{{ $image->url }}" alt="" class="h-40 w-full object-cover">
                                <div class="flex items-center justify-between gap-3 px-3 py-2">
                                    <div class="text-sm font-medium text-zinc-700">{{ $image->preset_label ?? 'Візуалізація' }}</div>
                                    <button
                                        type="button"
                                        class="rounded-full bg-zinc-900 px-3 py-1 text-xs text-white transition hover:bg-zinc-800"
                                        wire:click="deleteVisualization({{ $image->id }})"
                                        onclick="if (!confirm('Видалити цю візуалізацію?')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm leading-6 text-zinc-600">
                        Бібліотека поки порожня. Оберіть сцену та згенеруйте першу візуалізацію по головному фото.
                    </p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a
                    href="/admin/works"
                    class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    Назад до списку
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    Зберегти роботу
                </button>
            </div>
        </div>
    </form>
</div>
