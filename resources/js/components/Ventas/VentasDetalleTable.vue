<template>
    <v-card class="mt-4">

        <v-card-title>
            Detalle de Venta
            <v-spacer />
            <v-btn size="small" color="primary" @click="agregarFila" :loading="loading">
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
                    <th style="min-width: 220px">Observaciones</th>
                    <th style="min-width: 180px">Agarrador</th>
                    <th style="min-width: 180px">Papel</th>
                    <th style="min-width: 160px">Nom.Log</th>
                    <th style="min-width: 75px">Promoción aplicada</th>
                    <th style="min-width: 100px">Precio</th>
                    <th style="min-width: 75px">Cantidad</th>
                    <th style="min-width: 120px">Total</th>
                    <th style="min-width: 200px">Montaje</th>
                    <th style="min-width: 100px">Archivo</th>
                    <th>Eliminar</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(item, index) in detalle" :key="index">

                    <td>
                        <v-autocomplete :items="productos" :item-title="productoTitle" item-value="id"
                            v-model="item.productos_id" @update:modelValue="actualizarProducto(item)" dense
                            hide-details="auto" density="compact" variant="outlined" no-filter
                            label="SKU, producto, tipo o página" :loading="productosLoading"
                            no-data-text="Escribe para buscar productos"
                            :error-messages="fieldError(index, 'productos_id')"
                            @update:search="buscarProductos">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openProductoDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-autocomplete>
                    </td>

                    <td v-if="item.producto?.tipo_producto === 'personalizado'"><v-text-field v-model="item.alto" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td v-else>-</td>
                    <td v-if="item.producto?.tipo_producto === 'personalizado'"><v-text-field v-model="item.ancho" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td v-else>-</td>
                    <td v-if="item.producto?.tipo_producto === 'personalizado'"><v-text-field v-model="item.fuelle" readonly dense hide-details disabled density="compact" variant="outlined"/></td>
                    <td v-else>-</td>
                    <td><v-text-field v-model="item.tipo" readonly dense hide-details disabled density="compact" variant="outlined"/></td>

                    <td v-if="item.producto?.tipo_producto === 'personalizado'"><v-text-field v-model="item.color_agarrador" dense hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'color_agarrador')"/></td>
                    <td v-else>-</td>
                    <td><v-text-field v-model="item.detalle_impresion" dense hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'detalle_impresion')"/></td>
                    <td><v-textarea v-model="item.observaciones" dense hide-details="auto" density="compact" variant="outlined" rows="1" auto-grow :error-messages="fieldError(index, 'observaciones')"/></td>
                    <td v-if="item.producto?.tipo_producto === 'personalizado'">
                        <v-select :items="tiposAgarrador" item-title="nombre" item-value="id"
                            v-model="item.tipo_agarradors_id" dense hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'tipo_agarradors_id')">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openAgarradorDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-select>
                    </td>
                    <td v-else>-</td>

                    <td v-if="item.producto?.tipo_producto === 'personalizado'">
                        <v-select :items="tiposPapel" item-title="nombre" item-value="id" v-model="item.tipo_papels_id"
                            dense hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'tipo_papels_id')">
                            <template #append-inner>
                                <v-btn icon size="small" variant="text" @click.stop="openPapelDialog(index)">
                                    <v-icon size="18">mdi-plus</v-icon>
                                </v-btn>
                            </template>
                        </v-select>

                    </td>
                    <td v-else>-</td>



                    <td><v-text-field v-model="item.nombre_logo" dense hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'nombre_logo')"/></td>
                    <td>
                        <div v-if="item.promocion_aplicada">
                            <span style="color:red; font-size:11px">{{ item.promocion_aplicada.nombre }}</span><br>
                            -<span>{{ item.promocion_aplicada.tipo == 'porcentaje' ?  `${item.promocion_aplicada.valor}%` : formatQuetzales(item.promocion_aplicada.valor) }}</span>
                        </div>
                    </td>
                    <td>
                        <v-text-field v-model="item.precio" @input="onChangeItem(item)" dense
                            hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'precio')" :disabled="item.producto?.tipo_producto === 'simple'"/>
                    </td>

                    <td>
                        <v-text-field v-model="item.cantidad" @input="onChangeItem(item)" dense
                            hide-details="auto" density="compact" variant="outlined" :error-messages="fieldError(index, 'cantidad')"/>
                    </td>

                    <td>
                        <v-text-field :model-value="item.total" readonly dense hide-details density="compact" variant="outlined" disabled/>
                    </td>

                    <td>
                        <div class="d-flex flex-wrap" style="gap:6px">

                            <!-- PREVIEWS -->
                            <div v-for="(img, i) in item.imagenes" :key="i" style="position:relative">

                                <v-img :src="img.preview || img.url" width="60" height="60" cover class="rounded" />

                                <v-btn icon size="x-small" color="error" style="position:absolute; top:-6px; right:-6px"
                                    @click="removeImagen(index, i)" :loading="loading">
                                    <v-icon size="12">mdi-close</v-icon>
                                </v-btn>
                            </div>

                            <!-- botón subir -->
                            <v-btn size="small" color="primary" variant="outlined" @click="abrirImagenes(index)" :loading="loading">
                                Subir
                            </v-btn>

                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column" style="gap:6px">

                            <!-- Si ya hay archivo -->
                            <div v-if="item.archivo_diseno_file" class="d-flex align-center" style="gap:6px">

                                <span style="font-size:12px; max-width:120px; overflow:hidden; text-overflow:ellipsis;">
                                    {{ item.archivo_diseno_file.name }}
                                </span>
                            </div>

                            <div v-if="item.archivo_diseno_file">

                                <!-- nombre -->
                                <div style="font-size:12px">
                                    {{ item.archivo_diseno_file.name }}
                                </div>

                                <!-- progreso -->
                                <v-progress-linear v-if="item.upload_status === 'subiendo'"
                                    :model-value="item.upload_progress" height="6" color="blue" striped class="mt-1" />

                                <!-- estado -->
                                <div style="font-size:11px">

                                    <span v-if="item.upload_status === 'subiendo'">
                                        Subiendo... {{ item.upload_progress }}%
                                    </span>

                                    <span v-if="item.upload_status === 'completado'" style="color:green">
                                        ✔ Subido
                                    </span>

                                    <span v-if="item.upload_status === 'error'" style="color:red">
                                        ✖ Error
                                    </span>

                                </div>
                                
                                <v-btn icon size="x-small" color="error" @click="removeDiseno(index)" :loading="loading">
                                    <v-icon size="12">mdi-close</v-icon>
                                </v-btn>
                                <!-- acciones -->
                                <div v-if="item.upload_status === 'error'">
                                    <!-- eliminar -->
                                    <v-btn size="x-small" color="orange" @click="$emit('retry-upload', index)" :loading="loading">
                                        Reintentar
                                    </v-btn>
                                </div>

                            </div>

                            <!-- botón subir -->
                            <v-btn size="small" color="purple" prepend-icon="mdi-paperclip" @click="abrirDiseno(index)" :loading="loading">
                                Subir
                            </v-btn>

                        </div>
                    </td>

                    <td class="text-center">
                        <v-btn icon size="small" color="error" @click="eliminarFila(index)" :loading="loading" :disabled="modo=='editar'">
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
    <v-dialog v-model="dialogImagenes" max-width="500">
        <v-card>
            <v-card-title>Subir Imágenes</v-card-title>

            <v-card-text>
                <FilePond name="imagenes" allow-multiple="true" accepted-file-types="image/jpeg, image/png, image/webp"
                    @addfile="onAddImagen" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn text @click="dialogImagenes = false">Cerrar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <input type="file" ref="inputDiseno" style="display:none" accept=".psd,.zip,.rar" @change="onSelectDiseno" />
