import { describe, expect, it } from 'vitest'
import { schemaLogin, schemaCadastro } from '../AutenticacaoSchema'

describe('auth schemas', () => {
  it('accepts valid login data', () => {
    expect(schemaLogin.safeParse({
      email: 'user@example.com',
      password: 'password',
    }).success).toBe(true)
  })

  it('rejects invalid login data', () => {
    const result = schemaLogin.safeParse({ email: 'invalid', password: '' })

    expect(result.success).toBe(false)
  })

  it('requires matching registration passwords', () => {
    const result = schemaCadastro.safeParse({
      name: 'Maria',
      email: 'maria@example.com',
      password: 'password',
      password_confirmation: 'different',
    })

    expect(result.success).toBe(false)
    if (!result.success) {
      expect(result.error.issues[0]?.path).toEqual(['password_confirmation'])
    }
  })

  it('accepts valid registration data', () => {
    expect(schemaCadastro.safeParse({
      name: 'Maria',
      email: 'maria@example.com',
      password: 'password',
      password_confirmation: 'password',
    }).success).toBe(true)
  })
})
