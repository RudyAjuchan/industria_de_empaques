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
        .font-body { font-family: 'IBM+Plex+Sans', sans-serif; }
    </style>
</head>

<body class="font-body min-h-screen bg-gray-100">

    <div class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas.svg')] bg-none">
        
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8 border-t-4 border-teal-400">

            <h1 class="text-3xl font-title text-gray-800 text-center mb-2">
                Seguridad de la Cuenta
            </h1>
            
            <div class="flex justify-center mb-4">
                <div class="bg-teal-50 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
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
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-400 focus:outline-none transition-all" 
                        placeholder="••••••••">
                </div>

                <hr class="my-4 border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-400 focus:outline-none transition-all"
                        placeholder="Mínimo 8 caracteres">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-400 focus:outline-none transition-all"
                        placeholder="Repita la nueva contraseña">
                </div>

                <button type="submit"
                    class="w-full mt-6 bg-gradient-to-r from-teal-200 to-lime-200 hover:bg-gradient-to-l text-gray-700 py-3 rounded-lg transition-all font-bold shadow-md focus:ring-4 focus:outline-none focus:ring-lime-100 uppercase tracking-wider text-sm">
                    Actualizar y Entrar
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-primary transition-colors">
                    Cancelar y volver al login
                </a>
            </div>
        </div>
    </div>
</body>
</html>