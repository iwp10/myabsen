<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-sm text-gray-800 dark:text-gray-200 shadow-xs hover:bg-gray-50 dark:hover:bg-gray-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
