import { createRouter, createWebHashHistory } from "vue-router";

const InicioComponent = () => import('../components/Inicio.vue');
const UsuarioComponent = () => import('../components/Usuarios/Usuario.vue');
const routes = [
    { 
        path: '/', 
        name: 'InicioVue', 
        component: InicioComponent 
    },
    { 
        path: '/usuarios', 
        name: 'UsuarioVue', 
        component: UsuarioComponent 
    },
];
const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes
});
export default router;