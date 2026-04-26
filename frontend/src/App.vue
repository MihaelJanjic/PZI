<template>
  <component :is="layout">
    <router-view />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { authState } from '@/authState'

import AuthLayout from './layouts/AuthLayout.vue'
import MainLayout from './layouts/MainLayout.vue'

const layout = computed(() => {
  return authState.token ? AuthLayout : MainLayout
})
</script>
