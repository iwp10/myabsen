<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[44px] min-w-[44px] px-4 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-sm rounded-xl shadow-xs transition duration-150 ease-in-out focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-red-500/50 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
