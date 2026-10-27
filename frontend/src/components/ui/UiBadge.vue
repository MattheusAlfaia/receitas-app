<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'category' | 'featured'
  }>(),
  {
    variant: 'category',
  },
)
</script>

<template>
  <span
    :class="[
      'inline-flex items-center rounded-pill px-3 py-1 text-xs font-bold',
      variant === 'featured'
        ? 'bg-highlight text-text'
        : 'bg-surface-secondary text-complement',
    ]"
  >
    <slot />
  </span>
</template>
