@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-emerald-600 dark:focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/20 dark:focus:ring-emerald-400/20 rounded-xl shadow-xs min-h-[44px] text-base sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out']) }}>
