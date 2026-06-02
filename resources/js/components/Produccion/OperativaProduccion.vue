<template>
    <v-container>
        <h2 class="mb-2">Área: {{ estado.nombre }}</h2>
        <div class="text-caption mb-4">
            Tareas pendientes de producción
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
            {{ error }}
        </v-alert>

        <v-alert v-if="!loading && !error && !tareas.length" type="info" variant="tonal">
            No hay tareas pendientes en esta área
        </v-alert>

        <v-row v-if="loading">
            <v-col cols="12">
                <v-skeleton-loader type="card" />
            </v-col>
        </v-row>

        <v-row v-else>
            <v-col cols="12" md="6" lg="4" v-for="tarea in tareas" :key="tarea.id">
                <TareaProduccionCard :tarea="tarea" :estado="estado" @iniciar="abrirProceso" @finalizar="abrirFinalizar" @regresar="abrirRegresar"/>
            </v-col>
        </v-row>

        <!-- Modales -->
        <ModalProceso v-model="modalProceso" :tarea="tareaSeleccionada" @guardado="recargar" />

        <ConfirmarFinalizar v-model="modalFinalizar" :tarea="tareaSeleccionada" @confirmado="recargar" />

        <ModalRegresar v-model="modalRegresar" :tarea="tareaSeleccionada" @confirmado="recargar" />

    </v-container>
</template>
<script>
import axios from 'axios'
import TareaProduccionCard from './TareaProduccionCard.vue'
import ModalProceso from './ModalProceso.vue'
import ConfirmarFinalizar from './ConfirmarFinalizar.vue'
import ModalRegresar from './ModalRegresar.vue'
import { toast } from 'vue3-toastify'

export default {
    components: {
        TareaProduccionCard,
        ModalProceso,
        ConfirmarFinalizar,
        ModalRegresar
    },

    data() {
        return {
            estado: {},
            tareas: [],
            modalProceso: false,
            modalFinalizar: false,
            tareaSeleccionada: null,
            modalRegresar: false,
            loading: false,
            error: '',
        }
    },

    methods: {
        async cargar() {
            this.loading = true
            this.error = ''

            try {
                const { data } = await axios.get('/produccion/operativa')
                this.estado = data.estado
                this.tareas = data.tareas
            } catch (error) {
                this.estado = {}
                this.tareas = []
                this.error = error.response?.data?.message || 'No se pudieron cargar las tareas de producción'
                toast.error(this.error)
            } finally {
                this.loading = false
            }
        },

        abrirProceso(tarea) {
            this.tareaSeleccionada = tarea
            this.modalProceso = true
        },

        abrirFinalizar(tarea) {
            this.tareaSeleccionada = tarea
            this.modalFinalizar = true
        },

        recargar() {
            this.modalProceso = false
            this.modalFinalizar = false
            this.modalRegresar = false
            this.cargar()
        },

        abrirRegresar(tarea) {
            this.tareaSeleccionada = tarea
            this.modalRegresar = true
        }

    },

    mounted() {
        this.cargar()
    }
}
</script>
