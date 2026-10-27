<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Upload } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { useRoute, useRouter } from 'vue-router'
import {
  UiButton,
  UiInput,
  UiLabel,
  UiSelect,
  UiTextarea,
} from '../components/ui'
import { useCategorias } from '@/composables/useCategorias'
import { atualizarReceita, criarReceita, obterReceita } from '@/services/servicoReceita'
import type { ErroApi } from '@/services/http'
import { receitaFormSchema } from '@/schemas/receitaSchema'
import { useToastStore } from '@/stores/toast'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'

const {
  categorias,
  carregando,
  erro,
} = useCategorias()

const {
  errors: fieldErrors,
  defineField,
  handleSubmit,
  isSubmitting,
  setErrors,
  setFieldValue,
  setValues,
} = useForm({
  validationSchema: toTypedSchema(receitaFormSchema),
  initialValues: {
    nome: '',
    categoria_id: 0,
    tempo_preparo_minutos: 45,
    porcoes: 4,
    ingredientes: '',
    modo_preparo: '',
    imagem: null,
  },
})
const [nomeReceita] = defineField('nome')
const [categoriaId] = defineField('categoria_id')
const [tempoPreparo] = defineField('tempo_preparo_minutos')
const [porcoes] = defineField('porcoes')
const [ingredients] = defineField('ingredientes')
const [modoPreparo] = defineField('modo_preparo')
const [imagemSelecionada] = defineField('imagem')
const carregandoReceita = ref(false)
const enviando = computed(() => isSubmitting.value || carregandoReceita.value)
const mensagemSucesso = ref('')
const mensagemErro = ref('')
const router = useRouter()
const route = useRoute()
const toast = useToastStore()
const idReceita = computed(() => {
  const value = Number(route.params.id)
  return Number.isInteger(value) && value > 0 ? value : null
})
const editando = computed(() => idReceita.value !== null)

function lidarComAlteracaoImagem(event: Event) {
  const input = event.target as HTMLInputElement
  setFieldValue('imagem', input.files?.[0] ?? null)
}

function limparMensagens() {
  mensagemSucesso.value = ''
  mensagemErro.value = ''
}

const enviarFormulario = handleSubmit(async (values) => {
  if (carregandoReceita.value) return
  limparMensagens()
  try {
    if (idReceita.value) {
      await atualizarReceita(idReceita.value, values)
      toast.success('Receita atualizada com sucesso.')
    } else {
      await criarReceita(values)
      toast.success('Receita cadastrada com sucesso.')
    }
    await router.push({ name: 'home' })
  } catch (error) {
    const erroApi = error as ErroApi
    mensagemErro.value = erroApi.message || 'Não foi possível cadastrar a receita.'
    toast.error(mensagemErro.value)
    if (erroApi.errors) {
      setErrors(Object.fromEntries(
        Object.entries(erroApi.errors).map(([field, messages]) => [field, messages[0] ?? 'Valor inválido.']),
      ))
    }
  }
})

onMounted(async () => {
  if (!idReceita.value) return

  carregandoReceita.value = true
  try {
    const receita = await obterReceita(idReceita.value)
    setValues({
      nome: receita.nome,
      categoria_id: receita.categoria_id,
      tempo_preparo_minutos: receita.tempo_preparo_minutos,
      porcoes: receita.porcoes,
      ingredientes: receita.ingredientes,
      modo_preparo: receita.modo_preparo,
    })
  } catch (error) {
    const erroApi = error as ErroApi
    mensagemErro.value = erroApi.message || 'Não foi possível carregar a receita.'
    toast.error(mensagemErro.value)
  } finally {
    carregandoReceita.value = false
  }
})
</script>

