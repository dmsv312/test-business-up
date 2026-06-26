<script setup>
import { ACT_STATUS_STYLE } from '@/constants'

defineProps({
  status: { type: String, required: true },
  label: { type: String, required: true },
})
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
    :class="ACT_STATUS_STYLE[status] || 'bg-slate-100 text-slate-600 ring-slate-200'"
  >
    {{ label }}
  </span>
</template>
