<template>
  <v-container class="pa-8" fluid>
    <v-card class="mx-auto pa-6" max-width="600" elevation="3" dark>
      <h1 class="text-h5 mb-6">Report a Bug</h1>

      <!-- Form -->
      <v-form ref="bugForm" lazy-validation>
        <!-- Bug Name -->
        <v-text-field
          v-model="bug.title"
          label="Bug Name"
          :rules="[v => !!v || 'Title is required']"
          outlined
          dense
          color="cyan lighten-2"
          class="mb-4"
        />

        <!-- Bug Description -->
        <v-textarea
          v-model="bug.description"
          label="Description"
          :rules="[v => !!v || 'Description is required']"
          outlined
          dense
          color="cyan lighten-2"
          class="mb-4"
        />

        <!-- Image / Video Upload -->
        <v-file-input
          v-model="bugImages"
          label="Attach Screenshots/Videos"
          accept="image/*,video/*"
          multiple
          show-size
          chips
          outlined
          dense
          color="cyan lighten-2"
          class="mb-4"
          :rules="[files => {
            if (!files || files.length === 0) return true
            return files.every(f => f.size < 100_000_000) || 'Each file must be <100MB'
          }]"
        />

        <!-- Preview -->
        <div class="media-preview" v-if="bugImages.length">
          <div
            v-for="(file, index) in bugImages"
            :key="index"
            class="preview-item"
          >
            <img v-if="file.type.startsWith('image/')" :src="previewUrls[index]" />
            <video v-else controls width="100">
              <source :src="previewUrls[index]" :type="file.type" />
            </video>
            <v-btn
              icon
              small
              color="red lighten-2"
              class="remove-btn"
              @click="removeFile(index)"
            >
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </div>

        <!-- Severity -->
        <v-select
          v-model="bug.severity"
          :items="severities"
          label="Severity"
          :rules="[v => !!v || 'Select severity']"
          outlined
          dense
          color="cyan lighten-2"
          class="mb-4"
        />

        <!-- Category -->
        <v-select
          v-model="bug.category_id"
          :items="categories"
          item-title="name"
          item-value="id"
          label="Category"
          :rules="[v => !!v || 'Select category']"
          outlined
          dense
          color="cyan lighten-2"
          class="mb-6"
        />

        <!-- Submit -->
        <v-btn color="cyan accent-4" class="white--text" @click="submitBug">
          Submit Bug
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { authState } from '@/authState'

const bugForm = ref(null)
const bugImages = ref([])

const bug = reactive({
  title: '',
  description: '',
  severity: null,
  category_id: null,
})

const severities = ['low', 'medium', 'high', 'critical']
const categories = ref([])

const previewUrls = computed(() =>
  bugImages.value.map(file => URL.createObjectURL(file))
)

// Remove single file
const removeFile = (index) => {
  bugImages.value.splice(index, 1)
}

// Fetch categories
const fetchCategories = async () => {
  if (!authState.token) return
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_URL}/api/categories`, {
      headers: { Authorization: `Bearer ${authState.token}` }
    })
    categories.value = res.data
  } catch (err) {
    console.error('Error fetching categories:', err)
  }
}

watch(() => authState.token, (newToken) => {
  if (newToken) fetchCategories()
})

onMounted(() => {
  if (authState.token) fetchCategories()
})

// Submit
const submitBug = async () => {
  if (!bugForm.value.validate() || !authState.token) return

  const payload = { ...bug, status: 'open', assigned_admin: null }

  try {
    const bugRes = await axios.post(`${import.meta.env.VITE_API_URL}/api/bugs`, payload, {
      headers: { Authorization: `Bearer ${authState.token}` }
    })

    const createdBug = bugRes.data

    for (const file of bugImages.value) {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('imageable_id', createdBug.id)
      formData.append('imageable_type', 'App\\Models\\Bug')
      await axios.post(`${import.meta.env.VITE_API_URL}/api/images`, formData, {
        headers: { 
          Authorization: `Bearer ${authState.token}`,
        }
      })
    }

    alert('Bug submitted successfully!')

    // Reset form
    Object.assign(bug, { title: '', description: '', severity: null, category_id: null })
    bugImages.value = []
    bugForm.value.resetValidation()

  } catch (err) {
    console.error(err)
    alert('Failed to submit bug.')
  }
}
</script>

<style scoped>
.v-card { background-color: #1e1e2f; color: #e0e0e0; }

.v-input input, .v-input textarea { color: #ffffff !important; }
.v-select .v-input__control { color: #ffffff !important; }
.v-label { color: #80deea !important; }

.media-preview {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
  position: relative;
}

.media-preview img,
.media-preview video {
  max-width: 120px;
  max-height: 80px;
  object-fit: cover;
  border-radius: 4px;
  pointer-events: none;
}

.preview-item {
  position: relative;
}

.remove-btn {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 20px;
  height: 20px;
}

.remove-btn .v-icon {
  font-size: 16px;
}
</style>