<template>
    <v-container>
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona los Productos</div>
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
                                v-if="can('producto.reporte')">
                                <v-list-item-title>Excel</v-list-item-title>
                            </v-list-item>

                            <v-list-item prepend-icon="mdi-file-pdf-box" @click="exportPdf"
                                v-if="can('producto.reporte')">
                                <v-list-item-title>PDF</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <v-btn color="primary" prepend-icon="mdi-plus" @click="$router.push('/producto/create')" variant="tonal" :loading="loading"
                        v-if="can('producto.crear')">
                        Nuevo
                    </v-btn>
                </v-col>
            </v-row>
        </div>

        <v-data-table :headers="headers" :items="roles" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-green-darken-2' }" density="compact" :search="search"
            v-if="can('producto.ver')">
            <template v-slot:[`item.actions`]="{ item }">
                <v-row class="ga-2">
                    <v-btn icon @click="$router.push(`/producto/${item.id}/edit`)" color="primary" variant="tonal" density="compact"
                        v-if="can('producto.editar')">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>

                    <v-btn icon @click="openDelete(item)" color="red" variant="tonal" density="compact"
                        v-if="can('producto.borrar')">
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
                    Eliminar el producto
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
    name: 'producto.index',
    data() {
        return {
            roles: [],
            loading: false,
            deleting: false,
            showPermissions: false,
            selectedRole: null,

            headers: [
                { title: 'Nombre', key: 'nombre' },
                { title: 'Creado', key: 'created_at' },
                { title: 'Actualizado', key: 'updated_at' },
                { title: 'Acciones', key: 'actions', sortable: false }
            ],
            search: null,
            selected: null,
            toDelete: null,
            deleteDialog: false,
            informationDialog: false,
            message: "",
        }
    },

    mounted() {
        this.fetchProducto()
        const toastType = this.$route.query.toast
        if (toastType === 'saved') {
            toast.success('Producto guardado correctamente', {
                autoClose: 2000,
                onClose: () => {
                    this.$router.replace({ query: {} })
                }
            })
        }

        if (toastType === 'updated') {
            toast.success('Producto actualizado correctamente', {
                autoClose: 2000,
                onClose: () => {
                    this.$router.replace({ query: {} })
                }
            })
        }
    },

    methods: {
        async fetchProducto() {
            this.loading = true
            await axios.get('/producto')
                .then(res => this.roles = res.data)
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

            window.location.href = `/producto/export/excel?${params.toString()}`
        },

        exportPdf() {
            const params = new URLSearchParams({
                search: this.search
            })
            window.open(`/producto/export/pdf?${params.toString()}`, '_blank')
        },

        async confirmDelete() {
            if (!this.toDelete) return
            this.deleting = true

            try {
                await axios.delete(`/producto/${this.toDelete.id}`)
                this.deleteDialog = false
                this.toDelete = null
                await this.fetchProducto()
                toast.success('Producto eliminado')
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
