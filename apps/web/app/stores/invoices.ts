import { defineStore } from 'pinia'
import { toApiError, type ApiError } from '~/types/api'
import type {
  Invoice,
  InvoiceListQuery,
  Paginated,
  PaginationMeta,
  SingleResource,
  UpdateInvoicePayload,
} from '~/types/invoice'

type RequestState = 'idle' | 'loading' | 'success' | 'error'

export const useInvoiceStore = defineStore('invoices', () => {
  const items = ref<Invoice[]>([])
  const meta = ref<PaginationMeta | null>(null)
  const byId = ref<Record<string, Invoice>>({})

  const listState = ref<RequestState>('idle')
  const listError = ref<ApiError | null>(null)

  const detailState = ref<RequestState>('idle')
  const detailError = ref<ApiError | null>(null)

  const isListLoading = computed(() => listState.value === 'loading')
  const isDetailLoading = computed(() => detailState.value === 'loading')

  function cache(invoice: Invoice): void {
    byId.value[invoice.id] = invoice

    const index = items.value.findIndex(item => item.id === invoice.id)
    if (index !== -1) {
      items.value[index] = invoice
    }
  }

  async function fetchList(query: InvoiceListQuery = {}): Promise<void> {
    listState.value = 'loading'
    listError.value = null

    try {
      const response = await $fetch<Paginated<Invoice>>('/api/invoices', {
        query: {
          page: query.page ?? 1,
          per_page: query.perPage ?? 20,
          ...(query.status ? { status: query.status } : {}),
        },
      })

      items.value = response.data
      meta.value = response.meta
      response.data.forEach(cache)

      listState.value = 'success'
    }
    catch (cause) {
      listError.value = toApiError(cause)
      listState.value = 'error'
    }
  }

  async function fetchOne(id: string): Promise<void> {
    detailState.value = 'loading'
    detailError.value = null

    try {
      const response = await $fetch<SingleResource<Invoice>>(`/api/invoices/${id}`)

      cache(response.data)
      detailState.value = 'success'
    }
    catch (cause) {
      detailError.value = toApiError(cause)
      detailState.value = 'error'
    }
  }

  async function update(id: string, payload: UpdateInvoicePayload): Promise<Invoice> {
    const response = await $fetch<SingleResource<Invoice>>(`/api/invoices/${id}`, {
      method: 'PUT',
      body: payload,
    })

    cache(response.data)

    return response.data
  }

  return {
    items,
    meta,
    byId,
    listState,
    listError,
    detailState,
    detailError,
    isListLoading,
    isDetailLoading,
    fetchList,
    fetchOne,
    update,
  }
})
