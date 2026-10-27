import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useModalReceitaStore } from '../modalReceita'

describe('recipe modal store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('opens and closes a recipe', () => {
    const store = useModalReceitaStore()
    const receita = { id: 1, nome: 'Bolo' } as never

    store.abrir(receita)
    expect(store.estaAberto).toBe(true)
    expect(store.receita).toEqual(receita)

    store.fechar()
    expect(store.estaAberto).toBe(false)
    expect(store.receita).toBeNull()
  })

  it('notifies deletion and closes the modal', () => {
    const store = useModalReceitaStore()
    store.abrir({ id: 1, nome: 'Bolo' } as never)

    store.notificarExclusao(1)

    expect(store.idReceitaExcluida).toBe(1)
    expect(store.receita).toBeNull()
    expect(store.estaAberto).toBe(false)
  })
})
