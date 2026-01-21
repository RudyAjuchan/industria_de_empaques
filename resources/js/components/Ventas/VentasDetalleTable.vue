<template>
    <v-card class="mt-4">

        <v-card-title>
            Detalle de Venta
            <v-spacer />
            <v-btn size="small" color="primary" @click="agregarFila">
                Agregar Producto
            </v-btn>
        </v-card-title>

        <v-table density="compact" class="ventas_table">

            <thead>
                <tr>
                    <th style="min-width: 230px">Producto</th>
                    <th style="min-width: 75px">Alto</th>
                    <th style="min-width: 75px">Ancho</th>
                    <th style="min-width: 75px">Fuelle</th>
                    <th style="min-width: 120px">Tipo</th>
                    <th style="min-width: 120px">Color Agarrador</th>
                    <th style="min-width: 200px">Detalle Impresión</th>
                    <th style="min-width: 180px">Agarrador</th>
                    <th style="min-width: 180px">Papel</th>
                    <th style="min-width: 160px">Nom.Log</th>
                    <th style="min-width: 100px">Precio</th>
                    <th style="min-width: 75px">Cantidad</th>
                    <th style="min-width: 120px">Total</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(item, index) in detalle" :key="index">

                    <td>
                        <v-autocomplete :items="productos" item-title="nombre" item-value="id"
                            v-model="item.productos_id" @update:modelValue="actualizarProducto(item)" dense
                            hide-details density="compact" variant="outlined">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openProductoDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-autocomplete>
                    </td>

                    <td><v-text-field v-model="item.alto" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td><v-text-field v-model="item.ancho" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td><v-text-field v-model="item.fuelle" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td><v-text-field v-model="item.tipo" readonly dense hide-details disabled density="compact" variant="outlined"/></td>

                    <td><v-text-field v-model="item.color_agarrador" dense hide-details density="compact" variant="outlined"/></td>
                    <td><v-text-field v-model="item.detalle_impresion" dense hide-details density="compact" variant="outlined" /></td>
                    <td>
                        <v-select :items="tiposAgarrador" item-title="nombre" item-value="id"
                            v-model="item.tipo_agarradors_id" dense hide-details density="compact" variant="outlined">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openAgarradorDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-select>
                    </td>

                    <td>
                        <v-select :items="tiposPapel" item-title="nombre" item-value="id" v-model="item.tipo_papels_id"
                            dense hide-details density="compact" variant="outlined">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openPapelDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-select>

                    </td>



                    <td><v-text-field v-model="item.nombre_logo" dense hide-details density="compact" variant="outlined"/></td>

                    <td>
                        <v-text-field v-model="item.precio" @input="calcularFila(item)" dense
                            hide-details density="compact" variant="outlined"/>
                    </td>

                    <td>
                        <v-text-field v-model="item.cantidad" @input="calcularFila(item)" dense
                            hide-details density="compact" variant="outlined"/>
                    </td>

                    <td>
                        <v-text-field :model-value="item.total" readonly dense hide-details density="compact" variant="outlined" disabled/>
                    </td>

                    <td>
                        <v-btn icon size="small" color="red" @click="eliminarFila(index)">
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>

                </tr>
            </tbody>

        </v-table>

    </v-card>
    <PapelDialog v-model="dialogPapel" @saved="onPapelSave"></PapelDialog>
    <AgarradorDialog v-model="dialogAgarrador" @saved="onAgarradorSave"></AgarradorDialog>
    <ProductoDialog v-model="dialogProducto" @saved="onProductoSave" @cancel="dialogProducto = false"></ProductoDialog>
</template>


