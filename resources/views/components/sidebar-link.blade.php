@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'bg-blue-600 text-white font-semibold shadow-xs border-l-4 border-l-blue-200 dark:border-l-white group flex items-center min-h-[44px] px-3.5 py-2.5 text-sm rounded-r-lg transition duration-150 ease-in-out [&>svg]:w-5 [&>svg]:h-5 [&>svg]:mr-3 [&>svg]:flex-shrink-0 [&>svg]:text-white'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 hover:text-blue-600 dark:hover:text-blue-400 group flex items-center min-h-[44px] px-3.5 py-2.5 text-sm font-medium rounded-lg transition duration-150 ease-in-out [&>svg]:w-5 [&>svg]:h-5 [&>svg]:mr-3 [&>svg]:flex-shrink-0 [&>svg]:text-gray-400 dark:[&>svg]:text-gray-400 group-hover:[&>svg]:text-blue-600 dark:group-hover:[&>svg]:text-blue-400';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
