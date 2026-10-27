<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Printer, X } from '@lucide/vue'
import { useModalReceitaStore } from '@/stores/modalReceita'
import { UiBadge, UiButton } from '@/components/ui'
import { useRouter } from 'vue-router'
import { excluirReceita } from '@/services/servicoReceita'
import { useToastStore } from '@/stores/toast'

const modal = useModalReceitaStore()
const router = useRouter()
const toast = useToastStore()
const excluindo = ref(false)
const mostrarConfirmacaoExclusao = ref(false)

function fecharModal(): void {
  mostrarConfirmacaoExclusao.value = false
  modal.fechar()
}

function imprimirReceita(): void {
  window.print()
}

async function editarReceita(): Promise<void> {
  if (!modal.receita) return
  const id = modal.receita.id
  modal.fechar()
  await router.push({ name: 'edit-recipe', params: { id } })
}

async function solicitarExclusaoReceita(): Promise<void> {
  if (!modal.receita || excluindo.value) return

  mostrarConfirmacaoExclusao.value = true
}

async function confirmarExclusaoReceita(): Promise<void> {
  if (!modal.receita || excluindo.value) return

  const receita = modal.receita
  mostrarConfirmacaoExclusao.value = false
  excluindo.value = true
  try {
    await excluirReceita(receita.id)
    modal.idReceitaExcluida = receita.id
    modal.fechar()
    toast.success('Receita excluída com sucesso.')
  } catch (error) {
    const mensagem = error instanceof Error
      ? error.message
      : 'Não foi possível excluir a receita.'
    toast.error(mensagem)
  } finally {
    excluindo.value = false
  }
}

function cancelarExclusaoReceita(): void {
  mostrarConfirmacaoExclusao.value = false
}

function itensDoConteudo(content: string): string[] {
  const lines = content
    .split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean)

  return lines.length > 1
    ? lines
    : content.split(',').map((item) => item.trim()).filter(Boolean)
}

function lidarComEscape(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    if (mostrarConfirmacaoExclusao.value) {
      cancelarExclusaoReceita()
    } else {
      fecharModal()
    }
  }
}

function bloquearRolagemCorpo(estaAberto: boolean): void {
  if (estaAberto) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

watch(() => modal.estaAberto, bloquearRolagemCorpo, { immediate: true })

onMounted(() => {
  window.addEventListener('keydown', lidarComEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', lidarComEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modal.receita"
      class="modal-receita fixed inset-0 z-40 flex items-center justify-center bg-text/75 p-4 backdrop-blur-sm sm:p-8"
      role="presentation"
      @click.self="fecharModal"
    >
      <section
        class="grid max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-modal bg-background shadow-modal lg:grid-cols-[0.8fr_1.2fr]"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="`recipe-title-${modal.receita.id}`"
      >
        <div class="relative flex min-h-64 items-center justify-center overflow-hidden bg-primary p-8 sm:min-h-80 lg:min-h-full">
          <img
            :src="modal.receita.imagem_url || '/images/receita-sem-imagem.png'"
            :alt="modal.receita.nome"
            class="size-full max-h-96 rounded-card object-cover lg:max-h-none"
          />
        </div>

        <div class="relative flex max-h-[90vh] flex-col overflow-y-auto p-6 sm:p-8">
          <button
            type="button"
            class="absolute right-5 top-5 cursor-pointer rounded-full border border-text-secondary/60 p-2 text-text transition hover:bg-surface-secondary focus-visible:outline-none"
            aria-label="Fechar detalhes da receita"
            @click="fecharModal"
          >
            <X :size="19" aria-hidden="true" />
          </button>

          <div class="pr-12">
            <UiBadge>{{ modal.receita.categoria.nome }}</UiBadge>
            <h2
              :id="`recipe-title-${modal.receita.id}`"
              class="mt-4 font-display text-3xl font-extrabold leading-tight text-text sm:text-4xl"
            >
              {{ modal.receita.nome }}
            </h2>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="rounded-field bg-white p-4">
              <p class="text-[10px] font-bold uppercase tracking-wide text-text-secondary">
                Tempo de preparo
              </p>
              <p class="mt-1 font-display text-xl font-bold text-text">
                {{ modal.receita.tempo_preparo_minutos }} minutos
              </p>
            </div>
            <div class="rounded-field bg-white p-4">
              <p class="text-[10px] font-bold uppercase tracking-wide text-text-secondary">
                Porções
              </p>
              <p class="mt-1 font-display text-xl font-bold text-text">
                {{ modal.receita.porcoes }} pessoas
              </p>
            </div>
          </div>

          <div class="mt-6">
            <h3 class="font-display text-xl font-bold text-primary">Ingredientes</h3>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm leading-relaxed text-text">
              <li v-for="item in itensDoConteudo(modal.receita.ingredientes)" :key="item">
                {{ item }}
              </li>
            </ul>
          </div>

          <div class="mt-6">
            <h3 class="font-display text-xl font-bold text-primary">Modo de preparo</h3>
            <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm leading-relaxed text-text">
              <li v-for="item in itensDoConteudo(modal.receita.modo_preparo)" :key="item">
                {{ item }}
              </li>
            </ol>
          </div>

          <div class="mt-8 flex flex-col gap-3 border-t border-border pt-5 sm:flex-row">
            <UiButton class="flex-1" @click="editarReceita">
              Editar receita
            </UiButton>
            <UiButton variant="secondary" class="flex-1" @click="imprimirReceita">
              <Printer :size="17" aria-hidden="true" />
              Imprimir
            </UiButton>
            <UiButton
              variant="danger-outline"
              class="flex-1"
              :disabled="excluindo"
              @click="solicitarExclusaoReceita"
            >
              {{ excluindo ? 'Excluindo...' : 'Excluir' }}
            </UiButton>
          </div>
        </div>
      </section>
    </div>

    <div
      v-if="mostrarConfirmacaoExclusao && modal.receita"
      class="fixed inset-0 z-50 flex items-center justify-center bg-text/40 p-4"
      role="presentation"
      @click.self="cancelarExclusaoReceita"
    >
      <section
        class="w-full max-w-sm rounded-card bg-background p-6 shadow-modal"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="delete-recipe-title"
        aria-describedby="delete-recipe-description"
      >
        <h2 id="delete-recipe-title" class="font-display text-2xl font-bold text-text">
          Excluir receita?
        </h2>
        <p id="delete-recipe-description" class="mt-2 text-sm leading-relaxed text-text-secondary">
          A receita “{{ modal.receita.nome }}” será excluída permanentemente.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <UiButton variant="secondary" :disabled="excluindo" @click="cancelarExclusaoReceita">
            Cancelar
          </UiButton>
          <UiButton variant="danger-outline" :disabled="excluindo" @click="confirmarExclusaoReceita">
            {{ excluindo ? 'Excluindo...' : 'Excluir' }}
          </UiButton>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<style>
@media print {
  body > *:not(.modal-receita) {
    display: none !important;
  }

  .modal-receita {
    position: static !important;
    display: block !important;
    background: transparent !important;
    padding: 0 !important;
  }

  .modal-receita > section {
    display: grid !important;
    max-height: none !important;
    max-width: none !important;
    box-shadow: none !important;
  }

  .modal-receita button {
    display: none !important;
  }
}
</style>
