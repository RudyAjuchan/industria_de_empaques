<template>
    <v-row dense>
        <v-col cols="7">
            <v-row>
                <v-col cols="12">
                    <v-text-field v-model="form.nombre" variant="outlined" density="compact" label="Nombre" required
                        :error-messages="errors.nombre" />
                </v-col>

                <v-col cols="12" md="4">
                    <v-text-field v-model="form.alto" variant="outlined" density="compact" label="Alto" type="number"
                        :error-messages="errors.alto" />
                </v-col>

                <v-col cols="12" md="4">
                    <v-text-field v-model="form.ancho" variant="outlined" density="compact" label="Ancho" type="number"
                        :error-messages="errors.ancho" />
                </v-col>

                <v-col cols="12" md="4">
                    <v-text-field v-model="form.fuelle" variant="outlined" density="compact" label="Fuelle"
                        type="number" :error-messages="errors.fuelle" />
                </v-col>

                <v-col cols="12">
                    <v-text-field v-model="form.tipo" variant="outlined" density="compact" :error-messages="errors.tipo"
                        label="Tipo" />
                </v-col>

                <v-col cols="12">
                    <v-autocomplete v-model="form.paginas_id" variant="outlined" label="Página" density="compact"
                        :items="paginas" item-title="nombre" item-value="id" :error-messages="errors.paginas_id">
                        <template #append-inner>
                            <v-btn icon size="small" variant="text" @click.stop="dialogPagina = true">
                                <v-icon size="18">mdi-plus</v-icon>
                            </v-btn>
                        </template>
                    </v-autocomplete>
                    <DialogPagina v-model="dialogPagina" @saved="onPaginaSave"></DialogPagina>
                </v-col>

                <v-col cols="12">
                    <draggable v-model="files" item-key="id" class="v-row mt-3">
                        <template #item="{ element, index }">
                            <v-col cols="4">
                                <v-card outlined class="pa-2" :color="index === mainIndex ? 'green-lighten-4' : ''">
                                    <v-img :src="filePreview(element)" height="120" contain />

                                    <v-btn block size="small" color="green" variant="tonal" class="mt-2"
                                        @click="setMain(index)">
                                        {{ index === mainIndex ? 'Principal' : 'Marcar principal' }}
                                    </v-btn>
                                </v-card>
                            </v-col>
                        </template>
                    </draggable>
                </v-col>
            </v-row>
        </v-col>

        <v-col cols="5">
            <!-- FILEPOND -->
            <file-pond name="imagenes" ref="pond" label-idle="Arrastra tus imágenes..." :allow-multiple="true" :server="server"
                accepted-file-types="image/jpeg, image/png, image/webp" :files="files" @updatefiles="handleFilePond" />
        </v-col>
    </v-row>

    <v-divider class="my-4" />

    <v-row justify="end" class="ga-2">
        <v-btn variant="tonal" color="red" type="button" @click="$emit('cancel')">
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
import DialogPagina from '../Paginas/PaginaDialog.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'ProductosForm',

    components: {
        FilePond,
        draggable,
        DialogPagina,
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
                alto: '',
                ancho: '',
                fuelle: '',
                tipo: '',
                paginas_id: null,
            },
            errors: {},
            paginas: [],
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
            dialogPagina: false,
        }
    },

    mounted() {
        this.fetchPaginas();
    },

    methods: {
        handleFilePond(files) {
            this.files = files
        },

        async submit() {
            this.loading = true
            const formData = new FormData()

            Object.entries(this.form).forEach(([key, value]) => {
                if (value !== null) {
                    formData.append(key, value)
                }
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
                this.errors = err.response?.data?.errors || {}
            } finally {
                this.loading = false
            }
        },

        async fetchPaginas() {
            const { data } = await axios.get(`/producto/paginas/`)
            this.paginas = data
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

        onPaginaSave(pagina) {
            this.paginas.push(pagina)
            this.form.paginas_id = pagina.id
            toast.success('Pagina guardado')
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
                        alto: '',
                        ancho: '',
                        fuelle: '',
                        tipo: '',
                        paginas_id: null,
                    }
                    this.files = []
                    this.mainIndex = 0
                    return
                }

                // modo editar
                this.form = {
                    nombre: producto.nombre,
                    alto: producto.alto,
                    ancho: producto.ancho,
                    fuelle: producto.fuelle,
                    tipo: producto.tipo,
                    paginas_id: producto.paginas_id,
                }

                this.files = producto.imagenes.map(img => ({
                    source: `/storage/${img.path}`,
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
