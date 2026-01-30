import { createRouter, createWebHashHistory } from "vue-router";

const InicioComponent = () => import('../components/Inicio.vue');
const UsuarioComponent = () => import('../components/Usuarios/Usuario.vue');
//COMPONENTES DE ROLES
const RolesComponent = () => import('../components/Roles/RolesIndex.vue');
const RolesCreateComponent = () => import('../components/Roles/RolesCreate.vue');
const RolesEditComponent = () => import('../components/Roles/RolesEdit.vue');

const TipoPapelComponent = () => import('../components/TipoPapel/TipoPapelIndex.vue');
const TipoAgarradorComponent = () => import('../components/Agarradores/AgarradoresIndex.vue');
const PaginaComponent = () => import('../components/Paginas/PaginaIndex.vue');
const BancoComponent = () => import('../components/Bancos/BancoIndex.vue');

/* COMPONENTES PARA CLIENTES */
const ClienteComponent = () => import('../components/Clientes/ClientesIndex.vue');
const ClienteCreateComponent = () => import('../components/Clientes/ClientesCreate.vue');
const ClienteEditComponent = () => import('../components/Clientes/ClientesEdit.vue');

/* COMPONENTES PARA PRODUCTOS */
const productosComponent = () => import('../components/Productos/ProductosIndex.vue');
const ProductosCreateComponent = () => import('../components/Productos/ProductosCreate.vue');
const ProductosEditComponent = () => import('../components/Productos/ProductosEdit.vue');

/* COMPONENTES PARA VENTAS */
const ventasComponent = () => import('../components/Ventas/VentasIndex.vue');
const ventasCreateComponent = () => import('../components/Ventas/VentasCreate.vue');
const ventasDetalleComponent = () => import('../components/Ventas/Detalle/VentasDetalle.vue');

/* COMPONENTES PARA VER LOS ESTADOS */
const trackingVentaComponent = () => import('../components/Ventas/Estado/TrackingVentas.vue');

const routes = [
    /* RUTAS DEL MENÚ */
    {
        path: '/',
        name: 'InicioVue',
        component: InicioComponent,
        meta: {
            permission: 'menu.inicio'
        }
    },
    {
        path: '/usuarios',
        name: 'UsuarioVue',
        component: UsuarioComponent,
        meta: {
            permission: 'menu.usuarios'
        }
    },
    {
        path: '/roles',
        name: 'roles.index',
        component: RolesComponent,
        meta: {
            permission: 'menu.permisos'
        }
    },
    {
        path: '/tipo_papel',
        name: 'tipopapel.index',
        component: TipoPapelComponent,
        meta: {
            permission: 'menu.tipo_papel'
        }
    },
    {
        path: '/agarrador',
        name: 'agarradores.index',
        component: TipoAgarradorComponent,
        meta: {
            permission: 'menu.agarrador'
        }
    },
    {
        path: '/paginas',
        name: 'pagina.index',
        component: PaginaComponent,
        meta: {
            permission: 'menu.pagina'
        }
    },
    {
        path: '/bancos',
        name: 'banco.index',
        component: BancoComponent,
        meta: {
            permission: 'menu.banco'
        }
    },
    {
        path: '/clientes',
        name: 'cliente.index',
        component: ClienteComponent,
        meta: {
            permission: 'menu.cliente'
        }
    },
    {
        path: '/productos',
        name: 'productos.index',
        component: productosComponent,
        meta: {
            permission: 'menu.producto'
        }
    },
    {
        path: '/ventas',
        name: 'ventas.index',
        component: ventasComponent,
        meta: {
            permission: 'menu.venta'
        }
    },
    
    /* RUTAS PARA ROLES */
    {
        path: '/roles/create',
        name: 'roles.create',
        component: RolesCreateComponent,
        meta: {
            permission: 'usuario.crear'
        }
    },
    {
        path: '/roles/:id/edit',
        name: 'roles.edit',
        component: RolesEditComponent,
        meta: {
            permission: 'usuario.editar'
        }
    },
    /* RUTAS PARA CLIENTES */
    {
        path: '/cliente/create',
        name: 'cliente.create',
        component: ClienteCreateComponent,
        meta: {
            permission: 'cliente.crear'
        }
    },
    {
        path: '/cliente/:id/edit',
        name: 'cliente.edit',
        component: ClienteEditComponent,
        meta: {
            permission: 'cliente.editar'
        }
    },
    /* RUTAS PARA PRODUCTOS */
    {
        path: '/producto/create',
        name: 'producto.create',
        component: ProductosCreateComponent,
        meta: {
            permission: 'producto.crear'
        }
    },
    {
        path: '/producto/:id/edit',
        name: 'producto.edit',
        component: ProductosEditComponent,
        meta: {
            permission: 'producto.editar'
        }
    },
    /* RUTAS PARA VENTAS */
    {
        path: '/venta/create',
        name: 'venta.create',
        component: ventasCreateComponent,
        meta: {
            permission: 'venta.crear'
        }
    },
    {
        path: '/venta/:id',
        name: 'venta.detalle',
        component: ventasDetalleComponent,
        meta: {
            permission: 'venta.ver'
        }
    },
    /*  RUTAS PARA VER LOS ESTADOS */
    {
        path: '/venta/:id/tracking',
        name: 'venta.tracking',
        component: trackingVentaComponent,
        meta: {
            permission: 'venta.ver'
        }
    },

];
const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes
});

router.beforeEach((to, from, next) => {
    const permission = to.meta.permission

    if (!permission) {
        return next()
    }

    const permissions = window.USER_PERMISSIONS || []

    if (permissions.includes(permission)) {
        return next()
    }

    return next('/')
})
export default router;