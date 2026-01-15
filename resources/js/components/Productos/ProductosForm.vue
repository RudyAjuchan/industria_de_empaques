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
                        :items="paginas" item-title="nombre" item-value="id" :error-messages="errors.paginas_id" />
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

export default {
    name: 'ProductosForm',

    components: {
        FilePond,
        draggable,
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
            }
        }
    },

    mounted() {
        if (this.producto) {
            this.form = { ...this.producto }

            if (this.producto.imagen_principal_url) {
                this.files = [{
                    source: this.producto.imagen_principal_url,
                    options: { type: 'local' }
                }]
            }
        }

        this.fetchPaginas()

        if (this.producto) {
            this.form = {
                nombre: this.producto.nombre,
                alto: this.producto.alto,
                ancho: this.producto.ancho,
                fuelle: this.producto.fuelle,
                tipo: this.producto.tipo,
                paginas_id: this.producto.paginas_id,
            }

            // cargar imágenes existentes
            this.files = this.producto.imagenes.map(img => ({
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

            // imagen principal
            const principal = this.producto.imagenes.findIndex(i => i.is_main)
            this.mainIndex = principal !== -1 ? principal : 0
        }
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

            // imagen
            this.files.forEach((item, index) => {

                // IMAGEN EXISTENTE
                if (item.getMetadata('id')) {
                    formData.append(`imagenes_ordenadas[${index}][id]`, item.getMetadata('id'));
                    formData.append(`imagenes_ordenadas[${index}][tipo]`, 'existente');
                }

                // IMAGEN NUEVA
                if (item.file instanceof File) {
                    formData.append(`imagenes_ordenadas[${index}][file]`, item.file);
                    formData.append(`imagenes_ordenadas[${index}][tipo]`, 'nueva');
                }
            });


            formData.append('main_index', this.mainIndex)

            try {
                if (this.producto) {
                    await axios.post(
                        `/producto/${this.producto.id}?_method=PUT`,
                        formData
                    )
                } else {
                    await axios.post('/producto', formData)
                }

                this.$emit('saved')
            } catch (err) {
                const e = err.response?.data?.errors
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
        }
    },

    watch: {
        files() {
            if (this.mainIndex >= this.files.length) {
                this.mainIndex = 0
            }
        }
    }

}
</script>
