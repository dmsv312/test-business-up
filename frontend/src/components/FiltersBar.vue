<script setup>
import { watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from '@/stores/dashboard'
import { DIRECTIONS, ACT_STATUSES } from '@/constants'

const store = useDashboardStore()
const { filters, clients, projects } = storeToRefs(store)

const apply = () => store.loadDashboard()

// Поиск — с задержкой, чтобы не дёргать API на каждый символ.
let timer
watch(() => filters.value.q, () => {
  clearTimeout(timer)
  timer = setTimeout(apply, 350)
})

const field = 'rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:border-slate-400 focus:outline-none'
</script>

<template>
  <div class="flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">
    <label class="flex flex-col gap-1 text-xs text-slate-500">
      Период с
      <input v-model="filters.from" type="date" :class="field" @change="apply" />
    </label>
    <label class="flex flex-col gap-1 text-xs text-slate-500">
      по
      <input v-model="filters.to" type="date" :class="field" @change="apply" />
    </label>

    <label class="flex flex-col gap-1 text-xs text-slate-500">
      Юрлицо
      <select v-model="filters.client_id" :class="field" @change="apply">
        <option value="">Все</option>
        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </label>

    <label class="flex flex-col gap-1 text-xs text-slate-500">
      Проект
      <select v-model="filters.project_id" :class="field" @change="apply">
        <option value="">Все</option>
        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
    </label>

    <label class="flex flex-col gap-1 text-xs text-slate-500">
      Направление
      <select v-model="filters.direction" :class="field" @change="apply">
        <option value="">Все</option>
        <option v-for="d in DIRECTIONS" :key="d.value" :value="d.value">{{ d.label }}</option>
      </select>
    </label>

    <label class="flex flex-col gap-1 text-xs text-slate-500">
      Статус акта
      <select v-model="filters.act_status" :class="field" @change="apply">
        <option value="">Любой</option>
        <option v-for="s in ACT_STATUSES" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
    </label>

    <label class="flex flex-1 flex-col gap-1 text-xs text-slate-500">
      Поиск (назначение / клиент)
      <input v-model="filters.q" type="text" placeholder="например, SEO" :class="field" />
    </label>

    <button
      class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100"
      @click="store.resetFilters()"
    >
      Сбросить
    </button>
  </div>
</template>
