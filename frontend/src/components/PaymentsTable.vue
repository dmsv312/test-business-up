<script setup>
import ActStatusCell from '@/components/ActStatusCell.vue'
import ActActions from '@/components/ActActions.vue'
import CommentCell from '@/components/CommentCell.vue'
import { money, date } from '@/format'

// Одни данные — две презентации: таблица на десктопе (>=lg) и карточки на узких
// экранах (<lg). Логика актов/комментария вынесена в под-компоненты, поэтому
// дублируется только обёртка, а не поведение. Колонка «Проект» убрана: проект
// 1:1 равен клиенту (одна запись на клиента), поэтому дублировал его название.
defineProps({
  payments: { type: Array, default: () => [] },
})
</script>

<template>
  <!-- Десктоп: таблица. table-fixed + фикс. ширины убирают горизонтальный скролл. -->
  <div class="hidden overflow-x-auto rounded-lg border border-slate-200 bg-white lg:block">
    <table class="w-full table-fixed divide-y divide-slate-200 text-sm">
      <colgroup>
        <col class="w-28" />
        <col />
        <col class="w-36" />
        <col class="w-32" />
        <col class="w-44" />
        <col />
      </colgroup>
      <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-3 py-2">Дата</th>
          <th class="px-3 py-2">Клиент / назначение</th>
          <th class="px-3 py-2">Направление</th>
          <th class="px-3 py-2 text-right">Сумма</th>
          <th class="px-3 py-2">Акт</th>
          <th class="px-3 py-2">Комментарий и действия</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="p in payments" :key="p.id" class="align-top hover:bg-slate-50">
          <td class="whitespace-nowrap px-3 py-2 align-top text-slate-600">{{ date(p.payment_date) }}</td>
          <td class="px-3 py-2 align-top">
            <div class="font-medium text-slate-800">{{ p.client.name }}</div>
            <div class="line-clamp-2 text-xs text-slate-400" :title="p.payment_purpose">{{ p.payment_purpose }}</div>
          </td>
          <td class="px-3 py-2 align-top text-slate-600">
            {{ p.work_direction_label || '—' }}
            <div v-if="p.service_stage" class="text-xs text-slate-400">{{ p.service_stage }}</div>
          </td>
          <td class="whitespace-nowrap px-3 py-2 align-top text-right font-medium text-slate-800">{{ money(p.amount) }}</td>
          <td class="px-3 py-2 align-top"><ActStatusCell :act="p.act" /></td>
          <td class="px-3 py-2 align-top">
            <div class="flex flex-col gap-2">
              <CommentCell :payment="p" />
              <ActActions :payment="p" />
            </div>
          </td>
        </tr>
        <tr v-if="!payments.length">
          <td colspan="6" class="px-3 py-6 text-center text-slate-400">Нет оплат по заданным фильтрам</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Мобильный/планшет: карточки (<lg). Горизонтального скролла нет в принципе. -->
  <div class="space-y-3 lg:hidden">
    <div
      v-for="p in payments"
      :key="p.id"
      class="rounded-lg border border-slate-200 bg-white p-4"
    >
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="font-medium text-slate-800">{{ p.client.name }}</div>
          <div class="text-xs text-slate-400">{{ date(p.payment_date) }}</div>
        </div>
        <div class="whitespace-nowrap text-right font-semibold text-slate-800">{{ money(p.amount) }}</div>
      </div>

      <div class="mt-2 space-y-1">
        <div class="text-xs text-slate-500">{{ p.payment_purpose }}</div>
        <div class="text-sm text-slate-600">
          {{ p.work_direction_label || '—' }}
          <span v-if="p.service_stage" class="text-slate-400">· {{ p.service_stage }}</span>
        </div>
      </div>

      <div class="mt-3"><ActStatusCell :act="p.act" /></div>

      <div class="mt-3 space-y-2 border-t border-slate-100 pt-3">
        <CommentCell :payment="p" />
        <ActActions :payment="p" />
      </div>
    </div>

    <div v-if="!payments.length" class="rounded-lg border border-slate-200 bg-white px-3 py-6 text-center text-slate-400">
      Нет оплат по заданным фильтрам
    </div>
  </div>
</template>
