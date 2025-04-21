<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-color-800 border border-transparent1 rounded-md font-semibold text-xs text-white1 uppercase tracking-widest hover:bg-color-700 focus:bg-color-700 active:bg-color-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
