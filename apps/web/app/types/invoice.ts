export const INVOICE_STATUSES = ['pending', 'approved', 'rejected'] as const

export type InvoiceStatus = (typeof INVOICE_STATUSES)[number]

export interface Invoice {
  id: string
  number: string
  supplier_name: string
  supplier_tax_id: string
  net_amount: string
  vat_amount: string
  gross_amount: string
  currency: string
  status: InvoiceStatus
  is_editable: boolean
  issue_date: string
  due_date: string
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}

export interface SingleResource<T> {
  data: T
}

export interface UpdateInvoicePayload {
  net_amount: string
  vat_amount: string
  due_date: string
}

export interface InvoiceListQuery {
  page?: number
  perPage?: number
  status?: InvoiceStatus | null
}
