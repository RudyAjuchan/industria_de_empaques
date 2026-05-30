<template>
    <v-container fluid>
        <!-- Header -->
        <v-row class="mb-4">
            <v-col cols="12" md="6">
                <div>
                    <h2 class="text-h5 font-weight-bold">Banners</h2>
                    <div class="text-caption text-medium-emphasis">
                        Administración de banners publicitarios
                    </div>
                </div>
            </v-col>

            <v-col cols="12" md="6" class="d-flex justify-end align-center">
                <v-btn color="primary" variant="tonal" prepend-icon="mdi-plus" rounded="lg" @click="abrirDialog">
                    Nuevo Banner
                </v-btn>
            </v-col>
        </v-row>

        <!-- Tabla -->
        <v-card>
            <v-data-table :headers="headers" :items="banners" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-teal-lighten-2' }" density="compact">
                <!-- Imagen -->
                <template v-slot:[`item.imagen`]="{ item }">
                    <v-img :src="item.imagen_url" width="180" height="90" cover class="rounded-lg my-2" />
                </template>

                <!-- Tipo -->
                <template v-slot:[`item.tipo_redireccion`]="{ item }">
                    <v-chip color="primary" size="small">
                        {{ item.tipo_redireccion }}
                    </v-chip>
                </template>

                <!-- Estado -->
                <template v-slot:[`item.activo`]="{ item }">
                    <v-chip :color="item.activo ? 'success' : 'error'" size="small">
                        {{ item.activo ? "Activo" : "Inactivo" }}
                    </v-chip>
                </template>

                <!-- Acciones -->
                <template v-slot:[`item.actions`]="{ item }">
                    <v-btn icon variant="tonal" color="primary" density="compact" @click="editar(item)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>

                    <v-btn icon density="compact" color="error" variant="tonal" @click="eliminar(item)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <!-- Dialog -->
        <v-dialog v-model="dialog" max-width="800">
            <v-card rounded="xl">
                <v-card-title>
                    {{ editando ? "Editar Banner" : "Nuevo Banner" }}
                </v-card-title>

                <v-divider />

                <v-card-text class="py-6">
                    <!-- Imagen -->
                    <div class="mb-4">
                        <div class="text-subtitle-2 mb-2">Imagen banner</div>

                        <FilePond name="imagen" accepted-file-types="image/jpeg, image/png, image/webp"
                            :allow-multiple="false" @addfile="onAddImagen" />

                        <div class="text-caption text-medium-emphasis mt-1">
                            Tamaño recomendado: 2560x700 px
                        </div>
                        <v-alert v-if="errors.imagen" color="error" icon="$error"
                            :text="errors.imagen" density="compact"></v-alert>
                    </div>

                    <!-- Preview -->
                    <v-img v-if="form.preview && form.id!=null" :src="form.preview" height="220" cover class="rounded-xl mb-6" />

                    <v-row>
                        <!-- Tipo redirección -->
                        <v-col cols="12" md="6">
                            <v-select v-model="form.tipo_redireccion" :items="tiposRedireccion" item-title="title"
                                item-value="value" label="Tipo redirección" variant="outlined" />
                        </v-col>

                        <!-- Producto -->
                        <v-col cols="12" md="6" v-if="form.tipo_redireccion == 'producto'">
                            <v-autocomplete v-if="form.tipo_redireccion == 'producto'" v-model="form.producto_id"
                                v-model:search="searchProducto" :items="productos" :loading="loadingProductos"
                                item-value="id" label="Buscar producto" variant="outlined" clearable hide-no-data
                                no-filter :error-messages="errors.producto_id">

                                <!-- Item listado -->
                                <template v-slot:item="{ props, item }">
                                    <v-list-item v-bind="props" :title="item.raw.nombre" :subtitle="item.raw.codigo" />
                                </template>

                                <!-- Seleccionado -->
                                <template v-slot:selection="{ item }">
                                    <span>
                                        {{ item.raw.codigo }} - {{ item.raw.nombre }}
                                    </span>
                                </template>
                            </v-autocomplete>
                        </v-col>

                        <!-- Tipo -->
                        <v-col cols="12" md="6" v-if="form.tipo_redireccion == 'tipo'">
                            <v-select v-model="form.tipo_producto" :items="tiposProducto" label="Tipo producto"
                                variant="outlined" clearable :error-messages="errors.tipo_producto" />
                        </v-col>

                        <!-- Promoción -->
                        <v-col cols="12" md="6" v-if="form.tipo_redireccion == 'promocion'">
                            <v-autocomplete v-model="form.promocion_id" :items="promociones" item-title="nombre"
                                item-value="id" label="Promoción" variant="outlined" clearable :error-messages="errors.promocion_id"/>
                        </v-col>

                        <!-- Orden -->
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.orden" type="number" label="Orden" variant="outlined" :error-messages="errors.orden" />
                        </v-col>

                        <!-- Activo -->
                        <v-col cols="12" md="6" class="d-flex align-center">
                            <v-switch v-model="form.activo" label="Activo" color="success" />
                        </v-col>

                        <!-- Fecha inicio -->
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.fecha_inicio" type="date" label="Fecha inicio"
                                variant="outlined" />
                        </v-col>

                        <!-- Fecha fin -->
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.fecha_fin" type="date" label="Fecha fin" variant="outlined" />
                        </v-col>
                    </v-row>
                </v-card-text>

                <v-divider />

                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="error" @click="dialog = false">Cancelar</v-btn>
                    <v-btn color="success" variant="tonal" :loading="loadingGuardar" @click="guardar">
                        Guardar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import axios from "axios";

import { FilePond } from '../../plugins/filepond'
import { toast } from 'vue3-toastify'

