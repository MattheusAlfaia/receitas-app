import { z } from 'zod'

export const schemaLogin = z.object({
  email: z.string().min(1, 'Informe seu e-mail.').email('Informe um e-mail válido.'),
  password: z.string().min(1, 'Informe sua senha.'),
})

export const schemaCadastro = z
  .object({
    name: z.string().min(1, 'Informe seu nome.').max(255, 'O nome pode ter no máximo 255 caracteres.'),
    email: z.string().min(1, 'Informe seu e-mail.').email('Informe um e-mail válido.'),
    password: z.string().min(8, 'A senha deve ter pelo menos 8 caracteres.'),
    password_confirmation: z.string().min(8, 'Confirme sua senha.'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    path: ['password_confirmation'],
    message: 'As senhas precisam ser iguais.',
  })

export type DadosFormularioLogin = z.infer<typeof schemaLogin>
export type DadosFormularioCadastro = z.infer<typeof schemaCadastro>
