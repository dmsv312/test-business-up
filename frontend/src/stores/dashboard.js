import { defineStore } from 'pinia'
import { reactive, ref } from 'vue'
import api from '@/api/client'

/**
 * Единый стор дашборда: фильтры + загруженные данные. Фильтры применяются к
 * сводке и списку оплат на стороне backend (через те же query-параметры).
 */
export const useDashboardStore = defineStore('dashboard', () => {
  const filters = reactive({
    from: '', to: '', client_id: '', project_id: '', direction: '', act_status: '', q: '',
  })

  const summary = ref(null)
  const payments = ref([])
  const paymentsMeta = ref(null)
  const clients = ref([])
  const projects = ref([])
  const operations = ref([])
  const loading = ref(false)

  function activeParams() {
    const params = {}
    for (const [key, value] of Object.entries(filters)) {
      if (value !== '' && value != null) params[key] = value
    }
    return params
  }

  async function fetchSummary() {
    const { data } = await api.get('/dashboard/summary', { params: activeParams() })
    summary.value = data.data
  }

  async function fetchPayments(page = 1) {
    const { data } = await api.get('/payments', { params: { ...activeParams(), page } })
    payments.value = data.data
    paymentsMeta.value = data.meta
  }

  async function loadDashboard(page = 1) {
    loading.value = true
    try {
      await Promise.all([fetchSummary(), fetchPayments(page)])
    } finally {
      loading.value = false
    }
  }

  async function loadClients() {
    clients.value = (await api.get('/clients')).data.data
  }

  async function loadProjects() {
    clients.value.length || (await loadClients())
    projects.value = (await api.get('/projects')).data.data
  }

  async function loadOperations() {
    operations.value = (await api.get('/bank-operations')).data.data
  }

  async function updateAct(payment, payload) {
    const { data } = await api.patch(`/acts/${payment.act.id}`, payload)
    payment.act = data.data
    await fetchSummary()
  }

  function resetFilters() {
    Object.keys(filters).forEach((key) => { filters[key] = '' })
    return loadDashboard()
  }

  return {
    filters, summary, payments, paymentsMeta, clients, projects, operations, loading,
    loadDashboard, fetchPayments, loadClients, loadProjects, loadOperations, updateAct, resetFilters,
  }
})
