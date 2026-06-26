<script setup>
import AppNav from '@/components/AppNav.vue'
</script>

<template>
  <div class="min-h-screen">
    <AppNav />
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
      <router-view />
    </main>
  </div>
</template>
