<template>
    <v-row class="mb-4">
        <v-col cols="12">
            <h2 class="text-uppercase font-weight-bold text-center">
                INDUSTRIAS DE EMPAQUES, SOCIEDAD ANÓNIMA
            </h2>
        </v-col>

        <v-row class="justify-space-between">
            <v-col cols="8">
                <div class="text-body-2 mt-2">
                    <div><strong>FECHA EMISIÓN:</strong> {{ formatDate(venta.created_at) }}</div>
                    <div><strong>HORA:</strong> {{ formatTime(venta.created_at) }}</div>
                    <div><strong>NÚMERO DE BOLETA:</strong> {{ venta.numero_completo }}</div>
                    <div><strong>FECHA ENTREGA:</strong> {{ formatDate(venta.fecha_entrega) }}</div>
                </div>
            </v-col>
    
            <v-col cols="2" class="text-center">
                <div class="font-weight-bold">{{ venta.vendedor?.name }}</div>
                <div class="text-caption" style="font-style: italic;">ASESOR DE VENTAS</div>
    
                <div class="mt-4">
                    <div class="font-weight-bold">{{ venta.nombres_paginas_productos }}</div>
                    <div class="text-caption">PÁGINA</div>
                </div>
            </v-col>
        </v-row>
    </v-row>

    <v-divider />
</template>

<script>
export default {
    name: 'VentaHeader',
    props: {
        venta: {
            type: Object,
            required: true
        }
    },
    methods: {
        formatDate(dateString){
            if (!dateString) return '';
            const date = new Date(dateString);

            // Formato dd/mm/aaaa
            return new Intl.DateTimeFormat('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }).format(date);
        },
        formatTime(dateString){
            if (!dateString) return '';
            const date = new Date(dateString);

            return new Intl.DateTimeFormat('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            }).format(date);
        }
    }
}
</script>
