<script setup lang="ts">
useHead({ title: 'Рахунки — Invoicelane' })

const route = useRoute()
const router = useRouter()
const store = useInvoiceStore()

const PER_PAGE = 20

const page = computed(() => Math.max(1, Number(route.query.page ?? 1)))

await callOnce('invoices:list', () => store.fetchList({ page: page.value, perPage: PER_PAGE }))

watch(page, value => store.fetchList({ page: value, perPage: PER_PAGE }))

const { items, meta, listError, isListLoading } = storeToRefs(store)

function reload(): void {
  store.fetchList({ page: page.value, perPage: PER_PAGE })
}

function goToPage(value: number): void {
  router.push({ query: { ...route.query, page: value } })
}

useRefreshOnFocus(reload)

const isInitialLoading = computed(() => isListLoading.value && items.value.length === 0)
const isRefreshing = computed(() => isListLoading.value && items.value.length > 0)
const isFatalError = computed(() => Boolean(listError.value) && items.value.length === 0)
const isStaleError = computed(() => Boolean(listError.value) && items.value.length > 0)

const hasPages = computed(() => (meta.value?.last_page ?? 1) > 1)
const isEmptyPage = computed(() => items.value.length === 0 && page.value > 1)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-end justify-between">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Рахунки</h1>
        <p v-if="meta" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Всього: {{ meta.total }}
        </p>
      </div>

      <button
          type="button"
          class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
          :disabled="isListLoading"
          @click="reload"
      >
        <span
            v-if="isRefreshing"
            class="size-3 animate-spin rounded-full border-2 border-gray-400 border-t-transparent"
            aria-hidden="true"
        />
        {{ isRefreshing ? 'Оновлення…' : 'Оновити' }}
      </button>
    </div>

    <div v-if="isInitialLoading" class="space-y-2" aria-busy="true">
      <div
          v-for="n in 6"
          :key="n"
          class="h-14 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"
      />
    </div>

    <div
        v-else-if="isFatalError"
        class="rounded-lg border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950/40"
        role="alert"
    >
      <p class="font-medium text-rose-800 dark:text-rose-200">
        Не вдалося завантажити рахунки
      </p>
      <p class="mt-1 text-sm text-rose-700 dark:text-rose-300">
        {{ listError?.status ? `HTTP ${listError.status} — ${listError.message}` : listError?.message }}
      </p>
      <button
          type="button"
          class="mt-3 rounded-md bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-500"
          @click="reload"
      >
        Спробувати ще раз
      </button>
    </div>

    <div
        v-else-if="isEmptyPage"
        class="rounded-lg border border-dashed border-gray-300 p-12 text-center dark:border-gray-700"
    >
      <p class="font-medium">На цій сторінці немає рахунків</p>
      <button
          type="button"
          class="mt-3 text-sm font-medium text-blue-600 underline dark:text-blue-400"
          @click="goToPage(1)"
      >
        До першої сторінки
      </button>
    </div>

    <div
        v-else-if="items.length === 0"
        class="rounded-lg border border-dashed border-gray-300 p-12 text-center dark:border-gray-700"
    >
      <p class="font-medium">Рахунків поки немає</p>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Створіть перший рахунок через API.
      </p>
    </div>

    <template v-else>
      <div
          v-if="isStaleError"
          class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm dark:border-amber-900 dark:bg-amber-950/40"
          role="status"
      >
        <span class="text-amber-800 dark:text-amber-200">
          Не вдалося оновити список — показані попередні дані.
        </span>
        <button
            type="button"
            class="ml-2 font-medium text-amber-900 underline dark:text-amber-100"
            @click="reload"
        >
          Повторити
        </button>
      </div>

      <div
          class="overflow-x-auto rounded-lg border border-gray-200 bg-white transition-opacity dark:border-gray-800 dark:bg-gray-900"
          :class="isRefreshing && 'opacity-60'"
          :aria-busy="isRefreshing"
      >
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
          <thead class="bg-gray-50 dark:bg-gray-900/60">
          <tr>
            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Номер</th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Постачальник</th>
            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Сума</th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Статус</th>
            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Термін оплати</th>
          </tr>
          </thead>

          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <tr
              v-for="invoice in items"
              :key="invoice.id"
              class="cursor-pointer transition hover:bg-gray-50 focus-within:bg-gray-50 dark:hover:bg-gray-800/60 dark:focus-within:bg-gray-800/60"
              @click="navigateTo(`/invoices/${invoice.id}`)"
          >
            <td class="whitespace-nowrap px-4 py-3 font-medium">
              <NuxtLink
                  :to="`/invoices/${invoice.id}`"
                  class="rounded outline-offset-2 focus-visible:outline-2 focus-visible:outline-blue-600"
                  @click.stop
              >
                {{ invoice.number }}
              </NuxtLink>
            </td>

            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
              {{ invoice.supplier_name }}
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-right font-mono tabular-nums">
              {{ formatMoney(invoice.gross_amount, invoice.currency) }}
            </td>

            <td class="whitespace-nowrap px-4 py-3">
              <InvoiceStatusBadge :status="invoice.status" />
            </td>

            <td class="whitespace-nowrap px-4 py-3">
                <span :class="isOverdue(invoice.due_date, invoice.status) ? 'font-medium text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300'">
                  {{ formatDate(invoice.due_date) }}
                </span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <nav
          v-if="hasPages && meta"
          class="flex items-center justify-between"
          aria-label="Сторінки списку"
      >
        <button
            type="button"
            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
            :disabled="meta.current_page <= 1 || isListLoading"
            @click="goToPage(meta.current_page - 1)"
        >
          ← Назад
        </button>

        <span class="text-sm text-gray-500 dark:text-gray-400">
          Сторінка {{ meta.current_page }} з {{ meta.last_page }}
        </span>

        <button
            type="button"
            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
            :disabled="meta.current_page >= meta.last_page || isListLoading"
            @click="goToPage(meta.current_page + 1)"
        >
          Вперед →
        </button>
      </nav>
    </template>
  </div>
</template>
