<x-auth-shell
    title="Verificar correo - Industria de Empaques"
    heading="Verifica tu correo"
    subtitle="Enviamos un enlace de verificación a tu correo"
>
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded bg-green-50 px-3 py-2 text-sm font-medium text-green-700">
            Se envió un nuevo enlace de verificación.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full">
                Reenviar correo
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-center text-sm font-semibold text-[#0f6f94] underline">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-auth-shell>
