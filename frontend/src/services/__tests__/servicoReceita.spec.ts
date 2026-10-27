import { beforeEach, describe, expect, it, vi } from 'vitest'
import { request } from '../http'
import {
  atualizarReceita,
  criarReceita,
  excluirReceita,
  listarReceitas,
  obterReceita,
} from '../servicoReceita'
import type { ReceitaFormData } from '@/schemas/receitaSchema'

vi.mock('../http', () => ({
  request: vi.fn(),
}))

const mockedRequest = vi.mocked(request)

const recipeData: ReceitaFormData = {
  nome: 'Bolo',
  categoria_id: 1,
  tempo_preparo_minutos: 30,
  porcoes: 4,
  modo_preparo: 'Asse.',
  ingredientes: 'Farinha.',
  imagem: null,
}

describe('receita service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('sends JSON when creating a recipe without image', async () => {
    mockedRequest.mockResolvedValue({ data: {} })

    await criarReceita(recipeData)

    expect(mockedRequest).toHaveBeenCalledWith('/api/receitas', {
      method: 'POST',
      data: {
        categoria_id: 1,
        nome: 'Bolo',
        tempo_preparo_minutos: 30,
        porcoes: 4,
        modo_preparo: 'Asse.',
        ingredientes: 'Farinha.',
      },
    })
  })

  it('sends FormData when creating a recipe with image', async () => {
    const image = new File(['image'], 'bolo.png', { type: 'image/png' })
    mockedRequest.mockResolvedValue({ data: {} })

    await criarReceita({ ...recipeData, imagem: image })

    const call = mockedRequest.mock.calls[0]
    expect(call?.[0]).toBe('/api/receitas')
    expect(call?.[1]?.method).toBe('POST')
    expect(call?.[1]?.data).toBeInstanceOf(FormData)
    expect((call?.[1]?.data as FormData).get('imagem')).toBe(image)
  })

  it('maps filters to the API query names', async () => {
    mockedRequest.mockResolvedValue({ data: [] })

    await listarReceitas({ nome: 'bolo', categoriaId: 2, tempoMaximo: 60 })

    expect(mockedRequest).toHaveBeenCalledWith('/api/receitas', {
      method: 'GET',
      params: { nome: 'bolo', categoria_id: 2, tempo_maximo: 60 },
    })
  })

  it('uses method override to update and DELETE to remove', async () => {
    mockedRequest.mockResolvedValueOnce({ data: {} }).mockResolvedValueOnce(undefined)

    await atualizarReceita(3, recipeData)
    await excluirReceita(3)

    const updateData = mockedRequest.mock.calls[0]?.[1]?.data as FormData
    expect(updateData.get('_method')).toBe('PATCH')
    expect(mockedRequest).toHaveBeenLastCalledWith('/api/receitas/3', { method: 'DELETE' })
  })

  it('extracts recipe data from the detail response', async () => {
    const receita = { id: 3 }
    mockedRequest.mockResolvedValue({ data: receita })

    await expect(obterReceita(3)).resolves.toEqual(receita)
  })
})
