<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { createInvoiceEditSchema, normalizeDecimal, type InvoiceEditValues } from '~/schemas/invoice'
import { toApiError } from '~/types/api'
import type { Invoice } from '~/types/invoice'

const props = defineProps<{ invoice: Invoice }>()
const emit = defineEmits<{ saved: [invoice: Invoice] }>()

const store = useInvoiceStore()

const locked = computed(() => !props.invoice.is_editable)

const { handleSubmit, errors, defineField, values, setErrors, resetForm, isSubmitting, meta } =
    useForm<InvoiceEditValues>({
      validationSchema: toTypedSchema(createInvoiceEditSchema(props.invoice.issue_date)),
      initialValues: {
        net_amount: props.invoice.net_amount,
        vat_amount: props.invoice.vat_amount,
        due_date: props.invoice.due_date,
      },
    })

const [netAmount, netAmountAttrs] = defineField('net_amount')
const [vatAmount, vatAmountAttrs] = defineField('vat_amount')
const [dueDate, dueDateAttrs] = defineField('due_date')

const grossPreview = computed<string | null>(() => {
  const net = Number(normalizeDecimal(values.net_amount ?? ''))
  const vat = Number(normalizeDecimal(values.vat_amount ?? ''))

  if (!Number.isFinite(net) || !Number.isFinite(vat)) return null

  return (Math.round((net + vat) * 100) / 100).toFixed(2)
})

const formError = ref<string | null>(null)
const justSaved = ref(false)

const onSubmit = handleSubmit(async (formValues) => {
  formError.value = null
  justSaved.value = false

  try {
    const updated = await store.update(props.invoice.id, {
      net_amount: normalizeDecimal(formValues.net_amount),
      vat_amount: normalizeDecimal(formValues.vat_amount),
      due_date: formValues.due_date,
    })

    resetForm({
      values: {
        net_amount: updated.net_amount,
        vat_amount: updated.vat_amount,
        due_date: updated.due_date,
      },
    })

    justSaved.value = true
    emit('saved', updated)
  }
  catch (cause) {
    const error = toApiError(cause)

    if (error.status === 422) {
      setErrors(
          Object.fromEntries(
              Object.entries(error.fieldErrors).map(([field, messages]) => [field, messages[0]]),
          ) as Partial<Record<keyof InvoiceEditValues, string>>,
      )
      return
    }

    if (error.status === 409) {
      formError.value = 'Рахунок більше не можна редагувати — статус змінився.'
      await store.fetchOne(props.invoice.id)
      return
    }

    formError.value = error.message
  }
})
const hasErrors = computed(() => Object.keys(errors.value).length > 0)

const disabledReason = computed<string | null>(() => {
  if (locked.value) return 'Рахунок не в статусі «Очікує»'
  if (hasErrors.value) return 'Виправте помилки у формі'
  if (!meta.value.dirty) return 'Немає змін для збереження'
  return null
})

const inputClass =
    'w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:disabled:bg-gray-800'
</script>

<template>
  <form class="space-y-5" novalidate @submit="onSubmit">
    <div
        v-if="locked"
        class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400"
    >
      Редагування доступне лише для рахунків у статусі «Очікує».
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label for="net_amount" class="mb-1 block text-sm font-medium">Сума без ПДВ</label>
        <input
            id="net_amount"
            v-model="netAmount"
            v-bind="netAmountAttrs"
            type="text"
            inputmode="decimal"
            :disabled="locked"
            :aria-invalid="Boolean(errors.net_amount)"
            :aria-describedby="errors.net_amount ? 'net_amount-error' : undefined"
            :class="[inputClass, errors.net_amount && 'border-rose-500']"
        >
        <p
            v-if="errors.net_amount"
            id="net_amount-error"
            class="mt-1 text-sm text-rose-600 dark:text-rose-400"
        >
          {{ errors.net_amount }}
        </p>
      </div>

      <div>
        <label for="vat_amount" class="mb-1 block text-sm font-medium">Сума ПДВ</label>
        <input
            id="vat_amount"
            v-model="vatAmount"
            v-bind="vatAmountAttrs"
            type="text"
            inputmode="decimal"
            :disabled="locked"
            :aria-invalid="Boolean(errors.vat_amount)"
            :aria-describedby="errors.vat_amount ? 'vat_amount-error' : undefined"
            :class="[inputClass, errors.vat_amount && 'border-rose-500']"
        >
        <p
            v-if="errors.vat_amount"
            id="vat_amount-error"
            class="mt-1 text-sm text-rose-600 dark:text-rose-400"
        >
          {{ errors.vat_amount }}
        </p>
      </div>

      <div>
        <label for="due_date" class="mb-1 block text-sm font-medium">Термін оплати</label>
        <input
            id="due_date"
            v-model="dueDate"
            v-bind="dueDateAttrs"
            type="date"
            :min="invoice.issue_date"
            :disabled="locked"
            :aria-invalid="Boolean(errors.due_date)"
            :aria-describedby="errors.due_date ? 'due_date-error' : undefined"
            :class="[inputClass, errors.due_date && 'border-rose-500']"
        >
        <p
            v-if="errors.due_date"
            id="due_date-error"
            class="mt-1 text-sm text-rose-600 dark:text-rose-400"
        >
          {{ errors.due_date }}
        </p>
      </div>

      <div>
        <span class="mb-1 block text-sm font-medium">Разом з ПДВ</span>
        <output
            class="block rounded-md border border-dashed border-gray-300 px-3 py-2 font-mono text-sm tabular-nums dark:border-gray-700"
        >
          {{ grossPreview ? formatMoney(grossPreview, invoice.currency) : '—' }}
        </output>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Розраховується автоматично, остаточне значення підтверджує сервер.
        </p>
      </div>
    </div>

    <div
        v-if="formError"
        class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200"
        role="alert"
    >
      {{ formError }}
    </div>

    <div class="flex items-center gap-3">
      <span :title="disabledReason ?? undefined" class="inline-block">
        <button
            type="submit"
            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="Boolean(disabledReason) || isSubmitting"
            :aria-describedby="disabledReason ? 'submit-hint' : undefined"
        >
          {{ isSubmitting ? 'Збереження…' : 'Зберегти' }}
        </button>
      </span>

      <span
          v-if="disabledReason"
          id="submit-hint"
          class="text-sm text-gray-500 dark:text-gray-400"
      >
        {{ disabledReason }}
      </span>

      <span v-else-if="justSaved" class="text-sm text-emerald-600 dark:text-emerald-400">
        Збережено
      </span>
    </div>
  </form>
</template>
