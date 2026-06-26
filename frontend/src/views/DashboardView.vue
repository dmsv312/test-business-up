<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from '@/stores/dashboard'
import SummaryCards from '@/components/SummaryCards.vue'
import FiltersBar from '@/components/FiltersBar.vue'
import PaymentsTable from '@/components/PaymentsTable.vue'

const store = useDashboardStore()
const { summary, payments, paymentsMeta, loading } = storeToRefs(store)

onMounted(async () => {
  await store.loadClients()
  await store.loadDashboard()
})

const goToPage = (page) => store.fetchPayments(page)
</script>

<template>
  <div class="space-y-5">
    <SummaryCards :summary="summary" />
    <FiltersBar />

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">
          Оплаты <span v-if="paymentsMeta" class="text-slate-400">({{ paymentsMeta.total }})</span>
        </h2>
        <span v-if="loading" class="text-xs text-slate-400">Загрузка…</span>
      </div>

      <PaymentsTable :payments="payments" />

      <div v-if="paymentsMeta && paymentsMeta.last_page > 1" class="flex items-center justify-end gap-2 text-sm">
        <button
          class="rounded border border-slate-300 px-2.5 py-1 disabled:opacity-40"
          :disabled="paymentsMeta.current_page <= 1"
          @click="goToPage(paymentsMeta.current_page - 1)"
        >Назад</button>
        <span class="text-slate-500">{{ paymentsMeta.current_page }} / {{ paymentsMeta.last_page }}</span>
        <button
          class="rounded border border-slate-300 px-2.5 py-1 disabled:opacity-40"
          :disabled="paymentsMeta.current_page >= paymentsMeta.last_page"
          @click="goToPage(paymentsMeta.current_page + 1)"
        >Вперёд</button>
      </div>
    </div>
  </div>
</template>
