import { beforeEach, describe, expect, it, vi } from 'vitest'
import { request } from '../http'
import { listarCategorias } from '../servicoCategoria'

vi.mock('../http', () => ({
  request: vi.fn(),
}))

const mockedRequest = vi.mocked(request)

describe('categoria service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('lists categories with GET', async () => {
    const categories = [{ id: 1, nome: 'Sobremesas' }]
    mockedRequest.mockResolvedValue(categories)

    await expect(listarCategorias()).resolves.toEqual(categories)
    expect(mockedRequest).toHaveBeenCalledWith('/api/categorias', { method: 'GET' })
  })
})
