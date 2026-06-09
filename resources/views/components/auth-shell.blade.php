@props([
    'title' => 'Industria de Empaques',
    'heading' => 'Inicia sesión',
    'subtitle' => 'Ingresa tu información para continuar',
    'panelHeading' => 'Tus empaques en manos de expertos.',
])

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased">
    <main class="min-h-screen md:grid md:grid-cols-[450px_1fr]">
        <aside class="relative hidden min-h-screen flex-col overflow-hidden bg-[#073b5a] px-10 py-12 text-white md:flex md:px-16 md:py-20">
            <div class="relative z-10 mx-auto flex h-full w-full max-w-[280px] flex-col justify-center md:max-w-none">
                <img src="/img/logo_industrias.png" alt="Industria de Empaques" class="h-14 w-auto max-w-[260px] object-contain brightness-0 invert">

                <p class="mt-10 max-w-[260px] text-2xl font-bold leading-tight text-white/60 md:text-[26px]">
                    {{ $panelHeading }}
                </p>

                <p class="mt-8 max-w-[260px] text-sm font-semibold leading-relaxed text-white/35">
                    Sistema de gestión administrativa
                </p>
            </div>
        </aside>

        <section class="flex min-h-screen items-center justify-center px-6 py-10 md:py-12">
            <div class="w-full max-w-[330px]">
                <div class="mb-10 flex justify-center md:hidden">
                    <img src="/img/logo_industrias.png" alt="Industria de Empaques" class="h-14 w-auto max-w-[260px] object-contain">
                </div>

                <h1 class="text-[28px] font-bold leading-tight text-[#073b5a]">
                    {{ $heading }}
                </h1>
                <p class="mt-1 text-base font-medium text-gray-700">
                    {{ $subtitle }}
                </p>

                <div class="mt-4">
                    {{ $slot }}
                </div>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.passwordToggle)
                if (!target) return

                target.type = target.type === 'password' ? 'text' : 'password'
            })
        })
    </script>
</body>

</html>
