import { defineStore } from 'pinia'
import { usuarioAtual, entrar, cadastrar, sair, type Usuario } from '../services/autenticacao'
import type { ErroApi } from '../services/http'

type EstadoAutenticacao = {
  usuario: Usuario | null
  inicializado: boolean
  carregando: boolean
  erro: ErroApi | null
}

export const useAuthStore = defineStore('auth', {
  state: (): EstadoAutenticacao => ({
    usuario: null,
    inicializado: false,
    carregando: false,
    erro: null,
  }),

  getters: {
    estaAutenticado: (state) => state.usuario !== null,
  },

  actions: {
    async inicializar(): Promise<void> {
      if (this.inicializado) return

      this.carregando = true
      try {
        this.usuario = await usuarioAtual()
      } catch (error) {
        const erroApi = error as ErroApi
        if (erroApi.status !== 401) {
          this.erro = erroApi
        }
        this.usuario = null
      } finally {
        this.inicializado = true
        this.carregando = false
      }
    },

    async entrar(email: string, password: string): Promise<void> {
      this.carregando = true
      this.erro = null
      try {
        this.usuario = await entrar(email, password)
      } catch (error) {
        this.erro = error as ErroApi
        throw error
      } finally {
        this.carregando = false
      }
    },

    async cadastrar(
      name: string,
      email: string,
      password: string,
      passwordConfirmation: string,
    ): Promise<void> {
      this.carregando = true
      this.erro = null
      try {
        this.usuario = await cadastrar(name, email, password, passwordConfirmation)
      } catch (error) {
        this.erro = error as ErroApi
        throw error
      } finally {
        this.carregando = false
      }
    },

    async sair(): Promise<void> {
      this.carregando = true
      try {
        await sair()
      } finally {
        this.usuario = null
        this.carregando = false
      }
    },
  },
})