</template>


<script>
import AgarradorDialog from '../Agarradores/AgarradorDialog.vue'
import PapelDialog from '../TipoPapel/TipoPapelDialog.vue'
import ProductoDialog from '../Productos/ProductosDialog.vue'
import { toast } from 'vue3-toastify'
import { FilePond } from '../../plugins/filepond'
export default {
    props: {
        modelValue: Array,
        productos: Array,
        tiposAgarrador: Array,
        tiposPapel: Array,
        errors: Object,
        modo: String,
        loading: Boolean,
        productosLoading: Boolean,
    },
    components:{
        AgarradorDialog,
        PapelDialog,
        ProductoDialog,
        FilePond,
    },

    emits: ['update:modelValue', 'producto-saved', 'agarrador-saved', 'papel-saved', 'retry-upload', 'search-productos'],

    data() {
        return {
            detalle: this.modelValue || [],
            dialogAgarrador: false,
            filaAgarradorIndex: null,
            dialogPapel: false,
            filaPapelIndex: null,
            dialogProducto: false,
            filaProductoIndex: null,
            dialogLogo: false,
            filaLogoIndex: null,
            dialogImagenes: false,
            filaImagenIndex: null,
            filaDisenoIndex: null,
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
                uuid: crypto.randomUUID(),
                productos_id: null,
                tipo_agarradors_id: null,
                tipo_papels_id: null,
                color_agarrador: '',
                detalle_impresion: '',
                observaciones: '',
                nombre_logo: '',
                precio: 0,
                cantidad: 1,
                total: 0,
                alto: '',
                ancho: '',
                fuelle: '',
                tipo: '',
                producto: null,
                imagenes: [],
                archivo_diseno_file: null,
            })
        },

        eliminarFila(index) {
            this.detalle.splice(index, 1)
        },

        async actualizarProducto(item) {

            const producto = this.productos.find(p => p.id === item.productos_id)

            if (!producto) {
                item.promocion_aplicada = null
                this.calcularFila(item)
                return
            }

            // Guardamos referencia completa
            item.producto = producto

            // ===============================
            // DATOS GENERALES DEL PRODUCTO
            // ===============================
            item.alto = producto.alto
            item.ancho = producto.ancho
            item.fuelle = producto.fuelle
            item.tipo = producto.tipo

            // ===============================
            // LÓGICA POR TIPO DE PRODUCTO
            // ===============================
            if (producto.tipo_producto === 'simple') {

                // limpiar campos que no aplican
                item.tipo_agarradors_id = null
                item.tipo_papels_id = null
                item.color_agarrador = null
                item.detalle_impresion = null
                item.nombre_logo = null

                // precio automático
                item.precio = producto.precio_base

            } else {

                // si cambia a personalizado, permitir edición
                if (!item.precio || item.precio === producto.precio_base) {
                    item.precio = 0
                }
            }

            // ===============================
            // MODO EDICIÓN
            // ===============================
            if (this.modo === 'editar') {
                this.calcularFila(item)
                return
            }

            // ===============================
            // PROMOCIONES
            // ===============================
            item.promocion_aplicada = await this.obtenerPromocion(item)

            // ===============================
            // RECALCULAR
            // ===============================
            this.calcularFila(item)
        },

        productoTitle(producto) {
            if (!producto) return ''

            return [
                producto.sku,
                producto.nombre,
                producto.tipo,
                producto.tipo_catalogo?.nombre,
                producto.paginas?.nombre,
            ].filter(Boolean).join(' - ')
        },

        buscarProductos(search) {
            this.$emit('search-productos', search || '')
        },

        calcularFila(item) {
            const precio = parseFloat(item.precio || 0)
            const cantidad = parseFloat(item.cantidad || 0)

            let total = precio * cantidad

            if (item.promocion_aplicada) {
                if (item.promocion_aplicada.tipo === 'porcentaje') {
                    total -= total * (item.promocion_aplicada.valor / 100)
                } else {
                    total -= item.promocion_aplicada.valor
                }
            }

            item.total = total.toFixed(2)
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
            //console.log('pasa aquí');
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

        fieldError(index, field) {
            return this.errors?.[`detalle.${index}.${field}`] ?? []
        },
        getLogoUrl(path) {
            //console.log(path);
            //console.log(import.meta.env.VITE_API_URL)
            return `${import.meta.env.VITE_API_URL}/storage/${path}`
        },
        descargarLogo(path) {
            const url = this.getLogoUrl(path)

            const link = document.createElement('a')
            link.href = url
            link.download = path.split('/').pop()
            link.click()
        },

        subirLogo(index) {
            this.filaLogoIndex = index
            this.dialogLogo = true
        },
        async onFileUpload(error, file) {
            if (error) return

            try {

                const formData = new FormData()
                formData.append('logo', file.file)

                const res = await axios.post('/api/ecommerce/upload-logo', formData)

                // Lógica para la imagen
                if (this.filaLogoIndex !== null) {
                    this.detalle[this.filaLogoIndex].logo_path = res.data.path
                }

                this.dialogLogo = false

            } catch (err) {
                console.error(err)
            }
        },

        async onChangeItem(item) {

            if (this.modo === 'editar') {
                this.calcularFila(item)
                return
            }

            if (!item.productos_id) {
                item.promocion_aplicada = null
                this.calcularFila(item)
                return
            }

            item.promocion_aplicada = await this.obtenerPromocion(item)

            this.calcularFila(item)
        },

        formatQuetzales(value){
            if (value === null || value === undefined || isNaN(value)) {
                return 'Q 0.00';
            }

            return new Intl.NumberFormat('es-GT', {
                style: 'currency',
                currency: 'GTQ',
                minimumFractionDigits: 2
            }).format(value);
        },

        async obtenerPromocion(item) {
            try {
                const { data } = await axios.post('/api/ecommerce/validar-promociones', {
                    items: [
                        {
                            uuid: item.uuid,
                            productos_id: item.productos_id
                        }
                    ]
                })

                const res = data.find(r => r.uuid === item.uuid)

                return res?.promocion || null

            } catch (e) {
                console.error(e)
                return null
            }
        },

        abrirImagenes(index) {
            this.filaImagenIndex = index
            this.dialogImagenes = true
        },

        onAddImagen(error, fileItem) {
            if (error) return;

            const file = fileItem.file

            const previewUrl = URL.createObjectURL(file)

            this.detalle[this.filaImagenIndex].imagenes.push({
                file: file,
                preview: previewUrl
            });
        },

        async removeImagen(index, i) {
            const img = this.detalle[index].imagenes[i]
            /*
            |--------------------------------------------------------------------------
            | SI YA EXISTE EN S3
            |--------------------------------------------------------------------------
            */
            if (img.uploaded) {

                try {
                    await axios.post('/venta/delete-imagen',{ path: img.path })
                } catch (e) {
                    console.error(e)
                    toast.error('No se pudo eliminar')
                    return
                }
            }

            /*
            |--------------------------------------------------------------------------
            | LIBERAR MEMORIA
            |--------------------------------------------------------------------------
            */
            if (img.preview?.startsWith('blob:')) {
                URL.revokeObjectURL(
                    img.preview
                )
            }

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR ARRAY
            |--------------------------------------------------------------------------
            */
            this.detalle[index]
                .imagenes
                .splice(i, 1)
        },

        getPreview(img) {
            return img.preview || ''
        },

        abrirDiseno(index) {
            this.filaDisenoIndex = index
            this.$refs.inputDiseno.click()
        },

        onSelectDiseno(e) {
            const file = e.target.files[0]

            if (!file) return

            // guardar archivo en el item
            this.detalle[this.filaDisenoIndex].archivo_diseno_file = file

            // limpiar input (importante)
            e.target.value = null
        },

        removeDiseno(index) {
            this.detalle[index].archivo_diseno_file = null
        }

    },

    beforeUnmount() {
        this.detalle.forEach(item => {
            item.imagenes.forEach(img => {
                if (img.preview) URL.revokeObjectURL(img.preview);
            });
        });
    },

    mounted() {
        this.detalle.forEach(item => {
            if (!item.uuid) {
                item.uuid = crypto.randomUUID()
            }
        })

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
