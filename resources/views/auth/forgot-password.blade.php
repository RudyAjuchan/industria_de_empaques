<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">
    <style>
        .font-title { font-family: 'Bebas Neue', cursive; }
        .font-body { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
</head>

<body class="font-body min-h-screen bg-gray-100">

    <!-- CONTENEDOR -->
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas2.svg')] bg-none">

        <!-- CARD -->
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">

            <!-- TITULO -->
            <h1 class="text-3xl font-title text-[#043C5D] text-center mb-2">
                Recuperar contraseña
            </h1>

            <!-- LOGO -->
            <div class="flex justify-center mb-2">
                <img src="/img/logo_empaque.png" alt="Logo Empresa" class="h-24 md:h-28 object-contain transition-all">
            </div>

            <!-- TEXTO -->
            <p class="text-center text-gray-600 mb-6 text-sm">
                Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
            </p>

            <!-- MENSAJE DE ÉXITO -->
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <!-- EMAIL -->
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Correo electrónico</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required autofocus
                        placeholder="Ingresa tu correo"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#043C5D] focus:border-[#043C5D] focus:outline-none">

                    @error('email')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BOTÓN -->
                <button type="submit"
                    class="w-full py-2 rounded-lg transition-all duration-300 font-semibold shadow-md text-white bg-gradient-to-r from-[#043C5D] to-[#1c6a94] hover:from-[#1c6a94] hover:to-[#043C5D] focus:ring-4 focus:outline-none focus:ring-[#4F6D7A]/40">
                    Enviar enlace de recuperación
                </button>

                <!-- VOLVER -->
                <div class="text-center text-sm mt-3">
                    <a href="{{ route('login') }}" class="text-[#043C5D] hover:underline">
                        ← Volver al login
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>

</body>

</html>
