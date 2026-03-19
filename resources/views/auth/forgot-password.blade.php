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
</head>

<body class="font-body min-h-screen bg-gray-100">

    <!-- CONTENEDOR -->
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center md:bg-[url('/img/Curvas.svg')] bg-none">

        <!-- CARD -->
        <div class="w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-8">

            <!-- TITULO -->
            <h1 class="text-3xl font-title text-primary text-center mb-2">
                Recuperar contraseña
            </h1>

            <!-- LOGO -->
            <div class="flex justify-center mb-2">
                <img src="/img/jeple_logo.png" class="h-20 object-contain">
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
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:outline-none">

                    @error('email')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- BOTÓN -->
                <button type="submit"
                    class="w-full bg-primary hover:bg-secondary text-gray-600 py-2 rounded-lg transition-all font-semibold shadow-md bg-gradient-to-r from-teal-200 to-lime-200 hover:bg-gradient-to-l hover:from-teal-200 hover:to-lime-200 focus:ring-4 focus:outline-none focus:ring-lime-200">
                    Enviar enlace de recuperación
                </button>

                <!-- VOLVER -->
                <div class="text-center text-sm mt-3">
                    <a href="{{ route('login') }}" class="text-primary hover:underline">
                        ← Volver al login
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>

</body>

</html>