<template>
  <main class="min-h-screen bg-background">
    <div class="mx-auto max-w-6xl px-5 py-7 sm:px-8 lg:px-10">
      <header class="flex items-center justify-between">
        <RouterLink to="/" class="font-display text-xl font-extrabold text-primary">
          ReceitaApp
        </RouterLink>
        <RouterLink
          to="/"
          class="text-sm font-medium text-text underline-offset-4 transition hover:text-primary hover:underline"
        >
          Cancelar
        </RouterLink>
      </header>

      <section class="pt-10 sm:pt-2">
        <h1 class="text-4xl tracking-tight text-text sm:text-5xl">
          {{ editando ? 'Editar' : 'Nova' }} <span class="text-primary">receita</span>
        </h1>
        <p class="mt-2 text-text-secondary">
          {{ editando ? 'Atualize os dados da sua receita.' : 'Conte como é o prato. Você pode editar depois.' }}
        </p>
      </section>

      <form
        class="mt-8 grid gap-8 lg:grid-cols-[1fr_0.82fr]"
        novalidate
        @submit="enviarFormulario"
      >
        <p
          v-if="mensagemSucesso"
          class="rounded-field bg-green-50 p-3 text-sm text-complement lg:col-span-2"
          role="status"
        >
          {{ mensagemSucesso }}
        </p>
        <p
          v-if="mensagemErro"
          class="rounded-field bg-red-50 p-3 text-sm text-danger lg:col-span-2"
          role="alert"
        >
          {{ mensagemErro }}
        </p>
        <div class="space-y-5 lg:col-start-1 lg:row-start-1">
          <div>
            <UiLabel for="recipe-name" required>Nome da receita</UiLabel>
            <UiInput
              id="recipe-name"
              v-model="nomeReceita"
              name="name"
              placeholder="Ex.: Moqueca capixaba"
              :aria-invalid="Boolean(fieldErrors.nome)"
              :disabled="enviando"
              required
            />
            <p v-if="fieldErrors.nome" class="mt-1 text-sm text-danger">{{ fieldErrors.nome }}</p>
          </div>

          <div class="grid gap-4 sm:grid-cols-[1fr_1.2fr_0.65fr]">
            <div>
              <UiLabel for="category" required>Categoria</UiLabel>
              <UiSelect
                id="category"
                placeholder="Selecione uma categoria"
                v-model="categoriaId"
                :aria-invalid="Boolean(fieldErrors.categoria_id)"
                :disabled="carregando || enviando"
                name="category"
                :options="categorias.map(categoria => ({ label: categoria.nome, value: categoria.id }))"
                required
              />
              <p v-if="fieldErrors.categoria_id" class="mt-1 text-sm text-danger">
                {{ fieldErrors.categoria_id }}
              </p>
              <p v-if="erro" class="mt-1 text-sm text-red-600">{{ erro }}</p>
            </div>
            <div>
              <UiLabel for="preparation-time" required>Tempo de preparo (minutos)</UiLabel>
              <UiInput
                id="preparation-time"
                v-model="tempoPreparo"
                type="number"
                name="preparation_time"
                :min="1"
                placeholder="45"
                :aria-invalid="Boolean(fieldErrors.tempo_preparo_minutos)"
                :disabled="enviando"
                required
              />
              <p v-if="fieldErrors.tempo_preparo_minutos" class="mt-1 text-sm text-danger">
                {{ fieldErrors.tempo_preparo_minutos }}
              </p>
            </div>
            <div>
              <UiLabel for="porcoes" required>Porções</UiLabel>
              <UiInput
                id="porcoes"
                v-model="porcoes"
                type="number"
                name="porcoes"
                :min="1"
                placeholder="4"
                :aria-invalid="Boolean(fieldErrors.porcoes)"
                :disabled="enviando"
                required
              />
              <p v-if="fieldErrors.porcoes" class="mt-1 text-sm text-danger">
                {{ fieldErrors.porcoes }}
              </p>
            </div>
          </div>

          <div>
            <UiLabel for="ingredients" required>Ingredientes</UiLabel>
            <UiTextarea
              id="ingredients"
              v-model="ingredients"
              name="ingredients"
              placeholder="Um ingrediente por linha"
              :rows="4"
              :aria-invalid="Boolean(fieldErrors.ingredientes)"
              :disabled="enviando"
              required
            />
            <p v-if="fieldErrors.ingredientes" class="mt-1 text-sm text-danger">
              {{ fieldErrors.ingredientes }}
            </p>
          </div>

          <div>
            <UiLabel for="modoPreparo" required>Modo de preparo</UiLabel>
            <UiTextarea
              id="modoPreparo"
              v-model="modoPreparo"
              name="modoPreparo"
              placeholder="Descreva o passo a passo"
              :rows="5"
              :aria-invalid="Boolean(fieldErrors.modo_preparo)"
              :disabled="enviando"
              required
            />
            <p v-if="fieldErrors.modo_preparo" class="mt-1 text-sm text-danger">
              {{ fieldErrors.modo_preparo }}
            </p>
          </div>

        </div>

        <div class="lg:col-start-2 lg:row-start-1">
          <UiLabel for="recipe-image">Foto do prato</UiLabel>
          <label
            for="recipe-image"
            class="flex min-h-72 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-card-lg border-2 border-dashed border-primary bg-surface-secondary/70 p-6 text-center transition hover:bg-surface-secondary"
          >
            <span
              class="mb-3 flex size-12 items-center justify-center rounded-full bg-primary text-white"
              aria-hidden="true"
            >
              <Upload :size="22" :stroke-width="2.5" />
            </span>
            <span class="font-display text-xl font-semibold text-text">
              {{ imagemSelecionada ? imagemSelecionada.name : 'Arraste a foto aqui' }}
            </span>
            <span class="mt-3 rounded-pill bg-text px-4 py-2 text-sm font-bold text-white">
              Escolher arquivo
            </span>
            <span class="mt-3 text-xs text-text-secondary">JPG ou PNG · até 20 MB</span>
            <input
              id="recipe-image"
              type="file"
              accept="image/jpeg,image/png"
              class="sr-only"
              :disabled="enviando"
              @change="lidarComAlteracaoImagem"
            />
          </label>
          <p v-if="fieldErrors.imagem" class="mt-1 text-sm text-danger">{{ fieldErrors.imagem }}</p>
        </div>

        <div class="flex flex-wrap gap-3 pt-1 lg:col-start-1 lg:row-start-2">
          <UiButton type="submit" :disabled="enviando">
            {{ enviando ? 'Salvando...' : editando ? 'Atualizar receita' : 'Salvar receita' }}
          </UiButton>
        </div>
      </form>
    </div>
  </main>
</template>
