<x-auth-shell
    title="Iniciar sesión - Industria de Empaques"
    heading="Inicia sesión"
    subtitle="Ingresa tu información para continuar"
>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-3">
        @csrf

        <div>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="correo electrónico"
                class="h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="contraseña"
                    class="h-10 w-full rounded border border-gray-300 px-3 pr-11 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
                >
                <button
                    type="button"
                    data-password-toggle="password"
                    class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-500 hover:text-[#073b5a]"
                    aria-label="Mostrar u ocultar contraseña"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button
            type="submit"
            class="h-10 w-full rounded bg-[#1479bf] text-sm font-bold text-white transition hover:bg-[#0f68a5] focus:outline-none focus:ring-2 focus:ring-[#1479bf]/40"
        >
            Comenzar
        </button>

        <div class="pt-7 text-center">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#0f6f94] underline">
                    Olvidé mi contraseña
                </a>
            @endif
        </div>
    </form>
</x-auth-shell>
