<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cambiar Contraseña - Industria de Empaques</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-title { font-family: 'Bebas Neue', cursive; }
        .font-body { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>

<body class="font-body min-h-screen bg-gray-100">

    <div class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas2.svg')] bg-none">
        
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">

            <h1 class="text-3xl font-title text-[#043C5D] text-center mb-2">
                Seguridad de la Cuenta
            </h1>
            
            <div class="flex justify-center mb-2">
                <img src="/img/logo_empaque.png" alt="Logo Empresa" class="h-24 md:h-28 object-contain transition-all">
            </div>

            <p class="text-center text-gray-600 mb-6 text-sm">
                Por seguridad, debes actualizar tu contraseña temporal antes de continuar al sistema de ventas.
            </p>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-xs">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                    <input type="password" name="current_password" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#043C5D] focus:border-[#043C5D] focus:outline-none transition-all" 
                        placeholder="••••••••">
                </div>

                <hr class="my-4 border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#043C5D] focus:border-[#043C5D] focus:outline-none transition-all"
                        placeholder="Mínimo 8 caracteres">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#043C5D] focus:border-[#043C5D] focus:outline-none transition-all"
                        placeholder="Repita la nueva contraseña">
                </div>

                <button type="submit"
                    class="w-full mt-6 py-3 rounded-lg transition-all duration-300 font-bold shadow-md text-white bg-gradient-to-r from-[#043C5D] to-[#1c6a94] hover:from-[#1c6a94] hover:to-[#043C5D] focus:ring-4 focus:outline-none focus:ring-[#4F6D7A]/40 uppercase text-sm">
                    Actualizar y Entrar
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-[#043C5D] transition-colors">
                    Cancelar y volver al login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
