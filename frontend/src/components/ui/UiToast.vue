<script setup lang="ts">
import { CheckCircle2, CircleAlert, Info, TriangleAlert, X } from '@lucide/vue'
import { useToastStore, type Toast, type TipoToast } from '@/stores/toast'

const lojaToast = useToastStore()

const styles: Record<
  TipoToast,
  { container: string; iconContainer: string; icon: typeof CheckCircle2 }
> = {
  success: {
    container: 'border-complement',
    iconContainer: 'bg-complement/10 text-complement',
    icon: CheckCircle2,
  },
  error: {
    container: 'border-danger',
    iconContainer: 'bg-danger/10 text-danger',
    icon: CircleAlert,
  },
  warning: {
    container: 'border-highlight',
    iconContainer: 'bg-highlight/20 text-text',
    icon: TriangleAlert,
  },
  info: {
    container: 'border-primary',
    iconContainer: 'bg-primary/10 text-primary',
    icon: Info,
  },
}

function estiloToast(toast: Toast) {
  return styles[toast.type]
}
</script>

<template>
  <div
    class="pointer-events-none fixed inset-x-4 top-4 z-50 flex flex-col items-end gap-3 sm:left-auto sm:w-full sm:max-w-md"
    aria-live="polite"
    aria-atomic="true"
  >
    <div
      v-for="toast in lojaToast.items"
      :key="toast.id"
      class="pointer-events-auto flex w-full items-start gap-3 rounded-card border border-l-4 bg-background p-4 text-text shadow-card-featured"
      :class="estiloToast(toast).container"
      role="status"
    >
      <span
        class="flex size-9 shrink-0 items-center justify-center rounded-full"
        :class="estiloToast(toast).iconContainer"
      >
        <component :is="estiloToast(toast).icon" :size="19" aria-hidden="true" />
      </span>
      <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
      <button
        type="button"
        class="shrink-0 rounded-full p-1 text-text-secondary transition hover:bg-surface-secondary hover:text-text"
        aria-label="Fechar mensagem"
        @click="lojaToast.dismiss(toast.id)"
      >
        <X :size="17" aria-hidden="true" />
      </button>
    </div>
  </div>
</template>
