<script setup>
import ActStatusBadge from '@/components/ActStatusBadge.vue'

// Статус акта: производная плашка + два явных признака ТЗ («акт отправлен» /
// «акт подписан»). Признаки берутся прямо из is_sent/is_signed, поэтому всегда
// согласованы со статусом (status считает ActStatusService на backend).
defineProps({
  act: { type: Object, required: true },
})
</script>

<template>
  <div class="flex flex-col items-start gap-1">
    <ActStatusBadge :status="act.status" :label="act.status_label" />
    <div class="text-[11px] leading-tight">
      <span :class="act.is_sent ? 'text-emerald-600' : 'text-slate-400'">
        {{ act.is_sent ? '✓ отправлен' : '○ не отправлен' }}
      </span>
      <template v-if="act.is_sent">
        <span class="text-slate-300"> · </span>
        <span :class="act.is_signed ? 'text-emerald-600' : 'text-slate-400'">
          {{ act.is_signed ? '✓ подписан' : '○ не подписан' }}
        </span>
      </template>
    </div>
  </div>
</template>
