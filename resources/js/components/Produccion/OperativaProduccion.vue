<template>
    <v-container>
        <h2 class="mb-2">Área: {{ estado.nombre }}</h2>
        <div class="text-caption mb-4">
            Tareas pendientes de producción
        </div>

        <v-alert v-if="!tareas.length" type="info" variant="tonal">
            No hay tareas pendientes en esta área
        </v-alert>

        <v-row>
            <v-col cols="12" md="6" lg="4" v-for="tarea in tareas" :key="tarea.id">
                <TareaProduccionCard :tarea="tarea" @iniciar="abrirProceso" @finalizar="abrirFinalizar" />
            </v-col>
        </v-row>

        <!-- Modales -->
        <ModalProceso v-model="modalProceso" :tarea="tareaSeleccionada" @guardado="recargar" />

        <ConfirmarFinalizar v-model="modalFinalizar" :tarea="tareaSeleccionada" @confirmado="recargar" />
    </v-container>
</template>
<script>
import axios from 'axios'
import TareaProduccionCard from './TareaProduccionCard.vue'
import ModalProceso from './ModalProceso.vue'
import ConfirmarFinalizar from './ConfirmarFinalizar.vue'

export default {
    components: {
        TareaProduccionCard,
        ModalProceso,
        ConfirmarFinalizar
    },

    data() {
        return {
            estado: {},
            tareas: [],
            modalProceso: false,
            modalFinalizar: false,
            tareaSeleccionada: null
        }
    },

    methods: {
        async cargar() {
            const { data } = await axios.get('/produccion/operativa')
            this.estado = data.estado
            this.tareas = data.tareas
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
            this.cargar()
        }
    },

    mounted() {
        this.cargar()
    }
}
</script>