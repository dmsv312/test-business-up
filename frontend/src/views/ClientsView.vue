<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from '@/stores/dashboard'
import { money } from '@/format'

const store = useDashboardStore()
const { clients } = storeToRefs(store)

onMounted(() => store.loadClients())
</script>

<template>
  <div class="space-y-3">
    <h2 class="text-sm font-semibold text-slate-700">Клиенты / юрлица ({{ clients.length }})</h2>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-3 py-2">Наименование</th>
            <th class="px-3 py-2">Форма</th>
            <th class="px-3 py-2">ИНН</th>
            <th class="px-3 py-2 text-right">Оплат</th>
            <th class="px-3 py-2 text-right">Сумма</th>
            <th class="px-3 py-2">Акты</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="c in clients" :key="c.id" class="hover:bg-slate-50">
            <td class="px-3 py-2 font-medium text-slate-800">{{ c.name }}</td>
            <td class="px-3 py-2 text-slate-600">{{ c.type_label }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ c.inn }}</td>
            <td class="px-3 py-2 text-right text-slate-700">{{ c.payments_count }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-right font-medium text-slate-800">{{ money(c.total_amount) }}</td>
            <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">
              закрыто {{ c.acts.closed }} / открыто {{ c.acts.open }}
              <span v-if="c.acts.needs_attention" class="font-medium text-rose-600">· внимание {{ c.acts.needs_attention }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
