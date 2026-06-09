<x-auth-shell
    title="Registro - Industria de Empaques"
    heading="Crear usuario"
    subtitle="Ingresa la información para continuar"
>
    <form method="POST" action="{{ route('register') }}" class="space-y-3">
        @csrf

        <div>
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="nombre" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="correo electrónico" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="confirmar contraseña" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Registrar
        </x-primary-button>

        <div class="pt-7 text-center">
            <a class="text-sm font-semibold text-[#0f6f94] underline" href="{{ route('login') }}">
                Ya tengo usuario
            </a>
        </div>
    </form>
</x-auth-shell>
