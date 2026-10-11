@props(['isMatrix' => false])

<div {{ $attributes->merge([
    'class' => $isMatrix 
        ? 'overflow-x-auto rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-xs relative' 
        : 'overflow-x-auto rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-xs responsive-stack-table'
]) }}>
    <table class="w-full text-left text-sm text-gray-800 dark:text-gray-200 {{ $isMatrix ? 'whitespace-nowrap' : '' }}">
        {{ $slot }}
    </table>
</div>
