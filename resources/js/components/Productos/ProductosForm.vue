<template>
    <v-row dense>
        <v-col cols="6">
            <v-row>
                <v-col cols="12">
                    <v-text-field v-model="form.nombre" variant="outlined" density="compact" label="Nombre" required
                        :error-messages="errors.nombre" />
                </v-col>

                <v-col cols="12">
                    <v-text-field v-model="form.sku" variant="outlined" density="compact" label="SKU" required
                        :error-messages="errors.sku" />
                </v-col>

                <v-col cols="12">
                    <v-select v-model="form.tipo_producto" :items="[
                        { title: 'Personalizado', value: 'personalizado' },
                        { title: 'Simple', value: 'simple' }
                    ]" label="Tipo de producto" variant="outlined" density="compact" :error-messages="errors.tipo_producto" />
                </v-col>

                <template v-if="form.tipo_producto === 'personalizado'">
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.alto" variant="outlined" density="compact" label="Alto cm" type="number"
                            :error-messages="errors.alto" />
                    </v-col>
    
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.ancho" variant="outlined" density="compact" label="Ancho cm" type="number"
                            :error-messages="errors.ancho" />
                    </v-col>
    
                    <v-col cols="12" md="4">
                        <v-text-field v-model="form.fuelle" variant="outlined" density="compact" label="Fuelle cm"
                            type="number" :error-messages="errors.fuelle" />
                    </v-col>
                </template>

                <v-col cols="12" v-if="form.tipo_producto === 'simple'">
                    <MoneyInput v-model="form.precio_base" label="Precio unidad" variant="outlined" density="compact" />
                </v-col>

                <v-col cols="12">
                    <v-textarea v-model="form.descripcion" rows="2" variant="outlined" density="compact" :error-messages="errors.descripcion"
                        label="Descripción" />
                </v-col>

                <v-col cols="12">
                    <v-switch label="¿Ecommerce?" color="secondary" v-model="form.ecommerce"></v-switch>
                </v-col>

                <v-col cols="12">
                    <v-autocomplete v-model="form.tipo_productos_id" variant="outlined" density="compact"
                        :items="tiposProducto" item-title="nombre" item-value="id" :error-messages="errors.tipo_productos_id"
                        label="Tipo">
                        <template #append-inner>
                            <v-btn icon size="small" variant="text" @click.stop="dialogTipoProducto = true">
                                <v-icon size="18">mdi-plus</v-icon>
                            </v-btn>
                        </template>

                        <template #item="{ props, item }">
                            <v-list-item v-bind="props">
                                <template #subtitle v-if="item.raw.codigo">
                                    Código: {{ item.raw.codigo }}
                                </template>
                            </v-list-item>
                        </template>
                    </v-autocomplete>
                    <TipoProductoDialog v-model="dialogTipoProducto" @saved="onTipoProductoSave" />
                </v-col>

                <v-col cols="12">
                    <div class="text-subtitle-2 mb-2">
                        Estados de producción
                    </div>
                    <v-alert v-if="errors.estados_produccion" type="error" variant="tonal" density="compact"
                        class="mb-2">
                        {{ errors.estados_produccion }}
                    </v-alert>

                    <draggable v-model="form.estados_produccion" item-key="id" handle=".drag-handle">
                        <template #item="{ element, index }">
                            <v-card class="mb-2 pa-2">
                                <div class="d-flex align-center">

                                    <v-icon class="mr-2 drag-handle">
                                        mdi-drag
                                    </v-icon>

                                    <v-select v-model="element.id" :items="getEstadosDisponibles(index)"
                                        item-title="nombre" item-value="id" density="compact" variant="outlined"
                                        hide-details class="flex-grow-1" />

                                    <v-btn icon size="small" color="error" variant="text" @click="removeEstado(index)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </div>
                            </v-card>
                        </template>
                    </draggable>

                    <v-btn color="primary" variant="tonal" size="small" @click="addEstado">
                        Agregar estado
                    </v-btn>
                </v-col>
            </v-row>
        </v-col>

        <v-col cols="6">
            <v-row>
                <v-col cols="12">
                    <!-- FILEPOND -->
                    <file-pond name="imagenes" ref="pond" label-idle="Arrastra tus imágenes..." :allow-multiple="true"
                        :server="server" accepted-file-types="image/jpeg, image/png, image/webp" :files="files"
                        @updatefiles="handleFilePond" label-max-file-size-exceeded="El archivo es demasiado grande"
                        label-max-file-size="Máximo permitido: {filesize}" max-file-size="50MB" />
                    <v-alert v-if="errors.imagenes_ordenadas" type="error" variant="tonal" density="compact"
                        class="mt-2">
                        {{ errors.imagenes_ordenadas }}
                    </v-alert>
                </v-col>
            </v-row>
            <v-col cols="12">
                <draggable v-model="files" item-key="id" class="v-row mt-3">
                    <template #item="{ element, index }">
                        <v-col cols="4">
                            <v-card outlined class="pa-2" :color="index === mainIndex ? 'green-lighten-4' : ''">
                                <v-img :src="filePreview(element)" height="120" contain />
    
                                <v-btn block size="small" color="success" variant="tonal" class="mt-2"
                                    @click="setMain(index)">
                                    {{ index === mainIndex ? 'Principal' : 'Marcar principal' }}
                                </v-btn>
                            </v-card>
                        </v-col>
                    </template>
                </draggable>
            </v-col>
        </v-col>
    </v-row>

    <v-divider class="my-4" />

    <v-row justify="end" class="ga-2">
        <v-btn variant="tonal" color="error" type="button" @click="$emit('cancel')">
            Cancelar
        </v-btn>
        <v-btn color="success" variant="tonal" type="submit" :loading="loading" @click="submit">
            Guardar
        </v-btn>
    </v-row>
