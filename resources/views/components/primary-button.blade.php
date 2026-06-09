<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 rounded-lg border border-transparent font-semibold text-sm text-white bg-gradient-to-r from-primary to-secondary shadow-md hover:from-secondary hover:to-primary focus:outline-none focus:ring-4 focus:ring-[#4F6D7A]/40 disabled:opacity-60 transition duration-300']) }}>
    {{ $slot }}
</button>
