<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700 hover:border-gray-600 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 dark:bg-gray-900 dark:border-white/40 dark:hover:bg-gray-800 dark:hover:border-white/60']) }}>
    {{ $slot }}
</button>
