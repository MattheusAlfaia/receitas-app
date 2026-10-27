import { beforeEach, describe, expect, it, vi } from 'vitest'
import { csrfCookie, request } from '../http'
import { usuarioAtual, entrar, sair, cadastrar } from '../autenticacao'

vi.mock('../http', () => ({
  csrfCookie: vi.fn(),
  request: vi.fn(),
}))

const mockedRequest = vi.mocked(request)
const mockedCsrfCookie = vi.mocked(csrfCookie)

describe('auth service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('gets csrf cookie before login', async () => {
    mockedRequest.mockResolvedValue({
      data: { user: { id: 1, name: 'Maria', email: 'maria@example.com' } },
    })

    await entrar('maria@example.com', 'password')

    expect(mockedCsrfCookie).toHaveBeenCalledOnce()
    expect(mockedRequest).toHaveBeenCalledWith('/api/auth/login', {
      method: 'POST',
      data: { email: 'maria@example.com', password: 'password' },
    })
  })

  it('sends confirmation data when registering', async () => {
    mockedRequest.mockResolvedValue({
      message: 'ok',
      data: { user: { id: 1, name: 'Maria', email: 'maria@example.com' } },
    })

    await cadastrar('Maria', 'maria@example.com', 'password', 'password')

    expect(mockedRequest).toHaveBeenCalledWith('/api/auth/register', {
      method: 'POST',
      data: {
        name: 'Maria',
        email: 'maria@example.com',
        password: 'password',
        password_confirmation: 'password',
      },
    })
  })

  it('extracts the current user and logs out with POST', async () => {
    const usuario = { id: 1, name: 'Maria', email: 'maria@example.com' }
    mockedRequest.mockResolvedValueOnce({ user: usuario }).mockResolvedValueOnce(undefined)

    await expect(usuarioAtual()).resolves.toEqual(usuario)
    await sair()

    expect(mockedRequest).toHaveBeenLastCalledWith('/api/auth/logout', { method: 'POST' })
  })

  it('rejects malformed authentication responses', async () => {
    mockedRequest.mockResolvedValue({ invalid: true })

    await expect(usuarioAtual()).rejects.toThrow()
  })
})
