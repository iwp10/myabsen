@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4']) }}>
    <div>
        <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            @if(isset($icon))
                <span class="text-blue-600 dark:text-blue-400">{{ $icon }}</span>
            @endif
            <span>{{ $title }}</span>
        </h2>
        @if($description)
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                {{ $description }}
            </p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="flex items-center gap-2 flex-shrink-0 self-start sm:self-auto">
            {{ $slot }}
        </div>
    @endif
</div>
