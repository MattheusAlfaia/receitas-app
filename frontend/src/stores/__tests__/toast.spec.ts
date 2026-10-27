import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useToastStore } from '../toast'

describe('toast store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.useFakeTimers()
  })

  it('adds typed toast notifications', () => {
    const store = useToastStore()

    const id = store.success('Salvo', 0)

    expect(id).toBe(1)
    expect(store.items).toEqual([
      { id: 1, type: 'success', message: 'Salvo', duration: 0 },
    ])
  })

  it('dismisses a toast after its duration', () => {
    const store = useToastStore()
    store.info('Aguarde', 1000)

    vi.advanceTimersByTime(1000)

    expect(store.items).toHaveLength(0)
  })

  it('clears all notifications', () => {
    const store = useToastStore()
    store.error('Erro', 0)
    store.warning('Aviso', 0)

    store.clear()

    expect(store.items).toEqual([])
  })
})
