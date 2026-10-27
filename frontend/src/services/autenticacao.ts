import { csrfCookie, request } from './http'
import { z } from 'zod'

const schemaUsuario = z.object({
  id: z.number(),
  name: z.string(),
  email: z.string().email(),
  email_verified_at: z.string().nullable().optional(),
})

export type Usuario = z.infer<typeof schemaUsuario>

const schemaRespostaAutenticacao = z.object({
  data: z.object({ user: schemaUsuario }).optional(),
  user: schemaUsuario.optional(),
})

type RespostaAutenticacao = z.infer<typeof schemaRespostaAutenticacao>

function usuarioDaResposta(payload: RespostaAutenticacao): Usuario {
  const resposta = schemaRespostaAutenticacao.parse(payload)
  const usuario = resposta.data?.user ?? resposta.user
  if (!usuario) {
    throw new Error('Resposta inválida do servidor de autenticação.')
  }
  return usuario
}

export async function entrar(email: string, password: string): Promise<Usuario> {
  await csrfCookie()
  const response = await request<RespostaAutenticacao>('/api/auth/login', {
    method: 'POST',
    data: { email, password },
  })
  return usuarioDaResposta(response)
}

export async function cadastrar(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string,
): Promise<Usuario> {
  await csrfCookie()
  const response = await request<RespostaAutenticacao>('/api/auth/register', {
    method: 'POST',
    data: {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    },
  })
  return usuarioDaResposta(response)
}

export async function usuarioAtual(): Promise<Usuario> {
  const response = await request<RespostaAutenticacao>('/api/auth/me')
  return usuarioDaResposta(response)
}

export async function sair(): Promise<void> {
  await request('/api/auth/logout', { method: 'POST' })
}
