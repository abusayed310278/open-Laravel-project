<div class="absolute inset-0 z-10 flex items-center px-4 sm:px-8 md:px-12 lg:px-16 py-3 sm:py-6 {{ $isLink ? 'pointer-events-none' : '' }}">
    {{-- Left Content Box --}}
    <div class="max-w-[92%] sm:max-w-[70%] md:max-w-[58%] lg:max-w-[50%] flex flex-col justify-center select-none text-left">
        @if ($slide->badge_text)
            <div class="mb-1 sm:mb-2 md:mb-3">
                <span class="inline-flex items-center px-2.5 sm:px-3.5 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[11px] md:text-xs font-bold tracking-wider uppercase shadow-xs"
                      style="background-color: {{ $slide->badge_bg_color ?: '#FEF08A' }}; color: {{ $slide->badge_text_color ?: '#854D0E' }};">
                    {{ $slide->badge_text }}
                </span>
            </div>
        @endif

        <h2 class="font-black tracking-tight leading-[1.08] sm:leading-[1.05]"
            style="font-size: clamp(1.25rem, 3.2vw, 3.8rem);">
            @if ($slide->title_line1)
                <span class="block" style="color: {{ $slide->title_line1_color ?: '#0F172A' }};">
                    {{ $slide->title_line1 }}
                </span>
            @endif
            @if ($slide->title_line2)
                <span class="block" style="color: {{ $slide->title_line2_color ?: '#EAB308' }};">
                    {{ $slide->title_line2 }}
                </span>
            @endif
        </h2>

        @if ($slide->subtitle)
            <p class="mt-1 sm:mt-2 md:mt-3 line-clamp-2 sm:line-clamp-none max-w-xl text-[10px] sm:text-xs md:text-sm lg:text-base leading-relaxed"
               style="color: {{ $slide->subtitle_color ?: '#475569' }};">
                {{ $slide->subtitle }}
            </p>
        @endif

        @php $slideFeatures = $slide->features ?? []; @endphp
        @if (!empty($slideFeatures))
            <div class="mt-2 sm:mt-3.5 md:mt-5 pt-0.5 sm:pt-1.5 flex items-center gap-2 sm:gap-3 md:gap-5 flex-wrap sm:flex-nowrap">
                @foreach ($slideFeatures as $f)
                    @if (!empty($f['title']))
                        <div class="flex items-center gap-1.5 sm:gap-2.5 {{ !$loop->first ? 'sm:border-l sm:border-gray-300 dark:sm:border-gray-700 sm:pl-2.5 md:pl-4' : '' }}">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 md:w-8 md:h-8 shrink-0 flex items-center justify-center"
                                 style="color: {{ $f['color'] ?? '#EAB308' }};">
                                <x-hero-icon :name="$f['icon'] ?? 'shield-check'" class="w-full h-full" />
                            </div>
                            <div class="leading-tight">
                                <div class="text-[10px] sm:text-xs md:text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $f['title'] }}
                                </div>
                                @if (!empty($f['subtitle']))
                                    <div class="text-[8px] sm:text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                        {{ $f['subtitle'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Top-Right Sticker / Callout --}}
    @if ($slide->callout_text)
        <div class="hidden sm:block absolute top-[5%] md:top-[8%] right-[4%] md:right-[7%] lg:right-[9%] transform -rotate-[6deg] pointer-events-none select-none text-center">
            <div class="font-bold tracking-wide italic leading-[1.05]"
                 style="font-family: 'Caveat', cursive, sans-serif; font-size: clamp(1.1rem, 2.2vw, 2.5rem); color: {{ $slide->callout_color ?: '#1E3A8A' }}; white-space: pre-line;">
                {{ $slide->callout_text }}
            </div>
            <svg viewBox="0 0 140 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-2.5 sm:h-3.5 md:h-4 mt-0.5 mx-auto" style="color: {{ $slide->callout_accent_color ?: '#EAB308' }};">
                <path d="M3 13.5C35 4.5 105 3.5 137 11.5" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
            </svg>
        </div>
    @endif
</div>
