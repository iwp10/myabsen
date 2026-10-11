@props([
    'name' => null,
    'id' => null,
    'wrapperClass' => '',
])

<div class="relative w-full min-w-0 {{ $wrapperClass }}">
    <select
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full min-w-0 appearance-none pl-3 pr-10 min-h-[44px] text-base sm:text-xs rounded-lg font-medium truncate border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-50 dark:[color-scheme:dark] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 focus:outline-none transition shadow-2xs'
        ]) }}>
        {{ $slot }}
    </select>
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500 dark:text-slate-400">
        <x-ui.icon name="chevron-down" size="sm" />
    </div>
</div>
