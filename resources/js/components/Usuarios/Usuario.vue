<template>
    <v-container fluid class="py-6">
        <!-- Top bar -->
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona usuarios</div>
                </v-col>
                <v-col cols="6" class="d-flex ga-2 align-center justify-end">
                    <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar..."
                        prepend-inner-icon="mdi-magnify" style="max-width: 280px" />
                    <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate" variant="tonal" :loading="loading" v-if="can('usuario.crear')">
                        Nuevo
                    </v-btn>
                </v-col>
            </v-row>
        </div>

        <!-- Table -->
        <v-card rounded="xl" variant="flat" class="pa-2">
            <v-data-table :headers="headers" :items="filteredUsers" :loading="loading" item-key="id"
                fixed-header height="400px" :header-props="{ class: 'bg-green-darken-2'}" density="compact" v-if="can('usuario.ver')">
                <template v-slot:[`item.status`]="{item}">
                    <v-chip size="small" :color="item.status === 'active' ? 'success' : 'red'" variant="tonal" label>
                        {{ item.status === 'active' ? 'Activo' : 'Inactivo' }}
                    </v-chip>
                </template>

                <template v-slot:[`item.roles`]="{item}">
                    <div class="d-flex flex-wrap ga-1">
                        <v-chip v-for="r in (item.roles || [])" :key="r" size="x-small" color="primary" variant="tonal"
                            label>
                            {{ r.name }}
                        </v-chip>
                    </div>
                </template>

                <template v-slot:[`item.actions`]="{ item }">
                    <v-btn icon variant="text" @click="openEdit(item)" density="compact" v-if="can('usuario.editar')">
                        <v-icon>mdi-pencil-outline</v-icon>
                    </v-btn>
                    <v-btn icon variant="text" @click="openDelete(item)" density="compact" v-if="can('usuario.borrar')">
                        <v-icon color="error">mdi-delete-outline</v-icon>
                    </v-btn>
                </template>

                <template #no-data>
                    <div class="pa-8 text-center text-medium-emphasis">
                        No hay usuarios para mostrar.
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Drawer Create/Edit -->
        <v-navigation-drawer v-model="drawer" location="right" width="420" temporary>
            <div class="pa-5 d-flex align-center justify-space-between">
                <div>
                    <div class="text-subtitle-1 font-weight-bold">
                        {{ form.id ? 'Editar usuario' : 'Crear usuario' }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                        {{ form.id ? 'Actualiza los datos del usuario' : 'Registra un nuevo usuario' }}
                    </div>
                </div>

                <v-btn icon variant="text" @click="drawer = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </div>

            <v-divider />

            <v-form class="pa-5" @submit.prevent="submit">
                <v-text-field v-model="form.name" label="Nombre" variant="outlined" :error-messages="errors.name"
                    required />

                <v-text-field v-model="form.email" label="Email" variant="outlined" :error-messages="errors.email"
                    required />

                <v-text-field v-model="form.password" :label="form.id ? 'Password (opcional)' : 'Password'"
                    variant="outlined" :type="showPassword ? 'text' : 'password'"
                    :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="showPassword = !showPassword" :error-messages="errors.password"
                    :required="!form.id" />

                <v-select v-model="form.role" :items="roles" item-title="name" item-value="id" label="Rol" variant="outlined"
                    :error-messages="errors.role" clearable />

                <v-switch v-model="form.active" inset color="success" label="Activo" class="mt-2" />
                <v-row>
                    <v-col cols="12" class="d-flex ga-2 mt-4 justify-center">
                        <v-btn variant="tonal" @click="drawer = false">
                            Cancelar
                        </v-btn>
                        <v-btn :loading="saving" color="primary" type="submit">
                            Guardar
                        </v-btn>
                    </v-col>
                </v-row>
            </v-form>
        </v-navigation-drawer>

        <!-- Delete confirm -->
        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card rounded="xl">
                <v-card-title class="text-subtitle-1 font-weight-bold">
                    Eliminar usuario
                </v-card-title>

                <v-card-text class="text-body-2 text-medium-emphasis">
                    ¿Seguro que quieres eliminar a <b>{{ toDelete?.name }}</b>? Esta acción no se puede deshacer.
                </v-card-text>

                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="tonal" @click="deleteDialog = false">Cancelar</v-btn>
                    <v-btn color="error" :loading="deleting" @click="confirmDelete">Eliminar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- ifnormation -->
        <v-dialog v-model="informationDialog" max-width="420">
            <v-card rounded="xl">
                <v-card-title class="text-subtitle-1 font-weight-bold">
                    Información
                </v-card-title>

                <v-card-text class="text-body-2 text-medium-emphasis">
                    {{ message }}
                </v-card-text>

                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="sucess" @click="informationDialog = false">Aceptar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>

export default {
    name: 'UsuariosPage',

    data() {
        return {
            // UI state
            loading: false,
            saving: false,
            deleting: false,
            drawer: false,
            deleteDialog: false,
            showPassword: false,

            // data
            search: '',
            users: [],
            roles: [],
            toDelete: null,

            // form
            form: {
                id: null,
                name: '',
                email: '',
                password: '',
                role: null,
                active: true,
            },

            // errors
            errors: {
                name: null,
                email: null,
                password: null,
                role: null,
            },

            headers: [
                { title: 'Nombre', key: 'name' },
                { title: 'Email', key: 'email' },
                { title: 'Estado', key: 'status' },
                { title: 'Roles', key: 'roles' },
                { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
            ],

            informationDialog: false,
            message: "",
        }
    },

    computed: {
        filteredUsers() {
            const q = this.search.trim().toLowerCase()
            if (!q) return this.users

            return this.users.filter((u) => {
                const name = (u.name || '').toLowerCase()
                const email = (u.email || '').toLowerCase()
                return name.includes(q) || email.includes(q)
            })
        },
    },

    mounted() {
        this.fetchUsers()
    },

    methods: {
        resetErrors() {
            this.errors = { name: null, email: null, password: null, role: null }
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                email: '',
                password: '',
                role: null,
                active: true,
            }
            this.showPassword = false
            this.resetErrors()
        },

        openCreate() {
            this.resetForm()
            this.drawer = true
        },

        openEdit(item) {
            this.resetForm()
            this.form.id = item.id
            this.form.name = item.name
            this.form.email = item.email
            console.log(item);
            this.form.role = item.roles[0].id ? item.roles[0].id : null
            this.form.active = item.status === 'active'
            this.drawer = true
        },

        openDelete(item) {
            this.toDelete = item
            this.deleteDialog = true
        },

        async fetchUsers() {
            this.loading = true
            try {
                const { data } = await axios.get('/usuarios')
                this.users = data
                await this.fetchRoles()
            } catch (err) {
                console.error(err)
            } finally {
                this.loading = false
            }
        },

        async fetchRoles(){
            const { data } = await axios.get('/usuario/roles');
            this.roles = data;
        },

        async submit() {
            this.resetErrors()
            this.saving = true

            try {
                const payload = {
                    name: this.form.name,
                    email: this.form.email,
                    role: this.form.role,
                    active: this.form.active,
                }

                // Solo mandar password si viene
                if (this.form.password) payload.password = this.form.password

                if (this.form.id) {
                    await axios.put(`/usuarios/${this.form.id}`, payload)
                } else {
                    await axios.post('/usuarios', payload)
                }

                this.drawer = false
                await this.fetchUsers()
            } catch (err) {
                const e = err?.response?.data?.errors
                if (e) {
                    this.errors.name = e.name?.[0] ?? null
                    this.errors.email = e.email?.[0] ?? null
                    this.errors.password = e.password?.[0] ?? null
                    this.errors.role = e.role?.[0] ?? null
                } else {
                    console.error(err)
                }
            } finally {
                this.saving = false
            }
        },

        async confirmDelete() {
            if (!this.toDelete) return
            this.deleting = true

            try {
                await axios.delete(`/usuarios/${this.toDelete.id}`)
                this.deleteDialog = false
                this.toDelete = null
                await this.fetchUsers()
            } catch (err) {
                this.deleteDialog = false;
                this.informationDialog = true;
                this.message = err.response.data.message
            } finally {
                this.deleting = false
            }
        },
    },
}
</script>
