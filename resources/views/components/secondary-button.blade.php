<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2.5 bg-white dark:bg-slate-800 border-2 border-slate-400 dark:border-slate-500 rounded-xl font-semibold text-sm text-slate-900 dark:text-slate-100 shadow-xs hover:bg-slate-100 dark:hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
