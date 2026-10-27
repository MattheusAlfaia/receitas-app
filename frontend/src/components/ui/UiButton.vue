<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'dark' | 'danger-outline' | 'icon'
    type?: 'button' | 'submit' | 'reset'
    disabled?: boolean
  }>(),
  {
    variant: 'primary',
    type: 'button',
    disabled: false,
  },
)
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :aria-label="variant === 'icon' ? undefined : undefined"
    :class="[
      'inline-flex items-center justify-center gap-2 font-medium transition focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer',
      {
        'rounded-field bg-primary px-5 py-3 text-white hover:bg-danger': variant === 'primary',
        'rounded-field border border-text px-5 py-3 text-text hover:bg-surface-secondary':
          variant === 'secondary',
        'rounded-pill bg-text px-5 py-3 text-white hover:bg-text-secondary': variant === 'dark',
        'rounded-field border border-danger px-5 py-3 text-danger hover:bg-surface-secondary':
          variant === 'danger-outline',
        'size-10 rounded-full border border-border bg-background p-2 text-text hover:bg-surface-secondary':
          variant === 'icon',
      },
    ]"
  >
    <slot />
  </button>
</template>
