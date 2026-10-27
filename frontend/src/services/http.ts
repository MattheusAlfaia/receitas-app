import axios, { AxiosError, type AxiosRequestConfig } from 'axios'

const urlApi = (import.meta.env.VITE_API_URL ?? 'http://localhost:8000')
const urlBackend = urlApi.endsWith('/api') ? urlApi.slice(0, -4) : urlApi

export type ErroApi = Error & {
  status?: number
  errors?: Record<string, string[]>
}

type CargaErro = {
  message?: string
  errors?: Record<string, string[]>
}

const http = axios.create({
  baseURL: urlApi,
  withCredentials: true,
  headers: { Accept: 'application/json' },
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
})

function lerTokenXsrf(): string | undefined {
  if (typeof document === 'undefined') return undefined

  const cookie = document.cookie
    .split('; ')
    .find((value) => value.startsWith('XSRF-TOKEN='))

  if (!cookie) return undefined

  return decodeURIComponent(cookie.slice('XSRF-TOKEN='.length))
}

async function paraErroApi(error: Error | AxiosError<CargaErro>): Promise<ErroApi> {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as CargaErro | undefined
    const erroApi = new Error(
      payload?.message ?? error.message ?? 'Erro de comunicação com o servidor.',
    ) as ErroApi
    erroApi.status = error.response?.status
    erroApi.errors = payload?.errors
    return erroApi
  }

  return error instanceof Error ? error : new Error('Erro inesperado.')
}

export async function csrfCookie(): Promise<void> {
  try {
    await axios.get(`${urlBackend}/sanctum/csrf-cookie`, {
      withCredentials: true,
      headers: { Accept: 'application/json' },
    })
  } catch (error) {
    if (error instanceof Error) {
      throw await paraErroApi(error)
    }
    throw new Error('Erro inesperado.')
  }
}

export async function request<T>(
  path: string,
  options: AxiosRequestConfig = {},
  tentarAposCsrf = true,
): Promise<T> {
  try {
    const caminho = urlApi.endsWith('/api') && path.startsWith('/api/')
      ? path.slice('/api'.length)
      : path
    const headers: Record<string, string> = {
      ...(options.headers as Record<string, string> | undefined),
    }
    const xsrfToken = lerTokenXsrf()

    if (xsrfToken) {
      headers['X-XSRF-TOKEN'] = xsrfToken
    }

    const response = await http.request<T>({
      ...options,
      url: caminho,
      headers,
    })
    return response.data
  } catch (error) {
    if (!(error instanceof Error)) {
      throw new Error('Erro inesperado.')
    }

    const erroApi = await paraErroApi(error)
    if (erroApi.status === 419 && tentarAposCsrf) {
      await csrfCookie()
      return request<T>(path, options, false)
    }
    throw erroApi
  }
}
