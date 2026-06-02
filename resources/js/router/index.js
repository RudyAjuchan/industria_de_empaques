import { createRouter, createWebHashHistory } from "vue-router";

const InicioComponent = () => import('../components/Inicio.vue');
const DashboardCorporativoComponent = () => import('../components/DashboardCorporativo.vue');
const UsuarioComponent = () => import('../components/Usuarios/Usuario.vue');
//COMPONENTES DE ROLES
const RolesComponent = () => import('../components/Roles/RolesIndex.vue');
const RolesCreateComponent = () => import('../components/Roles/RolesCreate.vue');
const RolesEditComponent = () => import('../components/Roles/RolesEdit.vue');
const PermisosComponent = () => import('../components/Permisos/PermisosIndex.vue');

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

/* COMPONENTES PARA PRODUCCIÓN */
const operativaProduccionComponent = () => import('../components/Produccion/OperativaProduccion.vue');

/* COMPONENTES PARA PRODUCCIÓN ACTIVA */
const produccionActivaComponent = () => import('../components/Produccion/ProduccionActiva.vue');

/* COMPONENTES PARA COTIZACIONES DEL ECOMMERCE */
const cotizacionesComponent = () => import('../components/Ecommerce/EcommerceIndex.vue');
const editarCotizacionComponent = () => import('../components/Ventas/VentasForm.vue');

/* RUTAS PARA PROMOCIONES */
const promocionesComponent = () => import('../components/Promociones/PromocionesIndex.vue');

/* RUTAS PARA VENTAS CONTABILIDAD */
const ventasContabilidadComponent = () => import('../components/Contabilidad/ContabilidadIndex.vue');

/* RUTAS PARA PAGOS */
const pagosComponent = () => import('../components/Pagos/PagosIndex.vue');

/* RUTAS PARA BANNERS */
const bannersComponent = () => import('../components/Banners/BannersIndex.vue');

