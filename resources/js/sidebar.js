const sidebar = document.getElementById('sidebar');
const nav = document.getElementById('nav');
const btnToggle = document.getElementById('btnToggle');
const btnOpen = document.getElementById('btnOpen');
const backdrop = document.getElementById('backdrop');
const btnLogout = document.getElementById('btnLogout');
import router from './router/index'

const logoutForm = btnLogout?.closest('form');

if (logoutForm) {
    logoutForm.addEventListener('submit', () => {
        window.IS_LOGGING_OUT = true;
        btnLogout.disabled = true;
        btnLogout.setAttribute('aria-busy', 'true');
        btnLogout.querySelector('span').textContent = 'Saliendo...';
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const nav = document.getElementById('nav')
    const pageTitle = document.getElementById('pageTitle')

    if (!nav) return

    const setActiveFromRoute = (route) => {
        const links = nav.querySelectorAll('a[data-route]')
        let activeLink = null
        const navRoute = route.meta?.navRoute || route.path

        links.forEach((a) => {
            const isActive = a.dataset.route === navRoute
            a.classList.toggle('is-active', isActive)
            if (isActive) activeLink = a
        })

        if (pageTitle) {
            pageTitle.textContent = route.meta?.title || activeLink?.dataset.title || 'Dashboard'
        }
    }

    nav.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-route]')
        if (!link) return

        e.preventDefault()
        router.push(link.dataset.route)
    })

    await router.isReady()

    setActiveFromRoute(router.currentRoute.value)

    router.afterEach((to) => {
        setActiveFromRoute(to)
    })
})

const isMobile = () => window.matchMedia('(max-width: 820px)').matches;
const pageTitle = document.getElementById('pageTitle');

btnToggle.addEventListener('click', () => {
    if (isMobile()) {
        sidebar.classList.remove('is-drawer-open');
        backdrop.classList.remove('show');
    } else {
        sidebar.classList.toggle('is-rail');
    }
});

nav.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a) return;
    e.preventDefault();

    nav.querySelectorAll('a').forEach(x => x.classList.remove('is-active'));
    a.classList.add('is-active');

    if (isMobile()) {
        sidebar.classList.remove('is-drawer-open');
        backdrop.classList.remove('show');
    }
});

if (btnOpen) {
    btnOpen.addEventListener('click', () => {
        sidebar.classList.add('is-drawer-open');
        backdrop.classList.add('show');
    });
}

backdrop.addEventListener('click', () => {
    sidebar.classList.remove('is-drawer-open');
    backdrop.classList.remove('show');
});


if (nav && pageTitle) {
    nav.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-title]');
        if (!link) return;

        // visual active
        nav.querySelectorAll('a').forEach(a => a.classList.remove('is-active'));
        link.classList.add('is-active');

        // header title
        pageTitle.textContent = link.dataset.title;

        // mobile: cerrar drawer
        if (window.matchMedia('(max-width: 820px)').matches) {
            document.getElementById('sidebar')?.classList.remove('is-drawer-open');
            document.getElementById('backdrop')?.classList.remove('show');
        }
    });
}


// Sync on resize
const sync = () => {
    if (isMobile()) {
        // en móvil, no rail
        sidebar.classList.remove('is-rail');
        sidebar.classList.remove('is-drawer-open');
        backdrop.classList.remove('show');
    } else {
        // desktop: asegurar backdrop apagado
        backdrop.classList.remove('show');
        sidebar.classList.remove('is-drawer-open');
    }
};
window.addEventListener('resize', sync);
sync();
