<template>
    <v-card outlined>
        <v-card-title class="text-subtitle-1">
            {{ tarea.detalle_venta.producto.nombre }}
        </v-card-title>

        <v-card-subtitle>
            Venta {{ tarea.detalle_venta.venta.serie }}-{{ tarea.detalle_venta.venta.numero }}
        </v-card-subtitle>

        <v-card-text>
            <div class="mb-2">
                Cantidad: <strong>{{ tarea.detalle_venta.cantidad }}</strong>
            </div>
            <v-chip :color="tarea.proceso_estado ? 'primary' : 'grey'" variant="tonal" size="small">
                {{ tarea.proceso_estado?.nombre ?? 'Sin iniciar' }}
            </v-chip>
            <v-chip v-if="ultimoRegreso" color="red" variant="tonal" class="mt-2">
                <v-icon>mdi-arrow-u-left-top</v-icon> Regresado desde {{ ultimoRegreso.estado_produccion?.nombre }}
            </v-chip>

            <div v-if="tarea.detalle_venta.imagenes?.length && estado.nombre=='Área de impresión'" class="mt-3">

                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;">

                    <div v-for="img in tarea.detalle_venta.imagenes" :key="img.id"
                        style="position:relative; border:1px solid #eee; border-radius:8px; overflow:hidden;">

                        <img :src="getImagenUrl(img.path)" style="width:100%; height:80px; object-fit:cover;" />

                        <!-- Descargar -->
                        <v-btn icon size="x-small" color="primary" style="position:absolute; bottom:4px; right:4px;"
                            @click="descargarImagen(img.path)">
                            <v-icon size="14">mdi-download</v-icon>
                        </v-btn>

                    </div>

                </div>

            </div>

            <!-- DISEÑO -->
            <div v-if="tarea.detalle_venta.archivo_diseno_path && estado.nombre=='Área de impresión'" class="mt-3 pa-2"
                style="background:#f5f5f5; border-radius:8px;">

                <div class="d-flex align-center justify-space-between">

                    <div
                        style="font-size:12px; max-width:70%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ getNombreArchivo(tarea.detalle_venta.archivo_diseno_path) }}
                    </div>

                    <v-btn icon size="small" color="purple"
                        @click="descargarDiseno(tarea.detalle_venta.archivo_diseno_path)">
                        <v-icon>mdi-download</v-icon>
                    </v-btn>

                </div>

            </div>

        </v-card-text>

        <v-card-actions class="d-flex flex-column ga-2">
            <!-- INICIAR -->
            <v-btn v-if="!tarea.proceso_estado" color="green" variant="tonal" block @click="$emit('iniciar', tarea)">
                Iniciar proceso
            </v-btn>

            <!-- CAMBIAR -->
            <v-btn v-if="tarea.proceso_estado" color="amber-darken-1" variant="tonal" block
                @click="$emit('iniciar', tarea)">
                Cambiar proceso
            </v-btn>

            <!-- FINALIZAR -->
            <v-btn v-if="tarea.proceso_estado" color="deep-orange" variant="tonal" block
                @click="$emit('finalizar', tarea)">
                Finalizar / Revisado
            </v-btn>

            <!-- REGRESAR (SIEMPRE QUE NO ESTÉ FINALIZADO) -->
            <v-btn color="purple-darken-1" variant="tonal" block @click="$emit('regresar', tarea)">
                Regresar
            </v-btn>

        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'TareaProduccionCard',
    props: {
        tarea: {
            type: Object,
            required: true
        },
        estado: {
            type: Object,
            required: true
        }
    },

    computed: {
        ultimoRegreso() {

            const historial = this.tarea.detalle_venta?.historial_estados
            if (!historial) return null

            // Último regreso registrado
            const regreso = [...historial]
                .filter(h => h.tipo_evento === 'regreso_estado')
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!regreso) return null

            // Estado actual activo
            const estadoActivo = historial
                .filter(h => h.tipo_evento === 'entrada_estado' && !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!estadoActivo) return null

            // Necesitamos comparar orden
            const ordenActual = estadoActivo.estado_produccion?.orden
            const ordenDesde = regreso.estado_produccion?.orden

            if (!ordenActual || !ordenDesde) return null

            // Mostrar solo si aún no supera el estado desde donde regresó
            if (ordenActual <= ordenDesde) {
                return regreso
            }

            return null
        },

        /* ultimoRegreso() {

            const historial = this.tarea.detalle_venta?.historial_estados
            if (!historial) return null

            // Estado actual activo
            const estadoActivo = historial
                .filter(h => h.tipo_evento === 'entrada_estado' && !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!estadoActivo) return null

            // Buscar si justo antes de esta entrada hubo un regreso
            const ultimoEventoAntes = historial
                .filter(h => new Date(h.fecha_inicio) < new Date(estadoActivo.fecha_inicio))
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (ultimoEventoAntes?.tipo_evento === 'regreso_estado') {
                return ultimoEventoAntes
            }

            return null
        } */
    },

    methods: {
        getImagenUrl(path) {
            return `https://d2r0bm90jl3wk0.cloudfront.net/${path}`
        },

        async descargarImagen(path) {
            try {
                const url = `${this.getImagenUrl(path)}?t=${new Date().getTime()}`

                const response = await fetch(url)
                const blob = await response.blob()

                const blobUrl = window.URL.createObjectURL(blob)

                const link = document.createElement('a')
                link.href = blobUrl
                link.download = path.split('/').pop()

                document.body.appendChild(link)
                link.click()

                document.body.removeChild(link)
                window.URL.revokeObjectURL(blobUrl)

            } catch (error) {
                console.error(error)
                toast.error('No se pudo descargar la imagen')
            }
        },

        getNombreArchivo(path) {
            return path.split('/').pop()
        },

        getDisenoUrl(path) {
            return `https://d2r0bm90jl3wk0.cloudfront.net/${path}`
        },

        descargarDiseno(path) {
            const url = this.getDisenoUrl(path)

            const link = document.createElement('a')
            link.href = url
            link.download = this.getNombreArchivo(path)

            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
        }
    }

}
</script>
