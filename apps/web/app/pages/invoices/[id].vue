<script setup lang="ts">
const route = useRoute()
const store = useInvoiceStore()

const id = computed(() => String(route.params.id))

await callOnce(`invoice:${id.value}`, () => store.fetchOne(id.value))

const { detailError, isDetailLoading } = storeToRefs(store)
const invoice = computed(() => store.byId[id.value] ?? null)

useHead(() => ({
  title: invoice.value ? `${invoice.value.number} — Invoicelane` : 'Рахунок — Invoicelane',
}))
</script>

<template>
  <div class="space-y-6">
    <NuxtLink
        to="/invoices"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 dark:hover:text-gray-100"
    >
      ← До списку
    </NuxtLink>

    <div v-if="isDetailLoading && !invoice" class="h-64 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800" />

    <div
        v-else-if="detailError && !invoice"
        class="rounded-lg border border-rose-200 bg-rose-50 p-6 dark:border-rose-900 dark:bg-rose-950/40"
    >
      <p class="font-medium text-rose-800 dark:text-rose-200">
        {{ detailError.status === 404 ? 'Рахунок не знайдено' : 'Не вдалося завантажити рахунок' }}
      </p>
      <p v-if="detailError.status !== 404" class="mt-1 text-sm text-rose-700 dark:text-rose-300">
        {{ detailError.message }}
      </p>
    </div>

    <template v-else-if="invoice">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">{{ invoice.number }}</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Оновлено {{ formatDateTime(invoice.updated_at) }}
          </p>
        </div>
        <InvoiceStatusBadge :status="invoice.status" />
      </div>

      <section class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Реквізити</h2>

        <dl class="grid gap-x-8 gap-y-4 sm:grid-cols-2">
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Постачальник</dt>
            <dd class="mt-0.5 font-medium">{{ invoice.supplier_name }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Податковий номер</dt>
            <dd class="mt-0.5 font-mono">{{ invoice.supplier_tax_id }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Дата виставлення</dt>
            <dd class="mt-0.5">{{ formatDate(invoice.issue_date) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Термін оплати</dt>
            <dd
                class="mt-0.5"
                :class="isOverdue(invoice.due_date, invoice.status) && 'font-medium text-rose-600 dark:text-rose-400'"
            >
              {{ formatDate(invoice.due_date) }}
              <span v-if="isOverdue(invoice.due_date, invoice.status)" class="text-xs">(прострочено)</span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Сума без ПДВ</dt>
            <dd class="mt-0.5 font-mono tabular-nums">{{ formatMoney(invoice.net_amount, invoice.currency) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">ПДВ</dt>
            <dd class="mt-0.5 font-mono tabular-nums">{{ formatMoney(invoice.vat_amount, invoice.currency) }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-sm text-gray-500 dark:text-gray-400">Разом</dt>
            <dd class="mt-0.5 text-xl font-semibold tabular-nums">
              {{ formatMoney(invoice.gross_amount, invoice.currency) }}
            </dd>
          </div>
        </dl>
      </section>

      <section class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Редагування</h2>
        <InvoiceEditForm :key="invoice.id" :invoice="invoice" />
      </section>
    </template>
  </div>
</template>
