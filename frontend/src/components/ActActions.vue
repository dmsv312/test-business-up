<script setup>
import { useDashboardStore } from '@/stores/dashboard'

// Кнопки управления актом. Логика одна (store.updateAct), презентаций две —
// строка таблицы (десктоп) и карточка (мобильный) переиспользуют этот компонент.
const props = defineProps({
  payment: { type: Object, required: true },
})

const store = useDashboardStore()
const update = (payload) => store.updateAct(props.payment, payload)

const btn = 'rounded border px-2 py-0.5 text-xs font-medium whitespace-nowrap'
</script>

<template>
  <div class="flex flex-wrap gap-1">
    <button
      v-if="!payment.act.is_sent"
      :class="[btn, 'border-slate-300 text-slate-700 hover:bg-slate-100']"
      @click="update({ is_sent: true })"
    >Отправить</button>
    <button
      v-if="payment.act.is_sent && !payment.act.is_signed"
      :class="[btn, 'border-emerald-300 text-emerald-700 hover:bg-emerald-50']"
      @click="update({ is_signed: true })"
    >Подписан</button>
    <button
      v-if="payment.act.is_sent && !payment.act.is_signed"
      :class="[btn, 'border-slate-300 text-slate-500 hover:bg-slate-100']"
      @click="update({ is_sent: false })"
    >Отозвать</button>
    <button
      v-if="payment.act.is_signed"
      :class="[btn, 'border-slate-300 text-slate-500 hover:bg-slate-100']"
      @click="update({ is_signed: false })"
    >Переоткрыть</button>
  </div>
</template>
