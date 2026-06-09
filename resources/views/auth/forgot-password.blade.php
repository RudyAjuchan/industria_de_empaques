<x-auth-shell
    title="Recuperar contraseña - Industria de Empaques"
    heading="Recuperar contraseña"
    subtitle="Ingresa tu correo para continuar"
>
    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
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

        <button
            type="submit"
            class="h-10 w-full rounded bg-[#1479bf] text-sm font-bold text-white transition hover:bg-[#0f68a5] focus:outline-none focus:ring-2 focus:ring-[#1479bf]/40"
        >
            Enviar enlace
        </button>

        <div class="pt-7 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#0f6f94] underline">
                Volver al login
            </a>
        </div>
    </form>
</x-auth-shell>
