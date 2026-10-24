@props([
    'href',
    'label' => 'Kembali',
])

<a href="{{ $href }}"
   {{ $attributes->merge([
       'class' => 'inline-flex items-center justify-center p-2 rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/50 min-h-[44px] min-w-[44px] shadow-xs transition shrink-0'
   ]) }}
   aria-label="{{ $label }}"
   title="{{ $label }}">
    <x-ui.icon name="arrow-left" size="md" />
</a>
