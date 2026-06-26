<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from '@/stores/dashboard'
import { money, date } from '@/format'

const store = useDashboardStore()
const { operations } = storeToRefs(store)

const revenueCount = computed(() => operations.value.filter((o) => o.is_revenue).length)

onMounted(() => store.loadOperations())
</script>

<template>
  <div class="space-y-3">
    <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
      Из <strong>{{ operations.length }}</strong> операций выписки
      <strong class="text-emerald-700">{{ revenueCount }}</strong> — выручка по проектам,
      остальные отфильтрованы как небизнесовые (налоги, зарплаты, аренда, комиссии, депозиты, субподряд).
      Так система отделяет сигнал от шума.
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-3 py-2">Дата</th>
            <th class="px-3 py-2">Тип</th>
            <th class="px-3 py-2">Контрагент</th>
            <th class="px-3 py-2">Категория</th>
            <th class="px-3 py-2 text-right">Сумма</th>
            <th class="px-3 py-2">Назначение</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="o in operations"
            :key="o.id"
            :class="o.is_revenue ? 'bg-emerald-50/40' : ''"
            class="hover:bg-slate-50"
          >
            <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ date(o.op_date) }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ o.direction_label }}</td>
            <td class="px-3 py-2 text-slate-700">{{ o.counterparty_name }}</td>
            <td class="whitespace-nowrap px-3 py-2">
              <span
                class="rounded px-1.5 py-0.5 text-xs"
                :class="o.is_revenue ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
              >{{ o.category_label }}</span>
            </td>
            <td class="whitespace-nowrap px-3 py-2 text-right font-medium" :class="o.is_revenue ? 'text-emerald-700' : 'text-slate-500'">
              {{ money(o.amount) }}
            </td>
            <td class="max-w-md truncate px-3 py-2 text-xs text-slate-500" :title="o.purpose">{{ o.purpose }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
