@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'bg-emerald-100 text-emerald-900 border-l-4 border-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-200 dark:border-emerald-400 font-semibold shadow-xs group flex items-center min-h-[44px] px-3.5 py-2.5 text-sm rounded-r-lg transition duration-150 ease-in-out [&>svg]:w-5 [&>svg]:h-5 [&>svg]:mr-3 [&>svg]:flex-shrink-0 [&>svg]:text-emerald-700 dark:[&>svg]:text-emerald-300'
            : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 group flex items-center min-h-[44px] px-3.5 py-2.5 text-sm font-medium rounded-lg transition duration-150 ease-in-out [&>svg]:w-5 [&>svg]:h-5 [&>svg]:mr-3 [&>svg]:flex-shrink-0 [&>svg]:text-slate-600 dark:[&>svg]:text-slate-400 group-hover:[&>svg]:text-slate-900 dark:group-hover:[&>svg]:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
