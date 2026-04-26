<template>
  <div>
    <br><br>
    <BugList 
      :userOnly="userOnly" 
      :assignedAdmin="assignedAdmin"
      :search="search"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import BugList from '@/components/BugList.vue'

const route = useRoute()

const userOnly = ref(false)
const assignedAdmin = ref(null)
const search = ref("")

watch(
  () => route.query,
  (query) => {
    userOnly.value = query.userHistory === 'true'
    assignedAdmin.value = query.assigned_admin || null
    search.value = query.search || ""
  },
  { immediate: true }
)
</script>
