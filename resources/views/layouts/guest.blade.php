<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Industria de Empaques</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-body text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gray-100 bg-cover bg-center md:bg-[url('/img/Curvas2.svg')]">
            <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">
                <h1 class="text-4xl font-title text-primary text-center mb-2">
                    Bienvenido
                </h1>

                <div class="flex justify-center mb-1">
                    <a href="/">
                        <img src="/img/logo_empaque.png" alt="Logo Empresa" class="h-24 md:h-28 object-contain transition-all">
                    </a>
                </div>

                <p class="text-center text-gray-600 mb-6 text-sm">
                    <span class="text-xl">INDUSTRIA DE EMPAQUES S.A.</span><br>
                    <span class="text-xs">Sistema de gestión de ventas</span>
                </p>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
