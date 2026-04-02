@php
    $currentWidth = (int) ($size_w_mm ?? 0);
    $currentHeight = (int) ($size_h_mm ?? 0);
@endphp

<div class="sm:col-span-2">
    <label class="text-sm font-medium text-zinc-700">Формат</label>

    <div class="mt-3 flex flex-wrap items-start gap-3">
        @foreach ($paperPresets as $presetKey => $preset)
            @php
                $isLandscape = $preset['width'] > $preset['height'];
                $paperCode = str_starts_with($presetKey, 'a3') ? 'A3' : 'A4';
                $isActive = $currentWidth === $preset['width'] && $currentHeight === $preset['height'];
                $cardClasses = $isActive
                    ? 'bg-zinc-100 text-zinc-950'
                    : 'bg-white text-zinc-700 hover:bg-zinc-50';
                $confirmMessage = "Перезаписати поточні розміри значенням {$preset['label']}?";
            @endphp

            <button
                type="button"
                class="group inline-flex h-24 w-24 items-center justify-center rounded-2xl transition {{ $cardClasses }}"
                wire:click="applyPaperPreset('{{ $presetKey }}')"
                @if ($confirmOnChange)
                    onclick="const widthInput = document.getElementById('size_w_mm'); const heightInput = document.getElementById('size_h_mm'); const nextWidth = '{{ $preset['width'] }}'; const nextHeight = '{{ $preset['height'] }}'; const willChange = String(widthInput?.value ?? '') !== nextWidth || String(heightInput?.value ?? '') !== nextHeight; if (willChange && !confirm('{{ $confirmMessage }}')) { event.stopImmediatePropagation(); event.preventDefault(); }"
                @endif
                title="{{ $preset['label'] }}"
                aria-label="{{ $preset['label'] }}"
            >
                @if ($isLandscape)
                    <svg
                        viewBox="0 0 60 40"
                        class="h-[3.75rem] w-[5.75rem]"
                        aria-hidden="true"
                        fill="none"
                    >
                        <path
                            d="M6.5 8.5C6.5 6.567 8.067 5 10 5H38.5L51.5 18V30C51.5 31.933 49.933 33.5 48 33.5H10C8.067 33.5 6.5 31.933 6.5 30V8.5Z"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M38.5 5V15.5C38.5 16.328 39.172 17 40 17H51.5"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linejoin="round"
                        />
                        <text
                            x="29"
                            y="24.5"
                            text-anchor="middle"
                            font-size="9.5"
                            font-weight="700"
                            fill="currentColor"
                            style="font-family: ui-sans-serif, system-ui, sans-serif;"
                        >
                            {{ $paperCode }}
                        </text>
                    </svg>
                @else
                    <svg
                        viewBox="0 0 40 60"
                        class="h-[5.75rem] w-[3.75rem]"
                        aria-hidden="true"
                        fill="none"
                    >
                        <path
                            d="M5 10C5 8.067 6.567 6.5 8.5 6.5H26L35 15.5V50C35 51.933 33.433 53.5 31.5 53.5H8.5C6.567 53.5 5 51.933 5 50V10Z"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M26 6.5V14C26 14.828 26.672 15.5 27.5 15.5H35"
                            stroke="currentColor"
                            stroke-width="2.4"
                            stroke-linejoin="round"
                        />
                        <text
                            x="20"
                            y="33"
                            text-anchor="middle"
                            font-size="9.5"
                            font-weight="700"
                            fill="currentColor"
                            style="font-family: ui-sans-serif, system-ui, sans-serif;"
                        >
                            {{ $paperCode }}
                        </text>
                    </svg>
                @endif
            </button>
        @endforeach
    </div>

</div>
