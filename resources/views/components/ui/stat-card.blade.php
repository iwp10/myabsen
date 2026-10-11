@props([
    'label' => null,
    'title' => null,
    'value',
    'description' => null,
    'color' => 'blue',
    'icon' => null,
    'href' => null,
    'badge' => null,
])

@php
    $lbl = $label ?? $title ?? '';
    $borderAksen = \App\Support\Ui::statBorder($color);
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'group relative block bg-white dark:bg-slate-800 border-2 border-gray-300 dark:border-slate-700 ' . $borderAksen . ' rounded-xl p-4 sm:p-5 shadow-xs transition duration-150 ease-in-out ' . ($href ? 'hover:shadow-md hover:border-gray-400 dark:hover:border-slate-600 cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500/50' : '')
    ]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-slate-400 truncate">
                    {{ $lbl }}
                </span>
                @if($badge)
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            <div class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-slate-100 tabular-nums">
                {{ $value }}
            </div>
            @if($description)
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-600 dark:text-slate-400 truncate">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if(isset($iconSlot))
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gray-100 dark:bg-slate-700/60 text-gray-700 dark:text-slate-200 flex items-center justify-center flex-shrink-0 group-hover:scale-105 motion-safe:transition-transform">
                {{ $iconSlot }}
            </div>
        @elseif($icon)
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gray-100 dark:bg-slate-700/60 text-gray-700 dark:text-slate-200 flex items-center justify-center flex-shrink-0 group-hover:scale-105 motion-safe:transition-transform">
                {!! $icon !!}
            </div>
        @endif
    </div>

    @if($href)
        <div class="mt-3 pt-2 border-t-2 border-gray-200 dark:border-slate-700 flex items-center justify-end text-xs font-semibold text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 motion-safe:transition-transform">
            <span>Lihat rincian &rarr;</span>
        </div>
    @endif
</{{ $tag }}>
