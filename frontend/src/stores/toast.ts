import { defineStore } from 'pinia'

export type TipoToast = 'success' | 'error' | 'warning' | 'info'

export type Toast = {
  id: number
  type: TipoToast
  message: string
  duration: number
}

type EstadoToast = {
  items: Toast[]
  proximoId: number
}

export const useToastStore = defineStore('toast', {
  state: (): EstadoToast => ({
    items: [],
    proximoId: 1,
  }),

  actions: {
    show(message: string, type: TipoToast = 'info', duration = 5000): number {
      const id = this.proximoId++
      const toast: Toast = { id, type, message, duration }
      this.items.push(toast)

      if (duration > 0) {
        window.setTimeout(() => this.dismiss(id), duration)
      }

      return id
    },

    success(message: string, duration?: number): number {
      return this.show(message, 'success', duration)
    },

    error(message: string, duration?: number): number {
      return this.show(message, 'error', duration)
    },

    warning(message: string, duration?: number): number {
      return this.show(message, 'warning', duration)
    },

    info(message: string, duration?: number): number {
      return this.show(message, 'info', duration)
    },

    dismiss(id: number): void {
      this.items = this.items.filter((toast) => toast.id !== id)
    },

    clear(): void {
      this.items = []
    },
  },
})
