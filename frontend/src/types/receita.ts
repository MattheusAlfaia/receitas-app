import type { Categoria } from './categoria'

export interface Receita {
  id: number
  user_id: number
  categoria_id: number
  nome: string
  tempo_preparo_minutos: number
  porcoes: number
  modo_preparo: string
  ingredientes: string
  created_at: string
  updated_at: string
  categoria: Categoria
  imagem_url: string | null
}

export interface ReceitaResponse {
  message: string
  data: Receita
}

export interface ReceitaListResponse {
  data: Receita[]
  current_page: number
  last_page: number
  total: number
}
