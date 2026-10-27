<script setup lang="ts">
withDefaults(
  defineProps<{
    iniciais: string
    size?: 'sm' | 'md' | 'lg'
  }>(),
  {
    size: 'md',
  },
)
</script>

<template>
  <span
    :aria-label="`Avatar de ${iniciais}`"
    :class="[
      'inline-flex items-center justify-center rounded-full bg-surface-secondary font-bold text-complement',
      {
        'size-8 text-xs': size === 'sm',
        'size-10 text-sm': size === 'md',
        'size-14 text-lg': size === 'lg',
      },
    ]"
  >
    {{ iniciais }}
  </span>
</template>
