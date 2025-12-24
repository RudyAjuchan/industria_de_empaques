<template>
    <v-list density="compact">
        <v-list-item
            v-for="perm in permissions"
            :key="perm.name"
        >
            <template #prepend>
                <v-checkbox
                    density="compact"
                    hide-details
                    :model-value="modelValue.includes(perm.name)"
                    @update:model-value="toggle(perm.name)"
                />
            </template>

            <v-list-item-title class="text-body-2">
                {{ formatAction(perm.action) }}
            </v-list-item-title>
        </v-list-item>
    </v-list>
</template>

<script>
export default {
    props: {
        modelValue: Array,
        permissions: Array
    },
    emits: ['update:modelValue'],

    methods: {
        toggle(permission) {
            const updated = this.modelValue.includes(permission)
                ? this.modelValue.filter(p => p !== permission)
                : [...this.modelValue, permission]

            this.$emit('update:modelValue', updated)
        },

        formatAction(action) {
            return action
                .replace('_', ' ')
                .replace(/\b\w/g, l => l.toUpperCase())
        }
    }
}
</script>
