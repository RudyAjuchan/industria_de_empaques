<template>
    <div class="pa-10">
        <LoginWelcome />

        <v-card class="empty-dashboard-card elevation-1">
            <div class="empty-icon">
                <i class="mdi mdi-view-dashboard-outline"></i>
            </div>

            <div>
                <h2 class="mb-2">Dashboard sin asignar</h2>
                <p class="text-body-1 text-grey-darken-1 mb-0">
                    Tu usuario no tiene asignado un dashboard para visualizar. Solicita a un administrador el permiso
                    de dashboard general o dashboard corporativo.
                </p>
            </div>
        </v-card>
    </div>
</template>

<script>
import LoginWelcome from './LoginWelcome.vue'

export default {
    name: 'DashboardSinAsignar',
    components: {
        LoginWelcome,
    },
}
</script>

<style scoped>
.empty-dashboard-card {
    display: flex;
    gap: 18px;
    align-items: center;
    max-width: 760px;
    padding: 28px;
    border: 1px solid #e6e8eb;
    border-radius: 8px;
}

.empty-icon {
    display: grid;
    place-items: center;
    flex: 0 0 72px;
    width: 72px;
    height: 72px;
    border-radius: 8px;
    color: #005b83;
    background: #e8f4f8;
    font-size: 34px;
}

@media (max-width: 600px) {
    .empty-dashboard-card {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
