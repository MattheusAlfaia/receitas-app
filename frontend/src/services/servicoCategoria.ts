import { request } from './http'
import type { Categoria } from '@/types/categoria'

export async function listarCategorias(): Promise<Categoria[]> {
  return request<Categoria[]>('/api/categorias', {
    method: 'GET',
  })
}
