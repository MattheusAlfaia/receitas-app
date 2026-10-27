<script setup lang="ts">
import { ref } from 'vue'
import { LockKeyhole, Mail, UserPlus } from '@lucide/vue'
import { RouterLink } from 'vue-router'
import { UiButton, UiInput, UiLabel } from '../components/ui'
import { useAuthStore } from '../stores/autenticacao'
import { useRouter } from 'vue-router'
import type { ErroApi } from '../services/http'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { schemaCadastro, type DadosFormularioCadastro } from '@/schemas/AutenticacaoSchema'

const auth = useAuthStore()
const router = useRouter()
const { errors, defineField, handleSubmit, setErrors } = useForm<DadosFormularioCadastro>({
  validationSchema: toTypedSchema(schemaCadastro),
  initialValues: { name: '', email: '', password: '', password_confirmation: '' },
})
const [name] = defineField('name')
const [email] = defineField('email')
const [password] = defineField('password')
const [confirmPassword] = defineField('password_confirmation')
const mensagemErro = ref('')

const submit = handleSubmit(async (values) => {
  mensagemErro.value = ''
  try {
    await auth.cadastrar(values.name, values.email, values.password, values.password_confirmation)
    await router.replace('/')
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
          <UserPlus :size="24" :stroke-width="2" />
        </div>
        <h1 class="text-3xl tracking-tight text-text">Criar conta</h1>
        <p class="mt-2 text-sm text-text-secondary">Preencha seus dados para começar.</p>
      </div>

      <form class="space-y-5" novalidate @submit="submit">
        <p v-if="mensagemErro" class="rounded-field text-center bg-red-50 p-3 text-sm text-danger" role="alert">
          {{ mensagemErro }}
        </p>
        <div>
          <UiLabel for="name" required>Nome</UiLabel>
          <UiInput
            id="name"
            v-model="name"
            type="text"
            name="name"
            autocomplete="name"
            placeholder="Seu nome"
            :aria-invalid="Boolean(errors.name)"
          />
        </div>

        <div>
          <UiLabel for="register-email" required>
            E-mail
          </UiLabel>
          <UiInput
            id="register-email"
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

        <div>
          <UiLabel for="register-password" required>
            Senha
          </UiLabel>
          <UiInput
            id="register-password"
            v-model="password"
            type="password"
            name="password"
            autocomplete="new-password"
            placeholder="Crie uma senha"
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

        <div>
          <UiLabel for="confirm-password" required>
            Confirmar senha
          </UiLabel>
          <UiInput
            id="confirm-password"
            v-model="confirmPassword"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            placeholder="Repita sua senha"
            :aria-invalid="Boolean(errors.password_confirmation)"
          >
            <template #leading>
            <LockKeyhole
              class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-text-secondary"
              :size="18"
              aria-hidden="true"
            />
            </template>
          </UiInput>
          <p v-if="errors.password_confirmation" class="mt-2 text-sm text-danger">
            {{ errors.password_confirmation }}
          </p>
        </div>

        <UiButton type="submit" class="w-full" :disabled="auth.carregando">
          Criar conta
        </UiButton>

      </form>

      <p class="mt-6 text-center text-sm text-text-secondary">
        Já tem uma conta?
        <RouterLink to="/login" class="font-semibold text-primary hover:text-danger">
          Entrar
        </RouterLink>
      </p>
    </section>
  </main>
</template>
