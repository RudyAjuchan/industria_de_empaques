<template>
    <v-container>
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona Roles y Permisos</div>
                </v-col>
                <v-col cols="6" class="d-flex ga-2 align-center justify-end">
                    <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar..."
                        prepend-inner-icon="mdi-magnify" style="max-width: 280px" />
                    <v-menu>
                        <template #activator="{ props }">
                            <v-btn v-bind="props" variant="tonal" prepend-icon="mdi-export" color="teal">
                                Exportar
                            </v-btn>
                        </template>

                        <v-list density="compact">
                            <v-list-item prepend-icon="mdi-file-excel-outline" @click="exportExcel"
                                v-if="can('rol.reporte')">
                                <v-list-item-title>Excel</v-list-item-title>
                            </v-list-item>

                            <v-list-item prepend-icon="mdi-file-pdf-box" @click="exportPdf"
                                v-if="can('rol.reporte')">
                                <v-list-item-title>PDF</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <v-btn color="primary" prepend-icon="mdi-plus" @click="$router.push('/roles/create')"
                        variant="tonal" :loading="loading" v-if="can('rol.crear')">
                        Nuevo
                    </v-btn>
                </v-col>
            </v-row>
        </div>

        <v-data-table :headers="headers" :items="roles" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-green-darken-2' }" density="compact" :search="search" v-if="can('rol.ver')">
            <template v-slot:[`item.actions`]="{ item }">
                <v-row class="ga-2">
                    <v-btn icon @click="edit(item.id)" color="primary" variant="tonal" density="compact"
                        v-if="can('rol.editar')">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>

                    <v-btn icon @click="openPermissions(item)" color="orange" variant="tonal" density="compact"
                        v-if="can('rol.permisos')">
                        <v-icon>mdi-shield-key</v-icon>
                    </v-btn>
                </v-row>
            </template>
        </v-data-table>

        <!-- DIALOG -->
        <RolePermissions v-if="selectedRole" v-model="showPermissions" :role="selectedRole" />
    </v-container>
</template>

<script>
import axios from 'axios'
import RolePermissions from './RolesPermission.vue'

export default {
    components: { RolePermissions },

    data() {
        return {
            roles: [],
            loading: false,
            showPermissions: false,
            selectedRole: null,

            headers: [
                { title: 'Nombre', key: 'name' },
                { title: 'Acciones', key: 'actions', sortable: false }
            ],
            search: null,
        }
    },

    mounted() {
        this.fetchRoles()
    },

    methods: {
        fetchRoles() {
            this.loading = true
            axios.get('/roles')
                .then(res => this.roles = res.data)
                .finally(() => this.loading = false)
        },

        edit(id) {
            this.$router.push(`/roles/${id}/edit`)
        },

        openPermissions(role) {
            this.selectedRole = role
            this.showPermissions = true
        },

        exportExcel() {
            const params = new URLSearchParams({
                search: this.search
            })

            window.location.href = `/roles/export/excel?${params.toString()}`
        },

        exportPdf() {
            const params = new URLSearchParams({
                search: this.search
            })
            window.open(`/roles/export/pdf?${params.toString()}`, '_blank')
        },
    }
}
</script>
