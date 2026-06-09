<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center rounded bg-[#1479bf] px-4 text-sm font-bold text-white transition hover:bg-[#0f68a5] focus:outline-none focus:ring-2 focus:ring-[#1479bf]/40 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
