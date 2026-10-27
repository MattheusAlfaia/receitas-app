<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue?: string | number
    type?: 'text' | 'email' | 'password' | 'number'
    name?: string
    id?: string
    placeholder?: string
    autocomplete?: string
    disabled?: boolean
    required?: boolean
    ariaInvalid?: boolean
    minlength?: number
    min?: number
    max?: number
    step?: number
  }>(),
  {
    modelValue: '',
    type: 'text',
    disabled: false,
    required: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="relative">
    <slot name="leading" />
    <input
      :id="id"
      :name="name"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :disabled="disabled"
      :required="required"
      :aria-invalid="ariaInvalid"
      :minlength="minlength"
      :min="min"
      :max="max"
      :step="step"
      :class="[
        'w-full rounded-field border border-border bg-background px-3 py-3 text-text outline-none transition placeholder:text-text-secondary/70 focus:border-highlight focus:ring-2 focus:ring-highlight/30 disabled:cursor-not-allowed disabled:opacity-50',
        { 'pl-10': $slots.leading },
      ]"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
  </div>
</template>
