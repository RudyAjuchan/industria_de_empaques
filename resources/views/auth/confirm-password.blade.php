<x-auth-shell
    title="Confirmar contraseña - Industria de Empaques"
    heading="Confirma tu contraseña"
    subtitle="Esta es un área segura del sistema"
>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-3">
        @csrf

        <div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Confirmar
        </x-primary-button>
    </form>
</x-auth-shell>