</template>

<script>
import axios from 'axios'
import { FilePond } from '../../plugins/filepond'
import draggable from 'vuedraggable'
import TipoProductoDialog from './TipoProductoDialog.vue'
import MoneyInput from '../common/MoneyInput.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'ProductosForm',

    components: {
        FilePond,
        draggable,
        TipoProductoDialog,
        MoneyInput,
    },

    emits: ['saved', 'cancel'],

    props: {
        producto: {
            type: Object,
            default: null
        }
    },

    data() {
        return {
            loading: false,
            files: [],
            form: {
                nombre: '',
                sku: '',
                alto: '',
                ancho: '',
                fuelle: '',
                tipo_productos_id: null,
                // NUEVOS
                tipo_producto: 'personalizado',
                precio_base: null,
                descripcion: '',
                ecommerce: false,
                estados_produccion: [],
            },
            errors: {},
            tiposProducto: [],
            estadosProduccionDisponibles: [],
            mainIndex: 0,
            server: {
                load: (source, load, error, progress, abort) => {
                    fetch(source)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('No se pudo cargar la imagen')
                            }
                            return response.blob()
                        })
                        .then(blob => {
                            load(blob)
                        })
                        .catch(() => {
                            error('Error al cargar imagen')
                        })
                }
            },
            dialogTipoProducto: false,
        }
    },

    mounted() {
        this.fetchTiposProducto();
        this.fetchEstadosProduccion();
    },

    methods: {
        async submit() {
            this.loading = true
            const formData = new FormData()

            Object.entries(this.form).forEach(([key, value]) => {
                if (Array.isArray(value)) return

                if (key === 'precio_base' && value !== null) {
                    value = value.toString().replace(',', '.')
                }

                if (value !== null && value !== '') {
                    formData.append(key, value)
                }
            })

            this.form.estados_produccion.forEach((estado, index) => {
                formData.append(
                    `estados_produccion[${index}][id]`,
                    estado.id
                )

                formData.append(
                    `estados_produccion[${index}][orden]`,
                    index + 1
                )
            })

            this.files.forEach((item, index) => {
                if (item.getMetadata?.('id')) {
                    formData.append(`imagenes_ordenadas[${index}][id]`, item.getMetadata('id'))
                    formData.append(`imagenes_ordenadas[${index}][tipo]`, 'existente')
                }

                if (item.file instanceof File) {
                    formData.append(`imagenes_ordenadas[${index}][file]`, item.file)
                    formData.append(`imagenes_ordenadas[${index}][tipo]`, 'nueva')
                }
            })

            formData.append('main_index', this.mainIndex)

            try {
                let response

                if (this.producto) {
                    response = await axios.post(
                        `/producto/${this.producto.id}?_method=PUT`,
                        formData
                    )
                } else {
                    response = await axios.post('/producto', formData)
                }

                this.$emit('saved', response.data)

            } catch (err) {
                toast.error(err.response?.data?.message || 'Hubo un inconveniente al realizar la petición')
                this.errors = err.response?.data?.errors || {}
                const errorKeys = Object.keys(this.errors)

                if (errorKeys.some(key => key.startsWith('estados_produccion.'))) {
                    this.errors.estados_produccion = 'Selecciona al menos un estado de producción válido'
                }

                if (errorKeys.some(key => key.startsWith('imagenes_ordenadas.'))) {
                    this.errors.imagenes_ordenadas = 'Una o más imágenes no cumplen con el formato permitido'
                }
            } finally {
                this.loading = false
            }
        },

        async fetchTiposProducto() {
            const { data } = await axios.get('/producto/tipos')
            this.tiposProducto = data
        },

        handleFilePond(fileItems) {
            this.files = fileItems
        },

        filePreview(item) {
            if (item.file instanceof File) {
                return URL.createObjectURL(item.file)
            }

            if (typeof item.source === 'string') {
                return item.source
            }

            return ''
        },

        setMain(index) {
            this.mainIndex = index
        },

        onTipoProductoSave(tipoProducto) {
            this.tiposProducto.push(tipoProducto)
            this.form.tipo_productos_id = tipoProducto.id
            toast.success('Tipo guardado')
        },

        async fetchEstadosProduccion() {
            const { data } = await axios.get('/producto/estado-produccion')
            this.estadosProduccionDisponibles = data
        },

        addEstado() {
            this.form.estados_produccion.push({
                id: null
            })
        },

        removeEstado(index) {
            this.form.estados_produccion.splice(index, 1)
        },

        getEstadosDisponibles(currentIndex) {

            // IDs ya seleccionados
            const seleccionados = this.form.estados_produccion
                .map((e, index) => {
                    // excluir el select actual
                    if (index !== currentIndex) {
                        return e.id
                    }

                    return null
                })
                .filter(Boolean)

            // devolver solo disponibles
            return this.estadosProduccionDisponibles.filter(
                estado => !seleccionados.includes(estado.id)
            )
        }
    },

    watch: {
        producto: {
            immediate: true,
            handler(producto) {
                if (!producto) {
                    // modo crear
                    this.form = {
                        nombre: '',
                        sku: '',
                        alto: '',
                        ancho: '',
                        fuelle: '',
                        tipo_productos_id: null,
                        tipo_producto: 'personalizado',
                        precio_base: null,
                        descripcion: '',
                        ecommerce: false,
                        estados_produccion: [],
                    }
                    this.files = []
                    this.mainIndex = 0
                    return
                }

                // modo editar
                this.form = {
                    nombre: producto.nombre,
                    sku: producto.sku,
                    alto: producto.alto,
                    ancho: producto.ancho,
                    fuelle: producto.fuelle,
                    tipo_productos_id: producto.tipo_productos_id,

                    tipo_producto: producto.tipo_producto || 'personalizado',
                    precio_base: producto.precio_base,
                    descripcion: producto.descripcion,
                    ecommerce: producto.ecommerce === 1 ? true: false,
                    estados_produccion: producto.estados_produccion
                        ? producto.estados_produccion.map(e => ({
                            id: e.id,
                            orden: e.pivot.orden
                        }))
                        : [],
                }

                this.files = producto.imagenes.map(img => ({
                    source: img.url,
                    options: {
                        type: 'local',
                        metadata: {
                            id: img.id,
                            orden: img.orden,
                            is_main: img.is_main
                        }
                    }
                }))

                const principal = producto.imagenes.findIndex(i => i.is_main)
                this.mainIndex = principal !== -1 ? principal : 0
            }
        },

        files() {
            if (this.mainIndex >= this.files.length) {
                this.mainIndex = 0
            }
        }
    }


}
</script>
