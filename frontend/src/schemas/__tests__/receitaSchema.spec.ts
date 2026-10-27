import { describe, expect, it } from 'vitest'
import { receitaFormSchema } from '../receitaSchema'

const validRecipe = {
  nome: 'Bolo de chocolate',
  categoria_id: 1,
  tempo_preparo_minutos: 45,
  porcoes: 8,
  ingredientes: 'Farinha, ovos e chocolate.',
  modo_preparo: 'Misture e asse.',
  imagem: null,
}

describe('receita form schema', () => {
  it('accepts valid recipe data', () => {
    expect(receitaFormSchema.safeParse(validRecipe).success).toBe(true)
  })

  it('coerces numeric form values', () => {
    const result = receitaFormSchema.safeParse({
      ...validRecipe,
      categoria_id: '2',
      tempo_preparo_minutos: '30',
      porcoes: '4',
    })

    expect(result.success).toBe(true)
    if (result.success) {
      expect(result.data.categoria_id).toBe(2)
      expect(result.data.tempo_preparo_minutos).toBe(30)
      expect(result.data.porcoes).toBe(4)
    }
  })

  it('rejects non-positive preparation values', () => {
    const result = receitaFormSchema.safeParse({
      ...validRecipe,
      tempo_preparo_minutos: 0,
      porcoes: 0,
    })

    expect(result.success).toBe(false)
  })

  it('rejects unsupported or oversized images', () => {
    const unsupported = new File(['content'], 'recipe.gif', { type: 'image/gif' })
    const oversized = new File([new Uint8Array(20 * 1024 * 1024 + 1)], 'recipe.png', {
      type: 'image/png',
    })

    expect(receitaFormSchema.safeParse({ ...validRecipe, imagem: unsupported }).success).toBe(false)
    expect(receitaFormSchema.safeParse({ ...validRecipe, imagem: oversized }).success).toBe(false)
  })
})
