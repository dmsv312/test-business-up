import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '@/views/DashboardView.vue'
import ProjectsView from '@/views/ProjectsView.vue'
import ClientsView from '@/views/ClientsView.vue'
import OperationsView from '@/views/OperationsView.vue'

export default createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'dashboard', component: DashboardView, meta: { title: 'Дашборд' } },
    { path: '/projects', name: 'projects', component: ProjectsView, meta: { title: 'Проекты' } },
    { path: '/clients', name: 'clients', component: ClientsView, meta: { title: 'Клиенты' } },
    { path: '/operations', name: 'operations', component: OperationsView, meta: { title: 'Выписка' } },
  ],
})
