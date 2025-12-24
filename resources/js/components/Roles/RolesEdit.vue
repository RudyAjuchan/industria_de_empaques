<template>
    <v-container>
        <h2>Editar rol</h2>

        <v-form @submit.prevent="update" class="py-4">
            <v-text-field label="Nombre del rol" v-model="form.name" required variant="outlined" density="compact"/>
            <v-row class="ga-2 mt-5">
                <v-btn color="green" variant="tonal" type="submit" :loading="loading">
                    Actualizar
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

    mounted() {
        axios.get(`/roles/${this.$route.params.id}`)
            .then(res => this.form = res.data)
    },

    methods: {
        update() {
            this.loading = true
            axios.put(`/roles/${this.$route.params.id}`, this.form)
                .then(() => {
                    this.$router.push('/roles')
                })
                .finally(() => this.loading = false)
        }
    }
}
</script>
