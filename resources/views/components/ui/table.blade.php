@props(['isMatrix' => false])

<div {{ $attributes->merge([
    'class' => $isMatrix 
        ? 'overflow-x-auto rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 shadow-xs relative' 
        : 'overflow-x-auto md:rounded-xl md:border-2 md:border-slate-300 md:dark:border-slate-600 md:bg-white md:dark:bg-slate-900 md:shadow-xs responsive-stack-table'
]) }}>
    <table class="w-full text-left text-sm text-slate-800 dark:text-slate-100 {{ $isMatrix ? 'whitespace-nowrap' : '' }}">
        {{ $slot }}
    </table>
</div>
