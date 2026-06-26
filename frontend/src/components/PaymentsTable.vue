<script setup>
import { useDashboardStore } from '@/stores/dashboard'
import ActStatusBadge from '@/components/ActStatusBadge.vue'
import { money, date } from '@/format'

defineProps({
  payments: { type: Array, default: () => [] },
})

const store = useDashboardStore()
const update = (payment, payload) => store.updateAct(payment, payload)

const btn = 'rounded border px-2 py-0.5 text-xs font-medium whitespace-nowrap'
</script>

<template>
  <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-3 py-2">Дата</th>
          <th class="px-3 py-2">Клиент</th>
          <th class="px-3 py-2">Проект</th>
          <th class="px-3 py-2">Направление</th>
          <th class="px-3 py-2 text-right">Сумма</th>
          <th class="px-3 py-2">Акт</th>
          <th class="px-3 py-2">Действия</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="p in payments" :key="p.id" class="hover:bg-slate-50">
          <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ date(p.payment_date) }}</td>
          <td class="px-3 py-2">
            <div class="font-medium text-slate-800">{{ p.client.name }}</div>
            <div class="max-w-xs truncate text-xs text-slate-400" :title="p.payment_purpose">
              {{ p.payment_purpose }}
            </div>
          </td>
          <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ p.project.name }}</td>
          <td class="whitespace-nowrap px-3 py-2 text-slate-600">
            {{ p.work_direction_label || '—' }}
            <span v-if="p.service_stage" class="text-xs text-slate-400">· {{ p.service_stage }}</span>
          </td>
          <td class="whitespace-nowrap px-3 py-2 text-right font-medium text-slate-800">{{ money(p.amount) }}</td>
          <td class="px-3 py-2">
            <ActStatusBadge :status="p.act.status" :label="p.act.status_label" />
          </td>
          <td class="px-3 py-2">
            <div class="flex gap-1">
              <button
                v-if="!p.act.is_sent"
                :class="[btn, 'border-slate-300 text-slate-700 hover:bg-slate-100']"
                @click="update(p, { is_sent: true })"
              >Отправить</button>
              <button
                v-if="p.act.is_sent && !p.act.is_signed"
                :class="[btn, 'border-emerald-300 text-emerald-700 hover:bg-emerald-50']"
                @click="update(p, { is_signed: true })"
              >Подписан</button>
              <button
                v-if="p.act.is_sent && !p.act.is_signed"
                :class="[btn, 'border-slate-300 text-slate-500 hover:bg-slate-100']"
                @click="update(p, { is_sent: false })"
              >Отозвать</button>
              <button
                v-if="p.act.is_signed"
                :class="[btn, 'border-slate-300 text-slate-500 hover:bg-slate-100']"
                @click="update(p, { is_signed: false })"
              >Переоткрыть</button>
            </div>
          </td>
        </tr>
        <tr v-if="!payments.length">
          <td colspan="7" class="px-3 py-6 text-center text-slate-400">Нет оплат по заданным фильтрам</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
