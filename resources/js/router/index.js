import { createRouter, createWebHashHistory } from "vue-router";

const HomeComponent = () => import('../components/Home.vue');
const routes = [
    { 
        path: '/categorias', 
        name: 'CategoriasVue', 
        component: HomeComponent 
    },
];
const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes
});
export default router;