<template>
    <v-form @submit.prevent="submit">
        <v-row>
            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.nombre" label="Nombre" required
                    :error-messages="errors.nombre" />
            </v-col>

            <v-col cols="12" md="6">
                <v-select variant="outlined" density="compact" v-model="form.genero" :items="['Masculino', 'Femenino']"
                    label="Género" required :error-messages="errors.genero" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.dpi" label="DPI"
                    :error-messages="errors.dpi" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.nit" label="NIT" />
            </v-col>

            <v-col cols="12" md="6" v-if="form.pais === 'Guatemala'">
                <!-- Departamento -->
                <v-select v-model="selectedDepartamento" :items="departamentos" item-title="nombre" item-value="id"
                    label="Departamento" variant="outlined" density="compact" />
            </v-col>

            <v-col cols="12" md="6" v-if="form.pais === 'Guatemala'">
                <!-- Municipio -->
                <v-select v-model="form.municipios_id" :items="municipios" item-title="nombre" item-value="id"
                    label="Municipio" variant="outlined" :disabled="!selectedDepartamento" density="compact" />
            </v-col>

            <v-col cols="12" md="6" v-if="form.pais !== 'Guatemala'">
                <!-- Estado si no es de guatemala -->
                <v-text-field v-model="form.estado_pais" label="Departamento/Estado/Provincia" variant="outlined"
                    density="compact" />
            </v-col>

            <v-col cols="12" md="6" v-if="form.pais !== 'Guatemala'">
                <!-- Ciudad -->
                <v-text-field v-model="form.ciudad_pais" label="Municipio/Ciudad" variant="outlined"
                    density="compact" />
            </v-col>

            <v-col cols="6">
                <v-textarea variant="outlined" density="compact" v-model="form.direccion" label="Dirección" rows="2" />
            </v-col>
            <v-col cols="6">
                <v-autocomplete variant="outlined" density="compact" v-model="form.pais" label="País"
                    :items="phoneCountries" item-title="name" item-value="name">
                    <template #item="{ props, item }">
                        <v-list-item v-bind="props">
                            <template #prepend>
                                <v-avatar size="20">
                                    <v-img :src="item.raw.flag" aspect-ratio="1" cover>
                                        <template #error>
                                            <v-icon size="16">mdi-flag-outline</v-icon>
                                        </template>
                                    </v-img>
                                </v-avatar>
                            </template>
                        </v-list-item>
                    </template>

                    <template #selection="{ item }">
                        <div class="d-flex align-center ga-2">
                            <v-avatar size="18">
                                <v-img :src="item.raw.flag">
                                    <template #error>
                                        <v-icon size="14">mdi-flag-outline</v-icon>
                                    </template>
                                </v-img>
                            </v-avatar>
                            <span>{{ item.raw.name }}</span>
                        </div>
                    </template>
                </v-autocomplete>
            </v-col>

            <v-col cols="12" md="6">
                <v-row>
                    <v-col cols="4">
                        <v-autocomplete v-model="form.telefono_pais_iso" :items="phoneCountries" item-title="name"
                            item-value="iso" label="Codigo" variant="outlined" density="compact">
                            <template #item="{ props, item }">
                                <v-list-item v-bind="props">
                                    <template #prepend>
                                        <v-avatar size="20">
                                            <v-img :src="item.raw.flag" aspect-ratio="1" cover>
                                                <template #error>
                                                    <v-icon size="16">mdi-flag-outline</v-icon>
                                                </template>
                                            </v-img>
                                        </v-avatar>
                                    </template>

                                    <v-list-item-title>
                                        {{ item.raw.code }}
                                    </v-list-item-title>
                                </v-list-item>
                            </template>

                            <template #selection="{ item }">
                                <div class="d-flex align-center ga-2">
                                    <v-avatar size="18">
                                        <v-img :src="item.raw.flag">
                                            <template #error>
                                                <v-icon size="14">mdi-flag-outline</v-icon>
                                            </template>
                                        </v-img>
                                    </v-avatar>
                                    <span>{{ item.raw.code }}</span>
                                </div>
                            </template>
                        </v-autocomplete>
                    </v-col>

                    <v-col cols="8">
                        <v-text-field variant="outlined" density="compact" v-model="form.telefono_numero"
                            label="Teléfono" append-inner-icon="mdi-plus" @click:append-inner="addTelefono" />
                    </v-col>

                    <v-col cols="12" v-if="form.telefonos.length">
                        <div class="d-flex flex-wrap ga-2">
                            <v-chip v-for="(t, i) in form.telefonos" :key="i" closable @click:close="removeTelefono(i)">
                                {{ t.telefono_codigo_pais }} {{ t.telefono_numero }}
                            </v-chip>
                        </div>
                        <div>
                            <v-alert v-if="errors.telefonos" type="error" variant="tonal" density="compact"
                                class="mt-2">
                                {{ errors.telefonos }}
                            </v-alert>
                        </div>
                    </v-col>
                </v-row>

            </v-col>

            <v-col cols="12" md="6">
                <v-text-field v-model="newEmail" label="Correo electrónico" append-inner-icon="mdi-plus"
                    @click:append-inner="addEmail" variant="outlined" density="compact" />

                <v-chip v-for="(email, i) in form.emails" :key="i" closable @click:close="removeEmail(i)">
                    {{ email }}
                </v-chip>
                <div>
                    <v-alert v-if="errors.emails" type="error" variant="tonal" density="compact" class="mt-2">
                        {{ errors.emails }}
                    </v-alert>
                </div>
            </v-col>

        </v-row>

        <v-row>
            <v-col cols="12" class="d-flex justify-end ga-2">
                <v-btn variant="tonal" @click="$emit('cancel')" color="red">
                    Cancelar
                </v-btn>
                <v-btn color="success" variant="tonal" type="submit" :loading="saving">
                    Guardar
                </v-btn>
            </v-col>
        </v-row>
    </v-form>
