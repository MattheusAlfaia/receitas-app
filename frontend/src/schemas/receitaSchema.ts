import { z } from 'zod'

const imageSchema = z
  .custom<File | null>(
    (value) =>
      value === null ||
      (typeof File !== 'undefined' && value instanceof File),
    'Selecione um arquivo de imagem válido.',
  )
  .refine((file) => file === null || file.size <= 20 * 1024 * 1024, {
    message: 'A imagem deve ter no máximo 20 MB.',
  })
  .refine(
    (file) => file === null || ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type),
    {
      message: 'A imagem deve estar no formato JPG, PNG, JPEG ou WEBP.',
    },
  )

export const receitaFormSchema = z.object({
  nome: z.string().min(1, 'Informe o nome da receita.').max(150, 'O nome pode ter no máximo 150 caracteres.'),
  categoria_id: z.coerce.number().int('Selecione uma categoria válida.').min(1, 'Selecione uma categoria.'),
  tempo_preparo_minutos: z.coerce.number().int('Informe um número inteiro.').min(1, 'Informe pelo menos 1 minuto.'),
  porcoes: z.coerce.number().int('Informe um número inteiro.').min(1, 'Informe pelo menos 1 porção.'),
  ingredientes: z.string().min(1, 'Informe os ingredientes.'),
  modo_preparo: z.string().min(1, 'Informe o modo de preparo.'),
  imagem: imageSchema,
})

export type ReceitaFormData = z.infer<typeof receitaFormSchema>
