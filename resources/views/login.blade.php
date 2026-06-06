<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Industria de Empaques</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">
</head>

<body class="font-body min-h-screen bg-gray-100">

    <!-- CONTENEDOR GENERAL -->
    <div
        class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas2.svg')] bg-none">
        <!-- CARD LOGIN -->
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">

            <!-- TITULO -->
            <h1 class="text-4xl font-title text-primary text-center mb-2">
                Bienvenido
            </h1>
            <!-- LOGO -->
            <div class="flex justify-center mb-1">
                <img src="/img/logo_empaque.png" alt="Logo Empresa" class="h-24 md:h-28 object-contain transition-all">
            </div>
            <p class="text-center text-gray-600 mb-6 text-sm">
                <span class="text-xl">INDUSTRIA DE EMPAQUES S.A.</span><br>
                <span class="text-xs">Sistema de gestión de ventas</span>
            </p>

            <!-- FORMULARIO -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <!-- USUARIO -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Usuario</label>
                    <input placeholder="Ingrese su usuario"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none"
                        id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <!-- CONTRASEÑA -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Contraseña</label>
                    <input placeholder="Ingrese su contraseña"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none"
                        id="password" type="password" name="password" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- OPCIONES -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2" for="remember_me">
                        <input id="remember_me" type="checkbox" class="accent-primary" name="remember">
                        Recordar sesión
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-primary hover:underline">
                            ¿Olvidé mi contraseña?
                        </a>
                    @endif
                </div>

                <!-- BOTÓN -->
                <button type="submit"
                    class="w-full py-2 rounded-lg transition-all duration-300 font-semibold shadow-md text-white bg-gradient-to-r from-[#043C5D] to-[#1c6a94]
                    hover:from-[#1c6a94] hover:to-[#043C5D] focus:ring-4 focus:outline-none focus:ring-[#4F6D7A]/40">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>

</body>


</html>
