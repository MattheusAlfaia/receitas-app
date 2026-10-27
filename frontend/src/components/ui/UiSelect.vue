<script setup lang="ts">
export type SelectOption = {
  label: string
  value: string | number
}

withDefaults(
  defineProps<{
    modelValue?: string | number
    options: SelectOption[]
    name?: string
    id?: string
    disabled?: boolean
    required?: boolean
    ariaInvalid?: boolean
  }>(),
  {
    modelValue: '',
    disabled: false,
    required: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <select
    :id="id"
    :name="name"
    :value="modelValue"
    :disabled="disabled"
    :required="required"
    :aria-invalid="ariaInvalid"
    class="w-full rounded-field border border-border bg-background px-3 py-3 text-text outline-none transition focus:border-highlight focus:ring-2 focus:ring-highlight/30 disabled:cursor-not-allowed disabled:opacity-50"
    @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
  >
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>
