@props(['title' => null, 'message' => null, 'description' => null, 'icon' => null])

@php
    $text = $message ?? $description;
@endphp

<div {{ $attributes->merge(['class' => 'py-10 sm:py-12 px-4 text-center rounded-xl bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600']) }}>
    @if(isset($iconSlot))
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 mb-3">
            {{ $iconSlot }}
        </div>
    @elseif($icon)
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 mb-3">
            {!! $icon !!}
        </div>
    @else
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 8v4" />
                <path d="M12 16h.01" />
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
            </svg>
        </div>
    @endif

    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">
        @if(isset($titleSlot))
            {{ $titleSlot }}
        @elseif($title)
            {!! $title !!}
        @endif
    </h3>

    @if($text)
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1.5 max-w-md mx-auto">
            {{ $text }}
        </p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-5 flex items-center justify-center gap-2.5 flex-wrap">
            {{ $slot }}
        </div>
    @endif
</div>
