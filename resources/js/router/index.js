import { createRouter, createWebHashHistory } from "vue-router";

const InicioComponent = () => import('../components/Inicio.vue');
const UsuarioComponent = () => import('../components/Usuarios/Usuario.vue');
//COMPONENTES DE ROLES
const RolesComponent = () => import('../components/Roles/RolesIndex.vue');
const RolesCreateComponent = () => import('../components/Roles/RolesCreate.vue');
const RolesEditComponent = () => import('../components/Roles/RolesEdit.vue');


const routes = [
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
    }

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