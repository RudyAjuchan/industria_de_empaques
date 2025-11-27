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
        class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas.svg')] bg-none">
        <!-- CARD LOGIN -->
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">

            <!-- TITULO -->
            <h1 class="text-4xl font-title text-primary text-center mb-2">
                Bienvenido
            </h1>
            <!-- LOGO -->
            <div class="flex justify-center mb-1">
                <img src="/img/jeple_logo.png" alt="Logo Empresa" class="h-24 md:h-28 object-contain transition-all">
            </div>
            <p class="text-center text-gray-600 mb-6 text-sm">
                Industria de empaques S.A.<br>
                <span class="text-xs">Sistema de gestión de ventas</span>
            </p>
            
            <!-- FORMULARIO -->
            <form class="space-y-5">
                <!-- USUARIO -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Usuario</label>
                    <input type="text" placeholder="Ingrese su usuario"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none">
                </div>
                <!-- CONTRASEÑA -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Contraseña</label>
                    <input type="password" placeholder="Ingrese su contraseña"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                <!-- OPCIONES -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="accent-primary">
                        Recordar sesión
                    </label>

                    <a href="#" class="text-primary hover:underline">
                        ¿Olvidé mi contraseña?
                    </a>
                </div>

                <!-- BOTÓN -->
                <button type="submit"
                    class="w-full bg-primary hover:bg-secondary text-gray-600 py-2 rounded-lg transition-all font-semibold shadow-md bg-gradient-to-r from-teal-200 to-lime-200 hover:bg-gradient-to-l hover:from-teal-200 hover:to-lime-200 focus:ring-4 focus:outline-none focus:ring-lime-200">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>
</body>


</html>
