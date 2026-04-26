<template>
  <v-card
    class="bug-card mb-4"
    rounded="lg"
    elevation="2"
    @click="handleClick"
  >
    <v-row no-gutters>
      <!-- LEFT "POSTER" -->
      <v-col cols="2" class="severity-panel" :class="bug.severity">
        <div class="severity-text">
          {{ bug.severity.toUpperCase() }}
        </div>
      </v-col>

      <!-- MAIN CONTENT -->
      <v-col cols="9" class="pa-4">
        <div class="d-flex justify-space-between align-start">
          <div>
            <h2 class="text-h6 font-weight-bold mb-1">
              {{ bug.title }}
            </h2>
            <div class="text-caption text-grey">
              Reported by {{ bug.created_by.name }}
            </div>
          </div>

          <v-chip
            size="small"
            :color="statusColor(bug.status)"
            variant="tonal"
          >
            {{ bug.status }}
          </v-chip>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2 justify-space-between">
          <v-chip class="chip-space" size="small" color="#7a7a7a" variant="outlined">
            {{ bug.category.name }}
          </v-chip>

          <div class="text-caption text-grey">
            Last updated: {{ new Date(bug.updated_at).toLocaleDateString('de-DE') }}.
          </div>
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { defineEmits } from 'vue'
import { authState } from '@/authState'

const router = useRouter()

const props = defineProps({
  bug: Object
})

const emit = defineEmits(['open'])

const handleClick = () => {
  if (authState.token) {
    router.push(`/bugs/${props.bug.id}`)
  } else {
    authState.showLogin = true
  }
}

const statusColor = status => {
  switch (status) {
    case 'open': return 'yellow'
    case 'in_progress': return 'orange'
    case 'resolved': return 'green'
    case 'closed': return 'red'
    default: return 'grey'
  }
}


</script>

<style scoped>
.bug-card {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.bug-card:hover {
  transform: scale(1.015);
}

.severity-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1.2rem;
}

.severity-panel.critical {
  background: #b71c1c;
}

.severity-panel.high {
  background: #e65100;
}

.severity-panel.medium {
  background: #f9a825;
}

.severity-panel.low {
  background: #2e7d32;
}

.severity-text {
  letter-spacing: 2px;
}

.chip-space {
  margin-right: 12px;
}
</style>
