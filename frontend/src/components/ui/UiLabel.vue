<script setup lang="ts">
defineProps<{
  for?: string
  required?: boolean
}>()
</script>

<template>
  <label :for="$props['for']" class="mb-2 block text-sm font-medium text-text">
    <slot />
    <span v-if="required" class="ml-1 text-primary" aria-hidden="true">*</span>
  </label>
</template>
