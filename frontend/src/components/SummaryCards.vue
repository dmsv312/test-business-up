<script setup>
import { computed } from 'vue'
import { money } from '@/format'

const props = defineProps({
  summary: { type: Object, default: null },
})

const cards = computed(() => {
  const s = props.summary
  if (!s) return []
  return [
    { label: 'Оплат', value: s.payments_count },
    { label: 'Проектов', value: s.projects_count },
    { label: 'Клиентов', value: s.clients_count },
    { label: 'Сумма по закрытым актам', value: money(s.closed_acts_amount) },
    { label: 'Сумма по незакрытым актам', value: money(s.open_acts_amount), accent: 'amber' },
    { label: 'Без отправленного акта', value: s.payments_without_sent_act },
    { label: 'Отправлен, не подписан', value: s.payments_sent_not_signed },
    { label: 'Требует внимания', value: s.acts_needs_attention, accent: 'rose' },
  ]
})

const accentClass = {
  amber: 'text-amber-600',
  rose: 'text-rose-600',
}
</script>

<template>
  <div v-if="summary" class="space-y-4">
    <div class="rounded-xl bg-slate-900 p-5 text-white">
      <p class="text-sm text-slate-300">Общая выручка по проектам</p>
      <p class="mt-1 text-3xl font-semibold tracking-tight">{{ money(summary.total_revenue) }}</p>
      <p class="mt-1 text-xs text-slate-400">
        Из выписки исключено {{ summary.filtered_out_operations }} небизнесовых операций (налоги, зарплаты, аренда, комиссии, депозиты, субподряд)
      </p>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
      <div
        v-for="card in cards"
        :key="card.label"
        class="rounded-lg border border-slate-200 bg-white p-4"
      >
        <p class="text-xs text-slate-500">{{ card.label }}</p>
        <p class="mt-1 text-xl font-semibold" :class="accentClass[card.accent] || 'text-slate-900'">
          {{ card.value }}
        </p>
      </div>
    </div>
  </div>
</template>
