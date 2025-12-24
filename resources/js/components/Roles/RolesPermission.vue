<template>
    <v-dialog :model-value="modelValue" max-width="600" @update:model-value="$emit('update:modelValue', $event)">
        <v-card>
            <v-card-title>
                Permisos — {{ role.name }}
            </v-card-title>

            <v-card-text>
                <v-expansion-panels multiple>
                    <v-expansion-panel v-for="(perms, module) in groupedPermissions" :key="module">
                        <v-expansion-panel-title>
                            {{ formatModule(module) }}
                        </v-expansion-panel-title>

                        <v-expansion-panel-text>
                            <PermissionCheckboxes :permissions="perms" v-model="selectedPermissions" />
                        </v-expansion-panel-text>
                    </v-expansion-panel>
                </v-expansion-panels>

            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="close">Cancelar</v-btn>
                <v-btn color="primary" @click="save">Guardar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios'
import PermissionCheckboxes from './PermissionCheckboxes.vue'

export default {
    name: 'RolePermissions',

    components: { PermissionCheckboxes },

    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        role: {
            type: Object,
            required: true
        }
    },

    emits: ['update:modelValue'],

    data() {
        return {
            permissions: [],
            selectedPermissions: []
        }
    },

    watch: {
        modelValue(val) {
            if (val && this.role) {
                this.loadData()
            }
        },
        role: {
            immediate: true,
            handler(val) {
                if (val && this.modelValue) {
                    this.loadData()
                }
            }
        }
    },

    computed: {
        groupedPermissions() {
            const groups = {}

            this.permissions.forEach(permission => {
                const [module, action] = permission.split('.')

                if (!groups[module]) {
                    groups[module] = []
                }

                groups[module].push({
                    name: permission,
                    action
                })
            })

            return groups
        }
    },

    methods: {
        close() {
            this.$emit('update:modelValue', false)
        },

        async loadData() {
            await this.loadPermissions()
            await this.loadRolePermissions()
        },

        async loadPermissions() {
            const res = await axios.get('/permissions')
            this.permissions = res.data.map(p => p.name)
        },

        async loadRolePermissions() {
            const res = await axios.get(`/roles/${this.role.id}/permissions`)
            this.selectedPermissions = res.data
        },

        async save() {
            await axios.post(`/roles/${this.role.id}/permissions`, {
                permissions: this.selectedPermissions
            })
            this.close()
        },
        formatModule(module) {
            const map = {
                usuarios: 'Usuarios',
                roles: 'Roles y permisos',
                reportes: 'Reportes'
            }

            return map[module] || module
        }
    }
}
</script>
