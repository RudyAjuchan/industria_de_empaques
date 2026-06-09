<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-10 items-center justify-center rounded border border-gray-300 bg-white px-4 text-sm font-bold text-[#073b5a] transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1479bf]/30 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
