<template>
    <v-row class="mt-4">
        <!-- CLIENTE -->
        <v-col cols="4">
            <strong>TIPO DE CLIENTE:</strong> Nuevo<br>
            <strong>CLIENTE:</strong> {{ venta.cliente.nombre }}<br>
            <strong>TELÉFONO:</strong> {{ telefonos }}<br>
            <strong>CORREOS:</strong> {{ emails }}<br>
            <strong>NIT:</strong> {{ venta.cliente.nit }}<br>
            <strong>DIRECCIÓN:</strong> {{ direccion }}
        </v-col>

        <!-- PAGO -->
        <v-col cols="4">
            <strong>TIPO DE PAGO:</strong> {{ venta.tipo_pago }}<br>
            <strong>BANCO:</strong> {{ venta.banco?.nombre }}<br>
            <strong>NO. DEPÓSITO:</strong> {{ venta.no_deposito || '-' }}<br>
            <strong>CANTIDAD DEPÓSITO:</strong> <v-chip color="green">Q {{ venta.cantidad_deposito }}</v-chip><br>
            <strong>PENDIENTE A PAGAR:</strong> <v-chip color="red">Q {{ venta.pendiente_pagar }}</v-chip>
        </v-col>

        <!-- TOTALES -->
        <v-col cols="4">
            <div><strong>SUB-TOTAL:</strong> <v-chip color="primary">Q {{ venta.subtotal }}</v-chip></div>
            <div><strong>DESCUENTO:</strong> <v-chip color="red">Q {{ venta.descuento }}</v-chip></div>
            <div><strong>PROMOCIONES:</strong> <v-chip color="red">Q {{ venta.promociones }}</v-chip></div>
            <div><strong>COSTO DE LOGO:</strong> <v-chip color="green">Q {{ venta.costo_logo }}</v-chip></div>
            <div><strong>COSTO DE ENVÍO:</strong> <v-chip color="green">Q {{ venta.costo_envio }}</v-chip></div>

            <div class="mt-2 font-weight-bold">
                <v-chip color="red" class="text-h6">TOTAL: Q {{ venta.total }}</v-chip>
            </div>
        </v-col>
    </v-row>

    <v-divider class="my-4" />
</template>

<script>
export default {
    name: 'VentaClientePago',
    props: {
        venta: {
            type: Object,
            required: true
        }
    },
    computed: {
        telefonos() {
            return this.venta.cliente.telefonos
                ?.map(t => `${t.telefono_codigo_pais} ${t.telefono_numero}`)
                .join(' / ') || '-'
        },
        emails() {
            return this.venta.cliente.emails
                ?.map(e=> `${e.email }`)
                .join(' / ') || '-'
        },
        direccion() {
            const c = this.venta.cliente
            if (c.municipio) {
                return `${c.direccion}, ${c.municipio.nombre}, ${c.municipio.departamento.nombre}`
            }
            return `${c.direccion}, ${c.ciudad_pais}, ${c.estado_pais}`
        }
    }
}
</script>
