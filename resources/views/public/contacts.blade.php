<x-layouts.public :title="__('ui.contact_title')">
    <div class="bg-zinc-50 py-10 sm:py-14">
        <div class="mx-auto max-w-xl px-4">
            <div
                x-data="{ copied: false, copyEmail() { navigator.clipboard.writeText('ceo.equalympic@gmail.com').then(() => { this.copied = true; setTimeout(() => this.copied = false, 1500); }); } }"
                class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">{{ __('ui.contact_kicker') }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900 sm:text-4xl">{{ __('ui.contact_title') }}</h1>

                <p class="mt-5 text-base leading-relaxed text-zinc-600 sm:text-lg">
                    {{ __('ui.contact_text') }}
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a
                        href="mailto:ceo.equalympic@gmail.com"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 px-5 py-3 text-white hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M3 5.75A1.75 1.75 0 0 1 4.75 4h10.5A1.75 1.75 0 0 1 17 5.75v8.5A1.75 1.75 0 0 1 15.25 16H4.75A1.75 1.75 0 0 1 3 14.25v-8.5Z" stroke="currentColor" stroke-width="1.5"/>
                            <path d="m3.5 6.25 5.61 4.18a1.5 1.5 0 0 0 1.78 0L16.5 6.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="font-medium">ceo.equalympic@gmail.com</span>
                    </a>

                    <button
                        type="button"
                        @click="copyEmail()"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-3 text-zinc-900 hover:bg-zinc-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 focus-visible:ring-offset-2"
                    >
                        {{ __('ui.copy') }}
                    </button>
                </div>

                <p x-cloak x-show="copied" class="mt-3 text-sm text-zinc-600">{{ __('ui.copied') }}</p>
                <p class="mt-4 text-sm text-zinc-500">{{ __('ui.contact_note') }}</p>
            </div>
        </div>
    </div>
</x-layouts.public>
