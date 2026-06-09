<x-auth-shell
    title="Restablecer contraseña - Industria de Empaques"
    heading="Restablecer contraseña"
    subtitle="Ingresa tu nueva contraseña"
>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-3">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
                placeholder="correo electrónico"
                class="h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="nueva contraseña"
                class="h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
            >
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="confirmar contraseña"
                class="h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
            >
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button
            type="submit"
            class="h-10 w-full rounded bg-[#1479bf] text-sm font-bold text-white transition hover:bg-[#0f68a5] focus:outline-none focus:ring-2 focus:ring-[#1479bf]/40"
        >
            Restablecer
        </button>

        <div class="pt-7 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#0f6f94] underline">
                Volver al login
            </a>
        </div>
    </form>
</x-auth-shell>
