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
        'class' => 'group relative block bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 ' . $borderAksen . ' rounded-xl p-4 sm:p-5 shadow-xs dark:shadow-lg dark:shadow-black/40 dark:ring-1 dark:ring-white/5 transition duration-150 ease-in-out ' . ($href ? 'hover:shadow-md hover:border-slate-400 dark:hover:border-slate-500 cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50' : '')
    ]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 truncate">
                    {{ $lbl }}
                </span>
                @if($badge)
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            <div class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">
                {{ $value }}
            </div>
            @if($description)
                <p class="mt-1 text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-300 truncate">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if(isset($iconSlot))
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center flex-shrink-0 group-hover:scale-105 motion-safe:transition-transform">
                {{ $iconSlot }}
            </div>
        @elseif($icon)
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center flex-shrink-0 group-hover:scale-105 motion-safe:transition-transform">
                {!! $icon !!}
            </div>
        @endif
    </div>

    @if($href)
        <div class="mt-3 pt-2 border-t-2 border-slate-200 dark:border-slate-700 flex items-center justify-end text-xs font-semibold text-emerald-700 dark:text-emerald-400 group-hover:translate-x-0.5 motion-safe:transition-transform">
            <span>Lihat rincian &rarr;</span>
        </div>
    @endif
</{{ $tag }}>
