<template>
    <v-container>

        <!-- HEADER -->
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">
                        Gestiona las promociones
                    </div>
                </v-col>

                <v-col cols="6" class="d-flex ga-2 align-center justify-end">

                    <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar..."
                        prepend-inner-icon="mdi-magnify" style="max-width: 280px" />

                    <v-btn color="primary" prepend-icon="mdi-plus" @click="create" variant="tonal" :loading="loading">
                        Nueva
                    </v-btn>

                </v-col>
            </v-row>
        </div>

        <!-- TABLA -->
        <v-data-table :headers="headers" :items="promociones" :loading="loading" fixed-header height="400px"
            density="compact" :search="search" :header-props="{ class: 'bg-green-darken-2' }">

            <!-- ACCIONES -->
            <template v-slot:[`item.actions`]="{ item }">
                <v-row class="ga-2">
                    <v-btn icon @click="edit(item)" color="primary" variant="tonal" density="compact">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>

                    <v-btn icon @click="openDelete(item)" color="red" variant="tonal" density="compact">
                        <v-icon>mdi-delete-outline</v-icon>
                    </v-btn>
                </v-row>
            </template>

            <!-- FORMATO FECHA -->
            <template v-slot:[`item.fecha_inicio`]="{ item }">
                {{ formatDate(item.fecha_inicio) }}
            </template>

            <template v-slot:[`item.fecha_fin`]="{ item }">
                {{ formatDate(item.fecha_fin) }}
            </template>

            <!-- FORMATO TIPO -->
            <template v-slot:[`item.tipo`]="{ item }">
                <v-chip size="small" :color="item.tipo === 'porcentaje' ? 'blue' : 'green'">
                    {{ item.tipo }}
                </v-chip>
            </template>

            <!-- FORMATO VALOR -->
            <template v-slot:[`item.valor`]="{ item }">
                <span v-if="item.tipo === 'porcentaje'">
                    {{ item.valor }}%
                </span>
                <span v-else>
                    Q {{ item.valor }}
                </span>
            </template>

            <template v-slot:[`item.created_at`]="{ item }">
                {{ formatDate2(item.created_at) }}
            </template>

            <template v-slot:[`item.updated_at`]="{ item }">
                {{ formatDate2(item.updated_at) }}
            </template>

        </v-data-table>

        <!-- DIALOG FORM -->
        <PromocionDialog v-model="dialog" :promocion="selected" @saved="onSaved" />

        <!-- DIALOG DELETE -->
        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card rounded="xl">
                <v-card-title class="text-subtitle-1 font-weight-bold">
                    Eliminar promoción
                </v-card-title>

                <v-card-text class="text-body-2 text-medium-emphasis">
                    ¿Seguro que quieres eliminar <b>{{ toDelete?.nombre }}</b>?
                </v-card-text>

                <v-card-actions class="px-4 pb-4">
                    <v-spacer />
                    <v-btn variant="tonal" @click="deleteDialog = false">Cancelar</v-btn>
                    <v-btn color="error" :loading="deleting" @click="confirmDelete">Eliminar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </v-container>
</template>
<script>
import axios from 'axios'
import PromocionDialog from './PromocionDialog.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'promociones.index',

    components: {
        PromocionDialog
    },

    data() {
        return {
            promociones: [],
            loading: false,
            deleting: false,

            headers: [
                { title: 'Código', key: 'id' },
                { title: 'Nombre', key: 'nombre' },
                { title: 'Tipo', key: 'tipo' },
                { title: 'Valor', key: 'valor' },
                { title: 'Inicio', key: 'fecha_inicio' },
                { title: 'Fin', key: 'fecha_fin' },
                { title: 'Aplica a', key: 'aplica_a' },
                { title: 'Creado', key: 'created_at' },
                { title: 'Actualizado', key: 'updated_at' },
                { title: 'Acciones', key: 'actions', sortable: false }
            ],

            search: null,
            dialog: false,
            selected: null,
            toDelete: null,
            deleteDialog: false,
        }
    },

    mounted() {
        this.fetchPromociones()
    },

    methods: {

        async fetchPromociones() {
            this.loading = true
            await axios.get('/promocion')
                .then(res => this.promociones = res.data)
                .finally(() => this.loading = false)
        },

        create() {
            this.selected = null
            this.dialog = true
        },

        edit(item) {
            this.selected = item
            this.dialog = true
        },

        onSaved() {
            this.fetchPromociones()
            toast.success('Promoción guardada')
        },

        openDelete(item) {
            this.toDelete = item
            this.deleteDialog = true
        },

        async confirmDelete() {
            if (!this.toDelete) return

            this.deleting = true

            try {
                await axios.delete(`/promocion/${this.toDelete.id}`)
                this.deleteDialog = false
                this.toDelete = null
                await this.fetchPromociones()
                toast.success('Promoción eliminada')
            } catch (err) {
                console.error(err)
            } finally {
                this.deleting = false
            }
        },

        formatDate(date) {
            if (!date) return ''

            const [year, month, day] = date.split('-')
            return `${day}/${month}/${year}`
        },

        formatDate2(date) {
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