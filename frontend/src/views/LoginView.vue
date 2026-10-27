<script setup lang="ts">
import { ref } from 'vue'
import { LockKeyhole, LogIn, Mail } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { UiButton, UiInput, UiLabel } from '../components/ui'
import { useAuthStore } from '../stores/autenticacao'
import { useRoute, useRouter } from 'vue-router'
import type { ErroApi } from '../services/http'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { schemaLogin, type DadosFormularioLogin } from '@/schemas/AutenticacaoSchema'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const { errors, defineField, handleSubmit, setErrors } = useForm<DadosFormularioLogin>({
  validationSchema: toTypedSchema(schemaLogin),
  initialValues: { email: '', password: '' },
})
const [email] = defineField('email')
const [password] = defineField('password')
const mensagemErro = ref('')

const submit = handleSubmit(async (values) => {
  mensagemErro.value = ''
  try {
    await auth.entrar(values.email, values.password)
    const requestedRedirect =
      typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    const redirect = requestedRedirect.startsWith('/') && !requestedRedirect.startsWith('//')
      ? requestedRedirect
      : '/'
    await router.replace(redirect)
  } catch (error) {
    const erroApi = error as ErroApi
    if (erroApi.errors) {
      setErrors(Object.fromEntries(
        Object.entries(erroApi.errors).map(([field, messages]) => [field, messages[0] ?? 'Valor inválido.']),
      ))
    }
    mensagemErro.value = erroApi.message
  }
})
</script>

<template>
  <main class="flex min-h-[calc(100vh-2rem)] items-center justify-center px-4 py-12">
    <section
      class="w-full max-w-md rounded-modal border border-border bg-background p-8 shadow-modal"
    >
      <div class="mb-8 text-center">
        <div
          class="mx-auto mb-4 flex size-12 items-center justify-center rounded-pill bg-surface-secondary text-primary"
          aria-hidden="true"
        >
          <LogIn :size="24" :stroke-width="2" />
        </div>
        <h1 class="text-3xl tracking-tight text-text">Entrar</h1>
        <p class="mt-2 text-sm text-text-secondary">Acesse sua conta para continuar.</p>
      </div>

      <form class="space-y-5" novalidate @submit="submit">
        <p v-if="mensagemErro" class="text-center rounded-field bg-red-50 p-3 text-sm text-danger" role="alert">
          {{ mensagemErro }}
        </p>
        <div>
          <UiLabel for="email" required>E-mail</UiLabel>
          <UiInput
            id="email"
            v-model="email"
            type="email"
            name="email"
            autocomplete="email"
            placeholder="voce@exemplo.com"
            :aria-invalid="Boolean(errors.email)"
          >
            <template #leading>
            <Mail
              class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-text-secondary"
              :size="18"
              aria-hidden="true"
            />
            </template>
          </UiInput>
        </div>

        <div class="mt-8">
          <UiInput
            id="password"
            v-model="password"
            type="password"
            name="password"
            autocomplete="current-password"
            placeholder="Digite sua senha"
            :aria-invalid="Boolean(errors.password)"
          >
            <template #leading>
            <LockKeyhole
              class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-text-secondary"
              :size="18"
              aria-hidden="true"
            />
            </template>
          </UiInput>
        </div>

        <UiButton type="submit" class="w-full" :disabled="auth.carregando">
          Entrar
        </UiButton>
      </form>

      <p class="mt-6 text-center text-sm text-text-secondary">
        Ainda não tem uma conta?
        <RouterLink to="/register" class="font-semibold text-primary hover:text-danger">
          Criar conta
        </RouterLink>
      </p>
    </section>
  </main>
</template>
