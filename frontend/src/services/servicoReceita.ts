import { request } from './http'
import type { ReceitaFormData } from '@/schemas/receitaSchema'
import type { Receita, ReceitaListResponse, ReceitaResponse } from '@/types/receita'

export type ReceitaFilters = {
  nome?: string
  categoriaId?: number
  tempoMaximo?: number
}

function toFormData(data: ReceitaFormData): FormData {
  const formData = new FormData()
  formData.append('categoria_id', String(data.categoria_id))
  formData.append('nome', data.nome)
  formData.append('tempo_preparo_minutos', String(data.tempo_preparo_minutos))
  formData.append('porcoes', String(data.porcoes))
  formData.append('modo_preparo', data.modo_preparo)
  formData.append('ingredientes', data.ingredientes)

  if (data.imagem) {
    formData.append('imagem', data.imagem)
  }

  return formData
}

export async function criarReceita(data: ReceitaFormData): Promise<ReceitaResponse> {
  const payload = data.imagem ? toFormData(data) : {
    categoria_id: data.categoria_id,
    nome: data.nome,
    tempo_preparo_minutos: data.tempo_preparo_minutos,
    porcoes: data.porcoes,
    modo_preparo: data.modo_preparo,
    ingredientes: data.ingredientes,
  }

  return request<ReceitaResponse>('/api/receitas', {
    method: 'POST',
    data: payload,
  })
}

export async function listarReceitas(
  filters: ReceitaFilters = {},
): Promise<ReceitaListResponse> {
  return request<ReceitaListResponse>('/api/receitas', {
    method: 'GET',
    params: {
      nome: filters.nome || undefined,
      categoria_id: filters.categoriaId,
      tempo_maximo: filters.tempoMaximo,
    },
  })
}

export async function obterReceita(id: number): Promise<Receita> {
  const response = await request<{ data: Receita }>(`/api/receitas/${id}`, {
    method: 'GET',
  })
  return response.data
}

export async function atualizarReceita(
  id: number,
  data: ReceitaFormData,
): Promise<ReceitaResponse> {
  const formData = toFormData(data)
  formData.append('_method', 'PATCH')

  return request<ReceitaResponse>(`/api/receitas/${id}`, {
    method: 'POST',
    data: formData,
  })
}

export async function excluirReceita(id: number): Promise<void> {
  await request(`/api/receitas/${id}`, {
    method: 'DELETE',
  })
}
