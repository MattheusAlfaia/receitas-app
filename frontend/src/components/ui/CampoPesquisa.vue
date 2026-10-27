<script setup lang="ts">
import { Search, X } from '@lucide/vue'

withDefaults(
  defineProps<{
    modelValue?: string
    placeholder?: string
    name?: string
    id?: string
  }>(),
  {
    modelValue: '',
    placeholder: 'Buscar',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>

<template>
  <div class="relative">
    <Search
      class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary"
      :size="18"
      aria-hidden="true"
    />
    <input
      :id="id"
      :name="name"
      type="search"
      :value="modelValue"
      :placeholder="placeholder"
      class="w-full rounded-pill border border-border bg-background py-3 pl-11 pr-11 text-text outline-none transition placeholder:text-text-secondary/70 focus:border-highlight focus:ring-2 focus:ring-highlight/30"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <button
      v-if="modelValue"
      type="button"
      class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-text-secondary transition hover:bg-surface-secondary hover:text-text focus-visible:outline-none"
      aria-label="Limpar busca"
      @click="emit('update:modelValue', '')"
    >
      <X :size="17" aria-hidden="true" />
    </button>
  </div>
</template>

<style scoped>
input[type='search']::-webkit-search-cancel-button {
  appearance: none;
  display: none;
}

input[type='search']::-ms-clear {
  display: none;
}
</style>
