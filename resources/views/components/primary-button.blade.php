<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white dark:bg-emerald-400 dark:hover:bg-emerald-300 dark:text-slate-950 font-semibold text-sm rounded-xl shadow-xs transition duration-150 ease-in-out focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
