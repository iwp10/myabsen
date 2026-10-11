@props(['action' => null, 'method' => 'GET'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl p-4 sm:p-5 shadow-xs mb-6']) }}>
    @if($action)
        <form action="{{ $action }}" method="{{ $method }}" class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{ $slot }}
        </form>
    @else
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
