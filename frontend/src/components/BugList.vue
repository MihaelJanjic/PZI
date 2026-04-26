<template>
  <div class="bug-list-wrapper">

    <v-row class="mb-4">
      <v-col cols="12" md="4">
        <v-text-field
          v-model="search"
          label="Search bugs"
          prepend-inner-icon="mdi-magnify"
          clearable
          @update:model-value="handleFilters"
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          v-model="selectedCategory"
          :items="categories"
          item-title="name"
          item-value="id"
          label="Category"
          clearable
          @update:model-value="handleFilters"
        />
      </v-col>

      <v-col cols="12" md="4">
        <v-select
          v-model="selectedSeverity"
          :items="severities"
          label="Severity"
          clearable
          @update:model-value="handleFilters"
        />
      </v-col>
    </v-row>

    <BugTemplate
      v-for="bug in bugs"
      :key="bug.id"
      :bug="bug"
      @open="openBug"
      @require-login="showLoginForm = true"
      class="mb-4"
    />

    <div class="d-flex justify-center">
      <v-pagination
        v-model="page"
        :length="totalPages"
        @update:model-value="fetchBugs"
        class="mt-6"
      />
    </div>

  </div>
</template>

<script setup>
import { ref, watch } from "vue"
import BugTemplate from "@/components/BugTemplate.vue"
import { authState } from "@/authState.ts"
import { onMounted } from "vue"

const props = defineProps({
  userOnly: { type: Boolean, default: false },
  assignedAdmin: { type: [String, Number, null], default: null }
})

const bugs = ref([])
const page = ref(1)
const totalPages = ref(1)
const limit = 10

const search = ref("")
const categories = ref([])
const selectedCategory = ref(null)

const severities = ["low", "medium", "high", "critical"]
const selectedSeverity = ref(null)

const fetchBugs = async () => {
  const params = new URLSearchParams()

  params.append("page", page.value)
  params.append("limit", limit)

  if (props.userOnly && authState.user) {
    params.append("user_id", authState.user.id)
  }

  if (props.assignedAdmin) {
    params.append("assigned_admin", props.assignedAdmin)
  }

  if (search.value) {
    params.append("search", search.value)
  }

  if (selectedCategory.value) {
    params.append("category_id", selectedCategory.value)
  }

  if (selectedSeverity.value) {
    params.append("severity", selectedSeverity.value)
  }

  const url = `${import.meta.env.VITE_API_URL}/api/bugs?${params.toString()}`

  try {
    const res = await fetch(url, {
      headers: authState.token
        ? { Authorization: `Bearer ${authState.token}` }
        : {}
    })

    const data = await res.json()

    bugs.value = data.data
    totalPages.value = data.last_page
  } catch (err) {
    console.error("Failed to fetch bugs:", err)
    bugs.value = []
    totalPages.value = 1
  }
}

const handleFilters = () => {
  page.value = 1
  fetchBugs()
}

const fetchCategories = async () => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/api/categories`)
    const data = await res.json()
    categories.value = data
  } catch (err) {
    console.error("Failed to fetch categories:", err)
  }
}

watch([page, () => props.userOnly, () => props.assignedAdmin], fetchBugs, {
  immediate: true
})

const openBug = bug => {
  console.log("Open bug details:", bug)
}

onMounted(() => {
  fetchCategories()
})
</script>

<style scoped>
.bug-list-wrapper {
  width: 100%;
  max-width: 980px;
  margin: 0 auto;
  padding: 0 16px;
}

@media (min-width: 960px) {
  .bug-list-wrapper {
    padding: 0;
  }
}
</style>