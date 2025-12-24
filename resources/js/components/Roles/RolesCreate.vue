<template>
    <v-container>
        <h2>Crear rol</h2>

        <v-form @submit.prevent="store" class="py-4">
            <v-text-field label="Nombre del rol" variant="outlined" density="compact" v-model="form.name" required />
            <v-row class="ga-2 mt-5">
                <v-btn color="green" variant="tonal" type="submit" :loading="loading">
                    Guardar
                </v-btn>
    
                <v-btn text @click="$router.back()" color="red" variant="tonal">Cancelar</v-btn>
            </v-row>
        </v-form>
    </v-container>
</template>

<script>
import axios from 'axios'

export default {
    data() {
        return {
            form: {
                name: ''
            },
            loading: false
        }
    },

    methods: {
        store() {
            this.loading = true
            axios.post('/roles', this.form)
                .then(() => {
                    this.$router.push('/roles')
                })
                .finally(() => this.loading = false)
        }
    }
}
</script>
