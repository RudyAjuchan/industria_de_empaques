@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf] disabled:bg-gray-100 disabled:text-gray-500']) }}>