<script>
import AgarradorDialog from '../Agarradores/AgarradorDialog.vue'
import PapelDialog from '../TipoPapel/TipoPapelDialog.vue'
import ProductoDialog from '../Productos/ProductosDialog.vue'
import { toast } from 'vue3-toastify'
export default {
    props: {
        modelValue: Array,
        productos: Array,
        tiposAgarrador: Array,
        tiposPapel: Array
    },
    components:{
        AgarradorDialog,
        PapelDialog,
        ProductoDialog,
    },

    emits: ['update:modelValue', 'producto-saved', 'agarrador-saved', 'papel-saved'],

    data() {
        return {
            detalle: this.modelValue || [],
            dialogAgarrador: false,
            filaAgarradorIndex: null,
            dialogPapel: false,
            filaPapelIndex: null,
            dialogProducto: false,
            filaProductoIndex: null,
        }
    },

    watch: {
        detalle: {
            handler(val) {
                this.$emit('update:modelValue', val)
            },
            deep: true
        }
    },

    methods: {

        agregarFila() {
            this.detalle.push({
                productos_id: null,
                tipo_agarradors_id: null,
                tipo_papels_id: null,
                color_agarrador: '',
                detalle_impresion: '',
                nombre_logo: '',
                precio: 0,
                cantidad: 1,
                total: 0,
                alto: '',
                ancho: '',
                fuelle: '',
                tipo: ''
            })
        },

        eliminarFila(index) {
            this.detalle.splice(index, 1)
        },

        actualizarProducto(item) {
            const producto = this.productos.find(p => p.id === item.productos_id)

            if (producto) {
                item.alto = producto.alto
                item.ancho = producto.ancho
                item.fuelle = producto.fuelle
                item.tipo = producto.tipo
            }

            this.calcularFila(item)
        },

        calcularFila(item) {
            item.total = (parseFloat(item.precio || 0) * parseFloat(item.cantidad || 0)).toFixed(2)
        },

        openAgarradorDialog(index) {
            this.filaAgarradorIndex = index
            this.dialogAgarrador = true
        },

        onAgarradorSave(agarrador) {
            const existe = this.tiposAgarrador.find(a => a.id === agarrador.id)
            if (!existe) {
                this.$emit('agarrador-saved', agarrador)
            }

            if (this.filaAgarradorIndex !== null) {
                this.detalle[this.filaAgarradorIndex].tipo_agarradors_id = agarrador.id
            }

            this.filaAgarradorIndex = null
            this.dialogAgarrador = false

            toast.success('Agarrador guardado')
        },

        openPapelDialog(index) {
            this.filaPapelIndex = index
            this.dialogPapel = true
        },

        onPapelSave(papel) {
            const existe = this.tiposPapel.find(t => t.id === papel.id)
            if (!existe) {
                this.$emit('papel-saved', papel)
            }

            if (this.filaPapelIndex !== null) {
                this.detalle[this.filaPapelIndex].tipo_papels_id = papel.id
            }

            this.filaPapelIndex = null
            this.dialogPapel = false

            toast.success('Papel guardado')
        },

        openProductoDialog(index) {
            this.filaProductoIndex = index
            this.dialogProducto = true
        },

        onProductoSave(producto) {
            console.log('pasa aquí');
            const existe = this.productos.find(p => p.id === producto.id)
            if (!existe) {
                this.$emit('producto-saved', producto)
            }

            if (this.filaProductoIndex !== null) {
                this.detalle[this.filaProductoIndex].productos_id = producto.id
            }

            this.filaProductoIndex = null
            this.dialogProducto = false

            toast.success('Producto guardado')
        },

    },

    mounted() {
        if (!this.detalle.length) this.agregarFila()
    }
}
</script>

<style>
.ventas_table th,
.ventas_table td {
    padding: 4px 6px !important;
    vertical-align: middle;
}

/* Campo completo */
.ventas_table .v-field {
    min-height: 20px !important;
    font-size: 13px;
}

/* Área editable */
.ventas_table .v-field__input {
    padding: 2px 6px !important;
    font-size: 13px;
}

/* Select / autocomplete */
.ventas_table .v-select__selection-text {
    font-size: 13px;
}
</style>