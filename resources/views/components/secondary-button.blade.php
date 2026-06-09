<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-primary shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-[#4F6D7A]/30 disabled:opacity-60 transition duration-300']) }}>
    {{ $slot }}
</button>
