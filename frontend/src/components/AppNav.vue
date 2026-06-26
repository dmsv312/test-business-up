<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const tabs = [
  { name: 'dashboard', label: 'Дашборд' },
  { name: 'projects', label: 'Проекты' },
  { name: 'clients', label: 'Клиенты' },
  { name: 'operations', label: 'Выписка' },
]
</script>

<template>
  <header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <div>
        <h1 class="text-lg font-semibold text-slate-900">Учёт оплат, проектов и актов</h1>
        <p class="text-sm text-slate-500">Digital-агентство · документооборот по проектам</p>
      </div>
      <nav class="flex flex-wrap gap-1">
        <router-link
          v-for="tab in tabs"
          :key="tab.name"
          :to="{ name: tab.name }"
          class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
          :class="route.name === tab.name
            ? 'bg-slate-900 text-white'
            : 'text-slate-600 hover:bg-slate-100'"
        >
          {{ tab.label }}
        </router-link>
      </nav>
    </div>
  </header>
</template>
