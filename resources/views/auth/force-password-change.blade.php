<x-auth-shell
    title="Cambiar contraseña - Industria de Empaques"
    heading="Actualiza tu contraseña"
    subtitle="Cambia tu contraseña temporal para continuar"
>
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 px-3 py-2 text-sm text-red-600">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.force.update') }}" class="space-y-3">
        @csrf

        <div>
            <input
                id="current_password"
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
                placeholder="contraseña actual"
                class="h-10 w-full rounded border border-gray-300 px-3 text-sm font-semibold text-gray-700 placeholder:text-gray-500 focus:border-[#1479bf] focus:ring-1 focus:ring-[#1479bf]"
            >
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
        </div>

        <button
            type="submit"
            class="h-10 w-full rounded bg-[#1479bf] text-sm font-bold text-white transition hover:bg-[#0f68a5] focus:outline-none focus:ring-2 focus:ring-[#1479bf]/40"
        >
            Actualizar y entrar
        </button>

        <div class="pt-7 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#0f6f94] underline">
                Cancelar y volver al login
            </a>
        </div>
    </form>
</x-auth-shell>
