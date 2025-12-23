<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Sidebar</title>
    <link rel="stylesheet" href="/css/sidebar.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/app.js')
</head>

<body>
    <div class="app-layout">
        <div class="backdrop" id="backdrop"></div>

        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo" aria-hidden="true"><span>R</span></div>

                <div class="brand-text">
                    <div class="t1">{{ auth()->user()->name }}</div>
                    <div class="t2">{{ auth()->user()->getRoleNames()->first() }}</div>
                </div>

                <!-- botón colapsar (desktop) / cerrar (mobile) -->
                <button class="toggle" id="btnToggle" aria-label="Colapsar/expandir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>
            </div>

            <div class="divider"></div>

            <nav class="nav" id="nav">
                <a href="#" data-route="/" data-title="Dashboard">
                    <div class="ico" aria-hidden="true">
                        <i class="mdi mdi-view-dashboard"></i>
                    </div>
                    <div class="label">Dashboard</div>
                </a>

                <a href="#" data-route="/usuarios" data-title="Usuarios">
                    <div class="ico" aria-hidden="true">
                        <i class="mdi mdi-account-outline"></i>
                    </div>
                    <div class="label">Usuarios</div>
                </a>

                <a href="#" data-title="Permisos">
                    <div class="ico" aria-hidden="true">
                        <i class="mdi mdi-star-outline"></i>
                    </div>
                    <div class="label">Roles &amp; Permisos</div>
                </a>

                <a href="#" data-title="Reportes">
                    <div class="ico" aria-hidden="true">
                        <i class="mdi mdi-file-document-outline"></i>
                    </div>
                    <div class="label">Reportes</div>
                </a>

                <a href="#" data-title="Configuración">
                    <div class="ico" aria-hidden="true">
                        <i class="mdi mdi-cog-outline"></i>
                    </div>
                    <div class="label">Configuración</div>
                </a>
            </nav>

            <div class="footer">
                <div class="divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout text-white" id="btnLogout" type="submit">
                        <div class="ico">
                            <i class="mdi mdi-logout"></i>
                        </div>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="app-main" id="appMain">
            <header class="main-header">
                <div class="mobile-topbar">
                    <button class="hamburger" id="btnOpen" aria-label="Abrir menú">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <h1 id="pageTitle">Dashboard</h1>
            </header>

            <section class="main-content">
                <div id="app">
                    <router-view></router-view>
                </div>
            </section>
        </main>
    </div>

    <script>
        window.LOGIN_SUCCESS = @json(session('login_success', false));
        console.log(window.LOGIN_SUCCESS);
    </script>

</body>

</html>
