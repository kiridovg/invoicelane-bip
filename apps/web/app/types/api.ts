import type { FetchError } from 'ofetch'

export interface ApiErrorBody {
  message: string
  errors?: Record<string, string[]>
}

export interface ApiError {
  status: number
  message: string
  fieldErrors: Record<string, string[]>
}

export function toApiError(cause: unknown): ApiError {
  const error = cause as FetchError<ApiErrorBody> | undefined

  return {
    status: error?.statusCode ?? 0,
    message: error?.data?.message ?? error?.message ?? 'Невідома помилка',
    fieldErrors: error?.data?.errors ?? {},
  }
}
