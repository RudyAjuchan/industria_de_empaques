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
                @can('menu.inicio')
                    <a href="#" data-route="/" data-title="Dashboard">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-view-dashboard"></i>
                        </div>
                        <div class="label">Dashboard</div>
                    </a>
                @endcan

                @can('menu.usuarios')
                    <a href="#" data-route="/usuarios" data-title="Usuarios">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-account-outline"></i>
                        </div>
                        <div class="label">Usuarios</div>
                    </a>
                @endcan

                @can('menu.permisos')
                    <a href="#" data-route="/roles" data-title="Roles">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-star-outline"></i>
                        </div>
                        <div class="label">Roles &amp; Permisos</div>
                    </a>
                @endcan

                @can('menu.tipo_papel')
                    <a href="#" data-route="/tipo_papel" data-title="Tipos de Papeles">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-note-outline"></i>
                        </div>
                        <div class="label">Tipos de papel</div>
                    </a>
                @endcan

                @can('menu.agarrador')
                    <a href="#" data-route="/agarrador" data-title="Tipos de agarradores">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-shopping-outline"></i>
                        </div>
                        <div class="label">Tipos de agarrador</div>
                    </a>
                @endcan

                @can('menu.pagina')
                    <a href="#" data-route="/paginas" data-title="Páginas">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-file-outline"></i>
                        </div>
                        <div class="label">Páginas</div>
                    </a>
                @endcan

                @can('menu.banco')
                    <a href="#" data-route="/bancos" data-title="Bancos">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-bank-outline"></i>
                        </div>
                        <div class="label">Bancos</div>
                    </a>
                @endcan
                @can('menu.cliente')
                    <a href="#" data-route="/clientes" data-title="Clientes">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-account"></i>
                        </div>
                        <div class="label">Clientes</div>
                    </a>
                @endcan
                @can('menu.producto')
                    <a href="#" data-route="/productos" data-title="Productos">
                        <div class="ico" aria-hidden="true">
                            <i class="mdi mdi-cube-outline"></i>
                        </div>
                        <div class="label">Productos</div>
                    </a>
                @endcan
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
        window.USER_PERMISSIONS = @json(auth()->user()->getAllPermissions()->pluck('name'));
    </script>

</body>

</html>
