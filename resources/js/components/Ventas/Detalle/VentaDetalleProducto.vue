<template>
    <table class="mt-4 tabla-personalizada" border="1">
        <colgroup>
            <col style="width:8%" />
            <col style="width:20.5%" />
            <col style="width:17%" />
            <col style="width:20.5%" />
            <col style="width:10%" />
            <col style="width:20.5%" />
        </colgroup>

        <tbody>
            <tr>
                <th colspan="3">NOMBRE DEL LOGOTIPO</th>
                <th colspan="3">{{ item.nombre_logo || '-' }}</th>
            </tr>
            <tr>
                <th colspan="3">NOMBRE DEL PRODUCTO</th>
                <th colspan="3">{{ item.producto.nombre || '-' }}</th>
            </tr>

            <tr>
                <th>ALTO</th>
                <td>{{ item.producto.alto }}</td>
                <th>COLOR AGARRADOR</th>
                <td>{{ item.color_agarrador }}</td>
                <th>PRECIO U</th>
                <td>{{ item.precio }}</td>
            </tr>
            <tr>
                <th>ANCHO</th>
                <td>{{ item.producto.ancho }}</td>
                <th>TIPO AGARRADOR</th>
                <td>{{ item.tipo_agarrador.nombre }}</td>
                <th>CANTIDAD</th>
                <td>{{ item.cantidad }}</td>
            </tr>
            <tr>
                <th>FUELLE</th>
                <td>{{ item.producto.fuelle }}</td>
                <th>TIPO PAPEL</th>
                <td>{{ item.tipo_papel.nombre }}</td>
                <th>TOTAL</th>
                <td>{{ item.total }}</td>
            </tr>
            <tr>
                <th>TIPO</th>
                <td>{{ item.producto.tipo }}</td>
                <th>DETALLE IMPRESIÓN</th>
                <td colspan="3">{{ item.detalle_impresion }}</td>
            </tr>

            <tr v-if="item.logo_path">
                <th colspan="3">LOGOTIPO</th>
                <td colspan="3">
                    <img :src="getLogoUrl(item.logo_path)"
                        style="max-height: 80px; max-width: 150px; object-fit: contain;" />

                    <v-tooltip text="Descargar">
                        <template #activator="{ props }">
                            <v-btn v-bind="props" icon size="small" color="primary"
                                @click="descargarLogo(item.logo_path)">
                                <v-icon>mdi-download</v-icon>
                            </v-btn>
                        </template>
                    </v-tooltip>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name: 'VentaDetalleProductos',
    props: {
        item: {
            type: Object,
            required: true
        }
    },
    methods: {
        getLogoUrl(path) {
            return `${import.meta.env.VITE_API_URL}/storage/${path}`
        },
        descargarLogo(path) {
            const url = this.getLogoUrl(path)

            const link = document.createElement('a')
            link.href = url
            link.download = path.split('/').pop()
            link.click()
        }
    }
}
</script>

<style>
table {
    border-collapse: collapse;
    width: 100%;
}

table td {
    padding: 2px 5px;
}

table th {
    text-align: start;
    padding: 2px 3px;
}
</style>
