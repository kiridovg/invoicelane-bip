import { z } from 'zod'

export function normalizeDecimal(value: string): string {
  return value.trim().replace(',', '.')
}

const decimalString = z
  .string()
  .trim()
  .min(1, 'Обовʼязкове поле')
  .regex(/^-?\d+([.,]\d{1,2})?$/, 'Формат: число, максимум два знаки після коми')

export function createInvoiceEditSchema(issueDate: string) {
  return z.object({
    net_amount: decimalString.refine(
      value => Number(normalizeDecimal(value)) > 0,
      'Сума без ПДВ має бути більшою за нуль',
    ),

    vat_amount: decimalString.refine(
      value => Number(normalizeDecimal(value)) >= 0,
      'Сума ПДВ не може бути відʼємною',
    ),

    due_date: z
      .string()
      .min(1, 'Вкажіть термін оплати')
      .refine(
        value => value >= issueDate,
        `Термін оплати не може бути раніше дати виставлення (${issueDate})`,
      ),
  })
}

export type InvoiceEditValues = z.infer<ReturnType<typeof createInvoiceEditSchema>>
