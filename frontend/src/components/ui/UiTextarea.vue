<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue?: string
    name?: string
    id?: string
    placeholder?: string
    rows?: number
    disabled?: boolean
    required?: boolean
    ariaInvalid?: boolean
  }>(),
  {
    modelValue: '',
    rows: 4,
    disabled: false,
    required: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <textarea
    :id="id"
    :name="name"
    :value="modelValue"
    :placeholder="placeholder"
    :rows="rows"
    :disabled="disabled"
    :required="required"
    :aria-invalid="ariaInvalid"
    class="w-full resize-y rounded-field border border-border bg-background px-3 py-3 text-text outline-none transition placeholder:text-text-secondary/70 focus:border-highlight focus:ring-2 focus:ring-highlight/30 disabled:cursor-not-allowed disabled:opacity-50"
    @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  />
</template>
