<template>
    <v-container>
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona los clientes</div>
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
                                v-if="can('cliente.reporte')">
                                <v-list-item-title>Excel</v-list-item-title>
                            </v-list-item>

                            <v-list-item prepend-icon="mdi-file-pdf-box" @click="exportPdf"
                                v-if="can('cliente.reporte')">
                                <v-list-item-title>PDF</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <v-btn color="primary" prepend-icon="mdi-plus" @click="$router.push('/cliente/create')"
                        variant="tonal" :loading="loading" v-if="can('cliente.crear')">
                        Nuevo
                    </v-btn>
                </v-col>
            </v-row>
        </div>

        <v-data-table :headers="headers" :items="clientes" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-green-darken-2' }" density="compact" v-if="can('cliente.ver')">

            <template v-slot:[`item.contacto`]="{ item }">
                <v-menu open-on-hover location="end" v-if="item.emails.length > 0">
                    <template #activator="{ props }">
                        <span v-bind="props" class="cursor-pointer">
                            📧 {{ item.emails[0].email }}
                            <span v-if="item.emails.length > 1" class="text-caption text-medium-emphasis">
                                (+{{ item.emails.length - 1 }})
                            </span>
                        </span>
                    </template>

                    <v-list density="compact">
                        <v-list-item v-for="(e, i) in item.emails" :key="i">
                            <v-list-item-title>{{ e.email }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>


                <v-menu open-on-hover location="end" v-if="item.telefonos.length > 0">
                    <template #activator="{ props }">
                        <span v-bind="props" class="cursor-pointer">
                            📞 {{ item.telefonos[0].telefono_codigo_pais }} {{ item.telefonos[0].telefono_numero }}
                            <span v-if="item.telefonos.length > 1" class="text-caption text-medium-emphasis">
                                (+{{ item.telefonos.length - 1 }})
                            </span>
                        </span>
                    </template>

                    <v-list density="compact">
                        <v-list-item v-for="(t, i) in item.telefonos" :key="i">
                            <v-list-item-title>
                                {{ t.telefono_codigo_pais }} {{ t.telefono_numero }}
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>

            </template>

            <template v-slot:[`item.ubicacion`]="{ item }">
                <div class="text-body-2">
                    <template v-if="item.municipio">
                        {{ item.municipio.nombre }},
                        {{ item.municipio.departamento.nombre }}
                    </template>

                    <template v-else>
                        {{ item.ciudad_pais }} — {{ item.estado_pais }}
                    </template>
                </div>
            </template>



            <template v-slot:[`item.actions`]="{ item }">
                <v-row class="ga-2">
                    <v-btn icon @click="$router.push(`/cliente/${item.id}/edit`)" color="primary" variant="tonal"
                        density="compact" v-if="can('cliente.editar')">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>

                    <v-btn icon @click="openDelete(item)" color="red" variant="tonal" density="compact"
                        v-if="can('cliente.borrar')">
                        <v-icon>mdi-delete-outline</v-icon>
                    </v-btn>
                </v-row>
            </template>

            <template v-slot:[`item.created_at`]="{ item }">
                {{ formatDate(item.created_at) }}
            </template>

            <template v-slot:[`item.updated_at`]="{ item }">
                {{ formatDate(item.updated_at) }}
            </template>
        </v-data-table>



        <!-- DIALOG PARA ELIMINAR -->
        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card rounded="xl">
                <v-card-title class="text-subtitle-1 font-weight-bold">
                    Eliminar la página
                </v-card-title>

                <v-card-text class="text-body-2 text-medium-emphasis">
                    ¿Seguro que quieres eliminar a <b>{{ toDelete?.nombre }}</b>? Esta acción no se puede deshacer.
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
import axios from 'axios'
import { toast } from 'vue3-toastify'


export default {
    name: 'cliente.index',
    components: {
    },
    data() {
        return {
            clientes: [],
            loading: false,
            deleting: false,
            showPermissions: false,
            selectedRole: null,

            headers: [
                { title: 'Nombre', key: 'nombre' },
                { title: 'Contacto', key: 'contacto' },
                { title: 'Ubicación', key: 'ubicacion' },
                { title: 'Dirección', key: 'direccion' },
                { title: 'DPI', key: 'dpi' },
                { title: 'NIT', key: 'nit' },
                { title: 'Creado', key: 'created_at' },
                { title: 'Actualizado', key: 'updated_at' },
                { title: 'Acciones', key: 'actions', sortable: false }
            ],
            search: null,
            toDelete: null,
            deleteDialog: false,
            informationDialog: false,
            message: "",
        }
    },

    mounted() {
        this.fetchCliente()
        const toastType = this.$route.query.toast

        if (toastType === 'saved') {
            toast.success('Cliente guardado correctamente', {
                autoClose: 2000,
                onClose: () => {
                    this.$router.replace({ query: {} })
                }
            })
        }

        if (toastType === 'updated') {
            toast.success('Cliente actualizado correctamente', {
                autoClose: 2000,
                onClose: () => {
                    this.$router.replace({ query: {} })
                }
            })
        }
    },

    watch: {
        search() {
            clearTimeout(this.debounceTimer)

            this.debounceTimer = setTimeout(() => {
                this.fetchCliente()
            }, 300)
        }
    },

    methods: {
        async fetchCliente() {
            this.loading = true

            const params = {
                search: this.search
            }

            await axios.get('/cliente', { params })
                .then(res => this.clientes = res.data)
                .finally(() => this.loading = false)
        },

        openDelete(item) {
            this.toDelete = item
            this.deleteDialog = true
        },

        exportExcel() {
            const params = new URLSearchParams({
                search: this.search
            })

            window.location.href = `/cliente/export/excel?${params.toString()}`
        },

        exportPdf() {
            const params = new URLSearchParams({
                search: this.search
            })
            window.open(`/cliente/export/pdf?${params.toString()}`, '_blank')
        },

        async confirmDelete() {
            if (!this.toDelete) return
            this.deleting = true

            try {
                await axios.delete(`/cliente/${this.toDelete.id}`)
                this.deleteDialog = false
                this.toDelete = null
                await this.fetchCliente()
                toast.success('Cliente eliminado')
            } catch (err) {
                this.deleteDialog = false;
                this.informationDialog = true;
                this.message = err.response.data.message
            } finally {
                this.deleting = false
            }
        },

        formatDate(date) {
            if (!date) return ''
            return new Date(date).toLocaleString('es-GT', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            })
        }
    }
}
</script>