</template>

<script>
import { toast } from 'vue3-toastify'
import { getPhoneCountries } from '../../utils/phoneCountries'

export default {
    name: 'ClienteForm',

    props: {
        cliente: {
            type: Object,
            default: null,
        },
    },

    emits: ['saved', 'cancel'],

    data() {
        return {
            saving: false,
            form: {
                id: null,
                nombre: '',
                genero: '',
                dpi: '',
                departamento_id: '',
                municipio: '',
                direccion: '',
                nit: '',
                telefono_pais_iso: 'GT',
                municipios_id: null,
                pais: 'Guatemala',
                estado_pais: '',
                ciudad_pais: '',
                emails: [],
                telefonos: [],
            },
            errors: {
                emails: null,
                telefonos: null,
            },
            phoneCountries: [],
            departamentos: [],
            municipios: [],
            selectedDepartamento: null,
            newEmail: '',
            newTelefono: {
                telefono_pais: 'GT',
                telefono_codigo_pais: '',
                telefono_numero: ''
            }
        }
    },

    watch: {
        cliente: {
            immediate: true,
            async handler(cliente) {
                if (!cliente) return
                let selected = null;
                if (cliente.municipio ) {
                    this.selectedDepartamento = cliente.municipio.departamento.id
                    await this.fetchMunicipios(this.selectedDepartamento);
                } else {
                    if(cliente.pais != '' || cliente.pais != null){
                        selected = this.phoneCountries.find(
                            c => c.name === cliente.pais
                        )
                    }
                }
                this.form = {
                    id: cliente.id,
                    nombre: cliente.nombre,
                    genero: cliente.genero,
                    dpi: cliente.dpi,
                    municipios_id: cliente.municipio?.id,
                    departamento_id: cliente.municipio?.departamento?.id,
                    estado_pais: cliente.estado_pais,
                    ciudad_pais: cliente.ciudad_pais,
                    direccion: cliente.direccion,
                    nit: cliente.nit,
                    pais: selected == null ? 'Guatemala' : selected.name,
                    telefono_pais_iso: selected == null ? 'GT' : selected.iso,
                    emails: cliente.emails?.map(e => e.email) ?? [],
                    telefonos: cliente.telefonos?.map(t => ({
                        telefono_pais: t.telefono_pais,
                        telefono_codigo_pais: t.telefono_codigo_pais,
                        telefono_numero: t.telefono_numero
                    })) ?? []
                }
            }
        },
        selectedDepartamento(val) {
            this.form.municipios_id = null
            this.municipios = []

            if (val) {
                this.fetchMunicipios(val)
            }
        }
    },

    methods: {
        reset() {
            this.saving = false,
                this.form = {
                    id: null,
                    nombre: '',
                    genero: '',
                    dpi: '',
                    departamento_id: '',
                    municipio: '',
                    direccion: '',
                    nit: '',
                    telefono_pais_iso: 'GT',
                    municipios_id: null,
                    pais: 'Guatemala',
                    estado_pais: '',
                    ciudad: '',
                    emails: [],
                    telefonos: [],
                }
            this.errors = {}
            this.phoneCountries = []
            this.departamentos = []
            this.municipios = []
            this.selectedDepartamento = null
            this.newEmail = ''
        },

        async submit() {
            this.errors = {}
            this.saving = true

            this.normalizeContacts();

            try {
                let res
                if (this.form.id) {
                    res = await axios.put(`/cliente/${this.form.id}`, this.form)
                } else {
                    res = await axios.post('/cliente', this.form)
                }

                this.$emit('saved', res.data)
                this.reset()
            } catch (err) {
                const e = err.response?.data?.errors
                this.errors = err.response?.data?.errors || {}
                if (e) {
                    const emailErrors = Object.keys(e).filter(k => k.startsWith('emails.'))
                    if (emailErrors.length) {
                        this.errors.emails = 'Uno o más correos no son válidos'
                    }

                    const phoneErrors = Object.keys(e).filter(k => k.startsWith('telefonos.'))
                    if (phoneErrors.length) {
                        this.errors.telefonos = 'Uno o más teléfonos no son válidos'
                    }
                }

                toast.error('Error al guardar el cliente')
            } finally {
                this.saving = false
            }
        },
        async fetchMunicipios(departamentoId) {
            const { data } = await axios.get(`/municipios/${departamentoId}`)
            this.municipios = data
        },

        addEmail() {
            if (!this.newEmail) return
            this.form.emails.push(this.newEmail)
            this.newEmail = ''
        },

        removeEmail(index) {
            this.form.emails.splice(index, 1)
        },

        addTelefono() {
            if (!this.form.telefono_numero) return

            const selected = this.phoneCountries.find(
                c => c.iso === this.form.telefono_pais_iso
            )
            this.form.telefono_codigo_pais = selected?.code || null

            this.form.telefonos.push({
                telefono_pais: selected?.name || null,
                telefono_codigo_pais: selected?.code || null,
                telefono_numero: this.form.telefono_numero,
            })

            this.form.telefono_numero = ''
            console.log(this.form.telefonos)
        },

        removeTelefono(index) {
            this.form.telefonos.splice(index, 1)
        },

        normalizeContacts() {
            // EMAIL
            if (this.newEmail) {
                this.form.emails.push(this.newEmail)
                this.newEmail = ''
            }

            // TELÉFONO
            const selected = this.phoneCountries.find(
                c => c.iso === this.form.telefono_pais_iso
            )
            if (this.form.telefono_numero) {
                this.form.telefonos.push({
                    telefono_pais: selected?.name || null,
                    telefono_codigo_pais: selected?.code || null,
                    telefono_numero: this.form.telefono_numero,
                })

                this.form.telefono_numero = ''
            }
        },
    },

    async mounted() {
        this.phoneCountries = getPhoneCountries();
        if (this.form.telefono_pais_iso === 'GT') {
            const { data } = await axios.get('/departamentos')
            this.departamentos = data
        }
    }
}
</script>