export default {
    components: {
        FilePond,
    },

    data() {
        return {
            loading: false,
            loadingGuardar: false,

            dialog: false,
            editando: false,

            banners: [],
            promociones: [],
            tiposProducto: [],

            headers: [
                { title: "Imagen", key: "imagen" },
                { title: "Tipo redirección", key: "tipo_redireccion" },
                { title: "Orden", key: "orden" },
                { title: "Estado", key: "activo" },
                { title: "Acciones", key: "actions", sortable: false },
            ],

            tiposRedireccion: [
                { title: "Sin redirección", value: "ninguno" },
                { title: "Producto", value: "producto" },
                { title: "Tipo producto", value: "tipo" },
                { title: "Promoción", value: "promocion" },
            ],

            form: {
                id: null,
                imagen: null,
                preview: null,
                tipo_redireccion: "ninguno",
                producto_id: null,
                tipo_producto: null,
                promocion_id: null,
                orden: 0,
                activo: true,
                fecha_inicio: null,
                fecha_fin: null,
            },

            productos: [],
            loadingProductos: false,
            searchProducto: '',
            searchTimeout: null,

            tiposProducto: [],
            loadingTipos: false,

            errors: {},
        };
    },

    mounted() {
        this.obtenerBanners();
        this.obtenerPromociones();
        this.obtenerTiposProducto()
    },

    methods: {
        async obtenerBanners() {
            try {
                this.loading = true;
                const response = await axios.get("/banners");
                this.banners = response.data;
            } catch (error) {
                console.error(error);
                toast.error('Hubo un inconveniente al realizar la petición')
            } finally {
                this.loading = false;
            }
        },

        async obtenerPromociones() {
            try {
                const response = await axios.get("/promocion");
                this.promociones = response.data;
            } catch (error) {
                toast.error('Hubo un inconveniente al realizar la petición')
                console.error(error);
            }
        },

        abrirDialog() {
            this.resetForm();
            this.editando = false;
            this.dialog = true;
        },

        onAddImagen(error, fileItem) {
            if (error) return;
            const file = fileItem.file;
            this.form.imagen = file;
            this.form.preview = URL.createObjectURL(file);
        },

        async guardar() {
            try {
                this.loadingGuardar = true;

                const formData = new FormData();

                if (this.form.imagen) {
                    formData.append(
                        'imagen',
                        this.form.imagen,
                        this.form.imagen.name
                    )
                }

                formData.append("tipo_redireccion", this.form.tipo_redireccion);
                formData.append("producto_id", this.form.producto_id || "");
                formData.append("tipo_producto", this.form.tipo_producto || "");
                formData.append("promocion_id", this.form.promocion_id || "");
                formData.append("orden", this.form.orden);
                formData.append("activo", this.form.activo ? 1 : 0);
                formData.append("fecha_inicio", this.form.fecha_inicio || "");
                formData.append("fecha_fin", this.form.fecha_fin || "");

                if (this.editando) {
                    formData.append("_method", "PUT");

                    await axios.post(`/banners/${this.form.id}`, formData);
                } else {
                    await axios.post("/banners", formData);
                }
                this.dialog = false;
                this.obtenerBanners();
                toast.success('Se han guardado los cambios')
            } catch (error) {
                console.error(error);
                toast.error('Hubo un inconveniente al realizar la petición')
                this.errors = error.response?.data?.errors || {}
            } finally {
                this.loadingGuardar = false;
            }
        },

        editar(item) {
            this.editando = true;

            this.form.id = item.id;
            this.form.imagen = null;
            this.form.preview = item.imagen_url;
            this.form.tipo_redireccion = item.tipo_redireccion;
            this.form.producto_id = item.productos_id;
            this.form.tipo_producto = item.tipo_producto;
            this.form.promocion_id = item.promocion_id;
            this.form.orden = item.orden;
            this.form.activo = item.activo;
            this.form.fecha_inicio = item.fecha_inicio;
            this.form.fecha_fin = item.fecha_fin;

            this.productos = [
                    {
                        id: item.producto?.id,
                        nombre: item.producto?.nombre
                    }
                ];

            this.dialog = true;
        },

        async eliminar(item) {
            const ok = confirm("¿Eliminar banner?");
            if (!ok) return;
            try {
                await axios.delete(`/banners/${item.id}`);
                this.obtenerBanners();
                toast.error('Se ha eliminado el banner')
            } catch (error) {
                console.error(error);
            }
        },

        resetForm() {
            this.form = {
                id: null,
                imagen: null,
                preview: null,
                tipo_redireccion: "ninguno",
                producto_id: null,
                tipo_producto: null,
                promocion_id: null,
                orden: 0,
                activo: true,
                fecha_inicio: null,
                fecha_fin: null,
            };
        },

        async buscarProductos(search = '') {
            try {
                this.loadingProductos = true
                const response = await axios.get('/prod/search', {
                    params: {
                        search
                    }
                })
                this.productos = response.data
            } catch (error) {
                toast.error('Hubo un inconveniente al realizar la petición')
                console.error(error)
            } finally {
                this.loadingProductos = false
            }
        },

        async obtenerTiposProducto() {
            try {
                this.loadingTipos = true
                const response = await axios.get('/prod/tipos')
                this.tiposProducto = response.data
            } catch (error) {
                toast.error('Hubo un inconveniente al realizar la petición')
                console.error(error)
            } finally {
                this.loadingTipos = false
            }
        }
    },

    watch: {
        searchProducto(value) {
            clearTimeout(this.searchTimeout)
            this.searchTimeout = setTimeout(() => {
                this.buscarProductos(value)
            }, 1000)
        }
    }
};
</script>
