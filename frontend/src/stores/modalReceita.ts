import { defineStore } from 'pinia'
import type { Receita } from '@/types/receita'

type EstadoModalReceita = {
  receita: Receita | null
  idReceitaExcluida: number | null
}

export const useModalReceitaStore = defineStore('modalReceita', {
  state: (): EstadoModalReceita => ({
    receita: null,
    idReceitaExcluida: null,
  }),

  getters: {
    estaAberto: (state) => state.receita !== null,
  },

  actions: {
    abrir(receita: Receita): void {
      this.receita = receita
    },

    fechar(): void {
      this.receita = null
    },

    notificarExclusao(id: number): void {
      this.idReceitaExcluida = id
      this.receita = null
    },
  },
})