const routes = [
    /* RUTAS DEL MENÚ */
    {
        path: '/',
        name: 'InicioVue',
        component: InicioComponent,
        meta: {
            permission: 'dashboard.general.ver',
            title: 'Dashboard',
            navRoute: '/'
        }
    },
    {
        path: '/dashboard-corporativo',
        name: 'DashboardCorporativo',
        component: DashboardCorporativoComponent,
        meta: {
            permission: 'dashboard.corporativo.ver',
            title: 'Dashboard Corporativo',
            navRoute: '/dashboard-corporativo'
        }
    },
    {
        path: '/usuarios',
        name: 'UsuarioVue',
        component: UsuarioComponent,
        meta: {
            permission: 'menu.usuarios',
            title: 'Usuarios',
            navRoute: '/usuarios'
        }
    },
    {
        path: '/roles',
        name: 'roles.index',
        component: RolesComponent,
        meta: {
            permission: 'menu.permisos',
            title: 'Roles',
            navRoute: '/roles'
        }
    },
    {
        path: '/permisos',
        name: 'permisos.index',
        component: PermisosComponent,
        meta: {
            permission: 'rol.permisos',
            title: 'Permisos',
            navRoute: '/permisos'
        }
    },
    {
        path: '/tipo_papel',
        name: 'tipopapel.index',
        component: TipoPapelComponent,
        meta: {
            permission: 'menu.tipo_papel',
            title: 'Tipos de Papeles',
            navRoute: '/tipo_papel'
        }
    },
    {
        path: '/agarrador',
        name: 'agarradores.index',
        component: TipoAgarradorComponent,
        meta: {
            permission: 'menu.agarrador',
            title: 'Tipos de agarradores',
            navRoute: '/agarrador'
        }
    },
    {
        path: '/paginas',
        name: 'pagina.index',
        component: PaginaComponent,
        meta: {
            permission: 'menu.pagina',
            title: 'Páginas',
            navRoute: '/paginas'
        }
    },
    {
        path: '/bancos',
        name: 'banco.index',
        component: BancoComponent,
        meta: {
            permission: 'menu.banco',
            title: 'Bancos',
            navRoute: '/bancos'
        }
    },
    {
        path: '/clientes',
        name: 'cliente.index',
        component: ClienteComponent,
        meta: {
            permission: 'menu.cliente',
            title: 'Clientes',
            navRoute: '/clientes'
        }
    },
    {
        path: '/productos',
        name: 'productos.index',
        component: productosComponent,
        meta: {
            permission: 'menu.producto',
            title: 'Productos',
            navRoute: '/productos'
        }
    },
    {
        path: '/ventas',
        name: 'ventas.index',
        component: ventasComponent,
        meta: {
            permission: 'menu.venta',
            title: 'Ventas',
            navRoute: '/ventas'
        }
    },

    /* RUTAS PARA ROLES */
    {
        path: '/roles/create',
        name: 'roles.create',
        component: RolesCreateComponent,
        meta: {
            permission: 'rol.crear',
            title: 'Nuevo rol',
            navRoute: '/roles'
        }
    },
    {
        path: '/roles/:id/edit',
        name: 'roles.edit',
        component: RolesEditComponent,
        meta: {
            permission: 'rol.editar',
            title: 'Editar rol',
            navRoute: '/roles'
        }
    },
    /* RUTAS PARA CLIENTES */
    {
        path: '/cliente/create',
        name: 'cliente.create',
        component: ClienteCreateComponent,
        meta: {
            permission: 'cliente.crear',
            title: 'Nuevo cliente',
            navRoute: '/clientes'
        }
    },
    {
        path: '/cliente/:id/edit',
        name: 'cliente.edit',
        component: ClienteEditComponent,
        meta: {
            permission: 'cliente.editar',
            title: 'Editar cliente',
            navRoute: '/clientes'
        }
    },
    /* RUTAS PARA PRODUCTOS */
    {
        path: '/producto/create',
        name: 'producto.create',
        component: ProductosCreateComponent,
        meta: {
            permission: 'producto.crear',
            title: 'Nuevo producto',
            navRoute: '/productos'
        }
    },
    {
        path: '/producto/:id/edit',
        name: 'producto.edit',
        component: ProductosEditComponent,
        meta: {
            permission: 'producto.editar',
            title: 'Editar producto',
            navRoute: '/productos'
        }
    },
    /* RUTAS PARA VENTAS */
    {
        path: '/venta/create',
        name: 'venta.create',
        component: ventasCreateComponent,
        meta: {
            permission: 'venta.crear',
            title: 'Nueva venta',
            navRoute: '/ventas'
        }
    },
    {
        path: '/venta/:id',
        name: 'venta.detalle',
        component: ventasDetalleComponent,
        meta: {
            permission: ['venta.ver', 'ecommerce.ver'],
            title: 'Detalle de venta',
            navRoute: '/ventas'
        }
    },
    /*  RUTAS PARA VER LOS ESTADOS */
    {
        path: '/venta/:id/tracking',
        name: 'venta.tracking',
        component: trackingVentaComponent,
        meta: {
            permission: ['venta.ver', 'produccion.activa'],
            title: 'Tracking de venta',
            navRoute: '/ventas'
        }
    },
    /* RUTAS PARA LA PRODUCCIÓN (OPERARIOS) */
    {
        path: '/produccion',
        name: 'produccion.operativa',
        component: operativaProduccionComponent,
        meta: { 
            permission: 'produccion.ver',
            title: 'Producción',
            navRoute: '/produccion'
        }
    },
    /* RUTAS PARA VER LOS PRODUCTOS ACTIVOS */
    {
        path: '/produccion-activa',
        name: 'produccion.activa',
        component: produccionActivaComponent,
        meta: { 
            permission: 'produccion.activa',
            title: 'Productos Activos',
            navRoute: '/produccion-activa'
        }
    },
    /* RUTAS PARA VER LAS COTIZACIONES */
    {
        path: '/ecommerce',
        name: 'ecommerce.index',
        component: cotizacionesComponent,
        meta: { 
            permission: 'ecommerce.ver',
            title: 'Cotizaciones ecommerce',
            navRoute: '/ecommerce'
        }
    },
    {
        path: '/ecommerce/editar/:id',
        name: 'ecommerce.editar',
        component: editarCotizacionComponent,
        meta: { 
            permission: 'ecommerce.editar',
            title: 'Editar cotización',
            navRoute: '/ecommerce'
        }
    },
    /* RUTAS PARA PROMOCIONES */
    {
        path: '/promociones',
        name: 'promociones.index',
        component: promocionesComponent,
        meta: { 
            permission: 'promocion.ver',
            title: 'Promociones',
            navRoute: '/promociones'
        }
    },
    {
        path: '/ventas_contabilidad',
        name: 'ContabilidadVue',
        component: ventasContabilidadComponent,
        meta: { 
            permission: 'menu.contabilidad',
            title: 'Ventas Contabilidad',
            navRoute: '/ventas_contabilidad'
        }
    },
    /* PARA PAGOS */
    {
        path: '/pagos',
        name: 'pagos.index',
        component: pagosComponent,
        meta: {
            permission: 'menu.pagos',
            title: 'Pagos',
            navRoute: '/pagos'
        }
    },
    /* PARA BANNERS */
    {
        path: '/banners',
        name: 'banner.index',
        component: bannersComponent,
        meta: {
            permission: 'menu.banner',
            title: 'Banners',
            navRoute: '/banners'
        }
    },
];
const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes
});

router.beforeEach((to, from, next) => {
    const metaPermission = to.meta.permission

    if (!metaPermission) {
        return next()
    }

    const userPermissions = window.USER_PERMISSIONS || []

    // Convertimos a arreglo si es un string para manejar todo igual
    const requiredPermissions = Array.isArray(metaPermission)
        ? metaPermission
        : [metaPermission]

    // Lógica: ¿El usuario tiene AL MENOS UNO de los permisos requeridos?
    const hasPermission = requiredPermissions.some(p => userPermissions.includes(p))

    if (hasPermission) {
        return next()
    }

    // Si no tiene permiso, lo mandamos al inicio
    return next('/')
})
export default router;
