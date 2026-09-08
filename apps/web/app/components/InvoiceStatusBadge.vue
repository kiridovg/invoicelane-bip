<script setup lang="ts">
import type { InvoiceStatus } from '~/types/invoice'

const props = defineProps<{ status: InvoiceStatus | null | undefined }>()

interface BadgeView {
  label: string
  class: string
}

const STATUS_MAP: Record<InvoiceStatus, BadgeView> = {
  pending: {
    label: 'Очікує',
    class: 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30',
  },
  approved: {
    label: 'Затверджено',
    class: 'bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
  },
  rejected: {
    label: 'Відхилено',
    class: 'bg-rose-50 text-rose-800 ring-rose-600/20 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/30',
  },
}

const FALLBACK: BadgeView = {
  label: 'Невідомий статус',
  class: 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-gray-400/10 dark:text-gray-300 dark:ring-gray-400/30',
}

const view = computed<BadgeView>(() =>
    props.status ? STATUS_MAP[props.status] ?? FALLBACK : FALLBACK,
)
</script>

<template>
  <span
      class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
      :class="view.class"
  >
    {{ view.label }}
  </span>
</template>
