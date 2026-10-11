@props(['title', 'description' => null, 'badge' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6']) }}>
    <div class="min-w-0">
        <div class="flex items-center gap-2.5 flex-wrap">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 truncate">
                {{ $title }}
            </h1>
            @if($badge)
                <div class="flex-shrink-0">{{ $badge }}</div>
            @endif
        </div>
        @if($description)
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2.5 flex-wrap flex-shrink-0 self-stretch sm:self-auto">
            {{ $actions }}
        </div>
    @elseif($slot->isNotEmpty())
        <div class="flex items-center gap-2.5 flex-wrap flex-shrink-0 self-stretch sm:self-auto">
            {{ $slot }}
        </div>
    @endif
</div>
