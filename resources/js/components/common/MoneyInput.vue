<template>
    <v-text-field
        :model-value="displayValue"
        inputmode="decimal"
        v-bind="$attrs"
        @update:model-value="onInput"
        @focus="showRawValue"
        @blur="formatDisplay"
    />
</template>

<script>
import { formatQuetzales, parseMoney } from '../../utils/money'

export default {
    name: 'MoneyInput',
    inheritAttrs: false,
    props: {
        modelValue: {
            type: [Number, String],
            default: null,
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            displayValue: this.formatValue(this.modelValue),
            focused: false,
        }
    },
    watch: {
        modelValue(value) {
            if (!this.focused) {
                this.displayValue = this.formatValue(value)
            }
        },
    },
    methods: {
        onInput(value) {
            const parsed = parseMoney(value)

            this.displayValue = value
            this.$emit('update:modelValue', parsed)
        },
        showRawValue() {
            this.focused = true

            const parsed = parseMoney(this.modelValue)
            this.displayValue = parsed === null ? '' : String(parsed)
        },
        formatDisplay() {
            this.focused = false
            this.displayValue = this.formatValue(this.modelValue)
        },
        formatValue(value) {
            const parsed = parseMoney(value)

            return parsed === null ? '' : formatQuetzales(parsed)
        },
    },
}
</script>
