<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from '@/stores/dashboard'
import { money } from '@/format'
import { DIRECTIONS } from '@/constants'

const store = useDashboardStore()
const { projects } = storeToRefs(store)

const labels = Object.fromEntries(DIRECTIONS.map((d) => [d.value, d.label]))
const directionLabel = (value) => labels[value] || value

onMounted(() => store.loadProjects())
</script>

<template>
  <div class="space-y-3">
    <h2 class="text-sm font-semibold text-slate-700">Проекты ({{ projects.length }})</h2>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-3 py-2">Проект</th>
            <th class="px-3 py-2">Юрлицо</th>
            <th class="px-3 py-2">Направления</th>
            <th class="px-3 py-2 text-right">Оплат</th>
            <th class="px-3 py-2 text-right">Сумма</th>
            <th class="px-3 py-2">Акты</th>
            <th class="px-3 py-2">Статус</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="p in projects" :key="p.id" class="hover:bg-slate-50">
            <td class="px-3 py-2 font-medium text-slate-800">{{ p.name }}</td>
            <td class="px-3 py-2 text-slate-600">{{ p.client.name }}</td>
            <td class="px-3 py-2">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="d in p.directions"
                  :key="d"
                  class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600"
                >{{ directionLabel(d) }}</span>
              </div>
            </td>
            <td class="px-3 py-2 text-right text-slate-700">{{ p.payments_count }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-right font-medium text-slate-800">{{ money(p.total_amount) }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">
              закрыто {{ p.acts.closed }} / открыто {{ p.acts.open }}
              <span v-if="p.acts.needs_attention" class="font-medium text-rose-600">· внимание {{ p.acts.needs_attention }}</span>
            </td>
            <td class="px-3 py-2">
              <span
                class="rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                :class="p.status === 'closed'
                  ? 'bg-emerald-100 text-emerald-700 ring-emerald-200'
                  : 'bg-slate-100 text-slate-600 ring-slate-200'"
              >{{ p.status_label }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
