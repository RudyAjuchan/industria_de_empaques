<template>
    <v-container fluid class="py-6">
        <v-row class="mb-4">
            <v-col cols="12" md="4">
                <div class="text-body-2 text-medium-emphasis">
                    Selecciona un rol para administrar sus permisos
                </div>
            </v-col>
            <v-col cols="12" md="8" class="d-flex justify-end">
                <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar rol..."
                    prepend-inner-icon="mdi-magnify" style="max-width: 320px" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12" md="4">
                <v-card variant="flat" class="pa-2">
                    <v-list density="compact" nav>
                        <v-list-item v-for="role in filteredRoles" :key="role.id" :active="selectedRole?.id === role.id"
                            color="primary" rounded="lg" @click="selectRole(role)">
                            <template #prepend>
                                <v-icon>mdi-account-key-outline</v-icon>
                            </template>
                            <v-list-item-title>{{ role.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card rounded="lg">
                    <v-card-title class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-subtitle-1 font-weight-bold">
                                {{ selectedRole ? selectedRole.name : 'Permisos' }}
                            </div>
                            <div class="text-body-2 text-medium-emphasis">
                                {{ selectedRole ? 'Permisos asignados al rol seleccionado' : 'Selecciona un rol para comenzar' }}
                            </div>
                        </div>

                        <v-btn color="success" variant="tonal" :disabled="!selectedRole" :loading="saving" @click="save">
                            Guardar
                        </v-btn>
                    </v-card-title>

                    <v-divider />

                    <v-card-text>
                        <v-skeleton-loader v-if="loading" type="list-item-three-line" />

                        <v-empty-state v-else-if="!selectedRole" icon="mdi-shield-key-outline"
                            title="Sin rol seleccionado" text="Elige un rol de la lista para ver y editar sus permisos." />

                        <v-expansion-panels v-else multiple>
                            <v-expansion-panel v-for="(perms, module) in groupedPermissions" :key="module">
                                <v-expansion-panel-title>
                                    {{ formatModule(module) }}
                                </v-expansion-panel-title>

                                <v-expansion-panel-text>
                                    <PermissionCheckboxes :permissions="perms" v-model="selectedPermissions" />
                                </v-expansion-panel-text>
                            </v-expansion-panel>
                        </v-expansion-panels>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import axios from 'axios'
import { toast } from 'vue3-toastify'
import PermissionCheckboxes from '../Roles/PermissionCheckboxes.vue'

export default {
    name: 'PermisosIndex',
    components: {
        PermissionCheckboxes,
    },
    data() {
        return {
            roles: [],
            permissions: [],
            selectedRole: null,
            selectedPermissions: [],
            search: '',
            loading: false,
            saving: false,
        }
    },
    computed: {
        filteredRoles() {
            const query = this.search.trim().toLowerCase()
            if (!query) return this.roles
            return this.roles.filter(role => role.name.toLowerCase().includes(query))
        },
        groupedPermissions() {
            const groups = {}

            this.permissions.forEach(permission => {
                const [module, action] = permission.name.split('.')

                if (!groups[module]) {
                    groups[module] = []
                }

                groups[module].push({
                    name: permission.name,
                    action,
                })
            })

            return groups
        },
    },
    async mounted() {
        await this.loadData()
        if (this.roles.length) {
            await this.selectRole(this.roles[0])
        }
    },
    methods: {
        async loadData() {
            this.loading = true
            try {
                const [roles, permissions] = await Promise.all([
                    axios.get('/roles'),
                    axios.get('/permissions'),
                ])

                this.roles = roles.data
                this.permissions = permissions.data
            } catch (error) {
                console.error(error)
                toast.error('No se pudieron cargar los permisos')
            } finally {
                this.loading = false
            }
        },
        async selectRole(role) {
            this.selectedRole = role
            this.loading = true
            try {
                const { data } = await axios.get(`/roles/${role.id}/permissions`)
                this.selectedPermissions = data
            } catch (error) {
                console.error(error)
                toast.error('No se pudieron cargar los permisos del rol')
            } finally {
                this.loading = false
            }
        },
        async save() {
            if (!this.selectedRole) return

            this.saving = true
            try {
                await axios.post(`/roles/${this.selectedRole.id}/permissions`, {
                    permissions: this.selectedPermissions,
                })
                toast.success('Permisos actualizados')
            } catch (error) {
                console.error(error)
                toast.error('No se pudieron guardar los permisos')
            } finally {
                this.saving = false
            }
        },
        formatModule(module) {
            const map = {
                usuario: 'Usuarios',
                rol: 'Roles',
                tipo_papel: 'Tipos de papel',
                agarrador: 'Tipos de agarrador',
                pagina: 'Paginas',
                banco: 'Bancos',
                producto: 'Productos',
                cliente: 'Clientes',
                venta: 'Ventas',
                produccion: 'Produccion',
                ecommerce: 'Ecommerce',
                promocion: 'Promociones',
                dashboard: 'Dashboards',
                pago: 'Pagos',
                banner: 'Banners',
                menu: 'Menu',
            }

            return map[module] || module
        },
    },
}
</script>
