import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '../autenticacao'
import * as authService from '@/services/autenticacao'

vi.mock('@/services/autenticacao', () => ({
  usuarioAtual: vi.fn(),
  entrar: vi.fn(),
  sair: vi.fn(),
  cadastrar: vi.fn(),
}))

const mockedUsuarioAtual = vi.mocked(authService.usuarioAtual)
const mockedEntrar = vi.mocked(authService.entrar)
const mockedSair = vi.mocked(authService.sair)
const mockedCadastrar = vi.mocked(authService.cadastrar)

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('initializes with no authenticated user', () => {
    const store = useAuthStore()

    expect(store.usuario).toBeNull()
    expect(store.estaAutenticado).toBe(false)
    expect(store.inicializado).toBe(false)
  })

  it('loads the current user only once', async () => {
    const usuario = { id: 1, name: 'Maria', email: 'maria@example.com' }
    mockedUsuarioAtual.mockResolvedValue(usuario)
    const store = useAuthStore()

    await store.inicializar()
    await store.inicializar()

    expect(store.usuario).toEqual(usuario)
    expect(mockedUsuarioAtual).toHaveBeenCalledOnce()
    expect(store.inicializado).toBe(true)
  })

  it('ignores unauthorized initialization errors', async () => {
    mockedUsuarioAtual.mockRejectedValue(Object.assign(new Error('Unauthorized'), { status: 401 }))
    const store = useAuthStore()

    await store.inicializar()

    expect(store.usuario).toBeNull()
    expect(store.erro).toBeNull()
    expect(store.inicializado).toBe(true)
  })

  it('updates the user on sign in and sign up', async () => {
    const usuario = { id: 1, name: 'Maria', email: 'maria@example.com' }
    mockedEntrar.mockResolvedValue(usuario)
    mockedCadastrar.mockResolvedValue(usuario)
    const store = useAuthStore()

    await store.entrar(usuario.email, 'password')
    expect(store.usuario).toEqual(usuario)
    await store.cadastrar(usuario.name, usuario.email, 'password', 'password')
    expect(store.usuario).toEqual(usuario)
  })

  it('clears the user after sign out even when the request fails', async () => {
    mockedSair.mockRejectedValue(new Error('Network error'))
    const store = useAuthStore()
    store.$patch({ usuario: { id: 1, name: 'Maria', email: 'maria@example.com' } })

    await expect(store.sair()).rejects.toThrow('Network error')

    expect(store.usuario).toBeNull()
    expect(store.carregando).toBe(false)
  })
})
