<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { ChevronDown, LogOut, Plus } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/autenticacao'
import { listarReceitas } from '@/services/servicoReceita'
import type { Receita } from '@/types/receita'
import type { ErroApi } from '@/services/http'
import { useToastStore } from '@/stores/toast'
import { useModalReceitaStore } from '@/stores/modalReceita'
import {
  UiAvatar,
  UiButton,
  CampoPesquisa,
} from '../components/ui'

const pesquisa = ref('')
const receitas = ref<Receita[]>([])
const totalReceitas = ref(0)
const carregandoReceitas = ref(false)
const erroReceitas = ref('')
const toast = useToastStore()
const modalReceita = useModalReceitaStore()
const menuUsuarioAberto = ref(false)
const router = useRouter()
const auth = useAuthStore()
const usuario = computed(() => auth.usuario!)
const iniciais = computed(() =>
  usuario.value.name
    .split(' ')
    .map((parte) => parte[0])
    .join('')
    .slice(0, 2)
    .toUpperCase(),
)

function fecharMenuUsuario(event?: MouseEvent) {
  const target = event?.target
  if (target instanceof Node && (target as HTMLElement).closest('[data-user-menu]')) {
    return
  }

  menuUsuarioAberto.value = false
}

function lidarComEscape(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    menuUsuarioAberto.value = false
  }
}

async function lidarComLogout() {
  menuUsuarioAberto.value = false
  await auth.sair()
  await router.push('/login')
}

async function carregarReceitas(): Promise<void> {
  carregandoReceitas.value = true
  erroReceitas.value = ''

  try {
    const response = await listarReceitas({
      nome: pesquisa.value.trim(),
    })
    receitas.value = response.data
    totalReceitas.value = response.total
  } catch (error) {
    const erroApi = error as ErroApi
    erroReceitas.value = erroApi.message || 'Não foi possível carregar as receitas.'
    toast.error(erroReceitas.value)
  } finally {
    carregandoReceitas.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', fecharMenuUsuario)
  document.addEventListener('keydown', lidarComEscape)
  carregarReceitas()
})

onUnmounted(() => {
  document.removeEventListener('click', fecharMenuUsuario)
  document.removeEventListener('keydown', lidarComEscape)
})

let temporizadorPesquisa: number | undefined
watch(pesquisa, () => {
  window.clearTimeout(temporizadorPesquisa)
  temporizadorPesquisa = window.setTimeout(carregarReceitas, 300)
})

watch(
  () => modalReceita.idReceitaExcluida,
  (idReceitaExcluida) => {
    if (idReceitaExcluida !== null) {
      carregarReceitas()
    }
  },
)
</script>

<template>
  <main class="min-h-screen bg-background">
    <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10">
      <header class="flex flex-wrap items-center gap-4">
        <RouterLink to="/" class="font-display text-xl font-extrabold text-primary">
          ReceitaApp
        </RouterLink>

        <div class="order-3 w-full flex-1 sm:order-0 sm:min-w-64">
          <CampoPesquisa v-model="pesquisa" placeholder="Busque por prato" />
        </div>

        <UiButton class="ml-auto shrink-0" variant="primary" @click="router.push('/receita/nova')">
          <Plus :size="17" :stroke-width="2.5" aria-hidden="true" />
          Nova receita
        </UiButton>
        <div class="relative" data-user-menu>
          <button
            type="button"
            class="flex items-center gap-2 rounded-pill p-1 transition hover:bg-surface-secondary focus-visible:outline-none"
            :aria-expanded="menuUsuarioAberto"
            aria-haspopup="menu"
            aria-label="Abrir menu do usuário"
            @click.stop="menuUsuarioAberto = !menuUsuarioAberto"
          >
            <UiAvatar :iniciais="iniciais" size="md" />
            <ChevronDown
              class="hidden text-text-secondary sm:block"
              :size="16"
              aria-hidden="true"
            />
          </button>

          <div
            v-if="menuUsuarioAberto"
            class="absolute right-0 top-full z-20 mt-3 w-64 rounded-card border border-border bg-background p-4 shadow-modal"
            role="menu"
          >
            <div class="border-b border-border pb-3">
              <p class="font-semibold text-text">{{ usuario.name }}</p>
              <p class="mt-1 truncate text-sm text-text-secondary">{{ usuario.email }}</p>
            </div>
            <button
              type="button"
              class="mt-3 flex w-full items-center gap-2 rounded-field px-3 py-2 text-left text-sm font-medium text-danger transition hover:bg-surface-secondary focus-visible:outline-none"
              role="menuitem"
              @click="lidarComLogout"
            >
              <LogOut :size="17" aria-hidden="true" />
              Sair
            </button>
          </div>
        </div>
      </header>

      <section class="mt-8" aria-label="Resumo das receitas">
        <article class="relative overflow-hidden rounded-card-lg bg-text p-6 text-background shadow-card-featured sm:p-8">
          <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p class="text-sm font-bold uppercase tracking-[0.18em] text-highlight">
                Sua coleção
              </p>
              <h2 class="mt-2 max-w-md font-display text-3xl font-bold sm:text-4xl">
                Suas receitas <span class="text-highlight">salvas</span>
              </h2>
            </div>
            <div class="flex items-end gap-3">
              <p class="font-display text-6xl font-extrabold leading-none text-highlight">
                {{ totalReceitas }}
              </p>
              <p class="pb-1 text-sm text-background/75">{{ totalReceitas === 1 ? 'receita' : 'receitas' }}</p>
            </div>
          </div>
        </article>
      </section>

      <section class="mt-8 pb-12" aria-labelledby="recent-heading">
        <div class="mb-4 flex items-center justify-between gap-4">
          <h2 id="recent-heading" class="font-display text-2xl font-bold text-text sm:text-3xl">
            Suas <span class="text-primary">receitas</span>
          </h2>
        </div>

        <p v-if="erroReceitas" class="rounded-card bg-red-50 p-8 text-center text-danger">
          {{ erroReceitas }}
        </p>
        <p
          v-else-if="carregandoReceitas"
          class="rounded-card bg-surface-secondary p-8 text-center text-text-secondary"
        >
          Carregando receitas...
        </p>
        <div v-else id="receitas" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <article
            v-for="receita in receitas"
            :key="receita.id"
            class="group min-w-0 cursor-pointer"
            role="button"
            tabindex="0"
            @click="modalReceita.abrir(receita)"
            @keydown.enter="modalReceita.abrir(receita)"
            @keydown.space.prevent="modalReceita.abrir(receita)"
          >
            <div class="relative flex aspect-[1.1] items-center justify-center overflow-hidden rounded-card bg-[#F6C89D]">
              <img
                :src="receita.imagem_url || '/images/receita-sem-imagem.png'"
                :alt="receita.nome"
                class="size-full object-cover transition duration-300 group-hover:scale-105"
              />
            </div>
            <div class="mt-3">
              <p class="text-[10px] font-bold uppercase tracking-wide text-primary">
                {{ receita.categoria.nome }} · {{ receita.tempo_preparo_minutos }} min
              </p>
              <h3 class="mt-1 font-display text-lg font-semibold leading-tight text-text">
                {{ receita.nome }}
              </h3>
            </div>
          </article>
          <p
            v-if="receitas.length === 0"
            class="col-span-full rounded-card bg-surface-secondary p-8 text-center text-text-secondary"
          >
            Nenhuma receita encontrada para sua busca.
          </p>
        </div>
      </section>
    </div>
  </main>

</template>
