import { computed, onMounted, ref } from 'vue'
import { listarCategorias } from '@/services/servicoCategoria'
import type { Categoria } from '@/types/categoria'

export function useCategorias() {
  const categorias = ref<Categoria[]>([])
  const carregando = ref(false)
  const erro = ref<string | null>(null)

  async function carregarCategorias(): Promise<void> {
    carregando.value = true
    erro.value = null

    try {
      categorias.value = await listarCategorias()
    } catch (error) {
      erro.value =
        error instanceof Error
          ? error.message
          : 'Erro inesperado ao carregar categorias.'
    } finally {
      carregando.value = false
    }
  }

  const categoriasOpcoes = computed(() =>
    categorias.value.map((categoria) => ({
      label: categoria.nome,
      value: categoria.id,
    })),
  )

  onMounted(carregarCategorias)

  return {
    categorias,
    carregando,
    erro,
    categoriasOpcoes,
    carregarCategorias,
  }
}
