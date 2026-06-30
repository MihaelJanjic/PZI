<template>
  <v-container class="bug-page" fluid>
    <v-row justify="center">
      <v-col cols="12" md="10" lg="8">

        <!-- LOADING -->
        <v-skeleton-loader v-if="loading" type="card" class="mb-6" />

        <!-- ERROR -->
        <v-alert v-else-if="error" type="error" variant="tonal" class="mb-6">
          {{ error }}
        </v-alert>

        <!-- BUG DETAILS -->
        <v-card v-else class="bug-card pa-6 mb-6" rounded="lg" elevation="4">
          <div class="d-flex justify-space-between align-start mb-4">
            <!-- TITLE AND REPORTER -->
            <div>
              <h1 class="text-h5 font-weight-bold">{{ bug.title }}</h1>
              <div class="text-caption text-grey">Reported by {{ bug.created_by?.name }}</div>
            </div>

            <!-- STATUS + BUTTON + ASSIGNED ADMIN -->
            <div class="d-flex align-start">
              <!-- FIX BUG BUTTON -->
              <v-btn
                v-if="canCycleStatus"
                :color="statusColor(nextStatus)"
                :loading="cycling"
                :disabled="loading"
                class="me-3"
                @click="cycleStatus"
              >
                Set to {{ nextStatus }}
              </v-btn>

              <!-- STATUS + ASSIGNED ADMIN -->
              <div class="d-flex flex-column align-start">
                <!-- Wrap chip in a div so it doesn’t stretch -->
                <div style="display: inline-block;">
                  <v-chip size="small" :color="statusColor(bug.status)" variant="tonal">
                    {{ bug.status }}
                  </v-chip>
                </div>

                <!-- Assigned Admin -->
                <div class="text-caption text-grey mt-1" v-if="bug.assigned_admin">
                  Assigned to: {{ bug.assigned_admin?.name || '–' }}
                </div>
              </div>
            </div>
          </div>

          <v-divider class="my-4" />

          <!-- DESCRIPTION -->
          <p class="bug-description">{{ bug.description }}</p>

          <!-- BUG MEDIA -->
          <div v-if="bug.images?.length" class="mt-4">
            <v-row dense>
              <v-col v-for="media in bug.images" :key="media.id" cols="12" sm="6" md="4">
                <template v-if="media.type?.startsWith('video')">
                  <video :src="fileUrl(media.id)" controls class="rounded-lg" style="width: 100%;" />
                </template>
                <template v-else>
                  <v-img :src="fileUrl(media.id)" aspect-ratio="1" class="rounded-lg" contain />
                </template>
              </v-col>
            </v-row>
          </div>
        </v-card>

        <!-- COMMENTS -->
        <v-card v-if="!loading && !error" class="comments-card pa-6" rounded="lg">
          <h2 class="text-h6 mb-4">Comments</h2>

          <!-- NO COMMENTS -->
          <div v-if="comments.length === 0 && !loadingComments" class="text-grey text-caption">
            No comments yet.
          </div>

          <!-- COMMENTS LIST -->
          <div v-for="comment in comments" :key="comment.id" class="comment-item mb-4">
            <div class="font-weight-bold">{{ comment.user?.name }}</div>
            <div class="text-caption text-grey mb-2">{{ formatDate(comment.created_at) }}</div>
            <div class="mb-2">{{ comment.comment }}</div>

            <!-- COMMENT MEDIA -->
            <div v-if="comment.images?.length">
              <v-row dense>
                <v-col v-for="media in comment.images" :key="media.id" cols="12" sm="6" md="4">
                  <template v-if="media.type?.startsWith('video')">
                    <video :src="fileUrl(media.id)" controls style="max-width: 300px; border-radius: 8px;" />
                  </template>
                  <template v-else>
                    <v-img :src="fileUrl(media.id)" max-width="300" class="rounded-lg" contain />
                  </template>
                </v-col>
              </v-row>
            </div>
          </div>

          <!-- LOAD MORE BUTTON -->
          <div v-if="currentPage < lastPage" class="text-center my-4">
            <v-btn color="primary" :loading="loadingComments" @click="loadMoreComments">
              Load More
            </v-btn>
          </div>

          <v-divider class="my-4" />

          <!-- NEW COMMENT -->
          <v-textarea
            v-model="newComment"
            label="Write a comment..."
            auto-grow
            variant="outlined"
            class="mb-3"
          />

          <!-- FILE UPLOAD -->
          <v-file-input
            v-model="commentFiles"
            label="Attach Screenshots / Videos (max 100MB each)"
            accept="image/*,video/*"
            multiple
            show-size
            chips
            variant="outlined"
            class="mb-3"
            :rules="[validateFiles]"
          />

          <v-btn color="primary" :loading="posting" :disabled="!canSubmit" @click="addComment">
            Post Comment
          </v-btn>
        </v-card>

      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { authState } from '@/authState'

definePage({ meta: { requiresAuth: true } })

const route = useRoute()
const bugId = route.params.id

const API_BASE = import.meta.env.VITE_API_URL + "/api"

const bug = ref(null)
const comments = ref([])
const loading = ref(true)
const error = ref(null)

const newComment = ref('')
const commentFiles = ref([])
const posting = ref(false)
const cycling = ref(false)

const currentPage = ref(1)
const lastPage = ref(1)
const perPage = 5
const loadingComments = ref(false)

const MAX_FILE_SIZE = 100 * 1024 * 1024

const fileUrl = (id) => `${API_BASE}/images/file/${id}`

const validateFiles = files => {
  if (!files || files.length === 0) return true
  return files.every(f => f.size <= MAX_FILE_SIZE) || 'Each file must be under 100MB'
}
const canSubmit = computed(() => newComment.value.trim() || commentFiles.value.length > 0)

/* ---------------- PERMISSIONS & STATUS CYCLE ---------------- */
const canCycleStatus = computed(() => {
  if (!bug.value || !authState.user) return false

  const user = authState.user
  const next = nextStatus.value

  // Only admins or super_admin can change status
  if (!['admin', 'super_admin'].includes(user.user_type)) return false

  // Only assigned admin or super_admin can resolve or close
  if (['resolved', 'closed'].includes(next)) {
    return bug.value.assigned_admin?.id === user.id || user.user_type === 'super_admin'
  }

  return true
})

const nextStatus = computed(() => {
  if (!bug.value) return ''
  switch (bug.value.status) {
    case 'open': return 'in_progress'
    case 'in_progress': return 'resolved'
    case 'resolved': return 'open'
    case 'closed': return 'open'
    default: return 'open'
  }
})

const cycleStatus = async () => {
  if (!bug.value) return
  cycling.value = true

  let payload = { status: nextStatus.value }

  // Auto-assign current user when moving to in_progress
  if (nextStatus.value === 'in_progress' && !bug.value.assigned_admin) {
    payload.assigned_admin = authState.user.id
  }

  try {
    const res = await fetch(`${API_BASE}/bugs/${bug.value.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authState.token}`
      },
      body: JSON.stringify(payload)
    })

    if (!res.ok) throw new Error('Failed to update bug')
    bug.value = await res.json()
  } catch (err) {
    console.error(err)
  } finally {
    cycling.value = false
  }
}

/* ---------------- FETCH BUG & COMMENTS ---------------- */
const fetchBug = async () => {
  try {
    loading.value = true
    const res = await fetch(`${API_BASE}/bugs/${bugId}`, {
      headers: { Authorization: `Bearer ${authState.token}` }
    })
    if (!res.ok) throw new Error('Failed to load bug')
    bug.value = await res.json()
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const fetchComments = async (page = 1) => {
  try {
    loadingComments.value = true
    const res = await fetch(`${API_BASE}/bugs/${bugId}/comments?page=${page}&per_page=${perPage}`, {
      headers: { Authorization: `Bearer ${authState.token}` }
    })
    if (!res.ok) throw new Error('Failed to load comments')
    const result = await res.json()
    if (page === 1) comments.value = result.data || result
    else comments.value.push(...(result.data || result))
    currentPage.value = result.current_page || result.meta?.current_page || page
    lastPage.value = result.last_page || result.meta?.last_page || 1
  } catch (err) {
    console.error(err)
  } finally {
    loadingComments.value = false
  }
}

const loadMoreComments = () => {
  if (currentPage.value < lastPage.value) fetchComments(currentPage.value + 1)
}

/* ---------------- ADD COMMENT ---------------- */
const addComment = async () => {
  if (!canSubmit.value) return
  try {
    posting.value = true
    let commentId = null
    if (newComment.value.trim()) {
      const res = await fetch(`${API_BASE}/bugs/${bugId}/comments`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authState.token}`
        },
        body: JSON.stringify({ comment: newComment.value })
      })
      if (!res.ok) throw new Error('Failed to post comment')
      const createdComment = await res.json()
      commentId = createdComment.id
    }
    for (const file of commentFiles.value) {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('imageable_id', commentId)
      formData.append('imageable_type', 'App\\Models\\Comment')
      const res = await fetch(`${API_BASE}/images`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${authState.token}` },
        body: formData
      })
      if (!res.ok) console.error('Failed to upload file', file.name)
    }
    await fetchComments(1)
    newComment.value = ''
    commentFiles.value = []
  } catch (err) {
    console.error(err)
  } finally {
    posting.value = false
  }
}

/* ---------------- HELPERS ---------------- */
const formatDate = date => new Date(date).toLocaleString('de-DE')
const statusColor = status => {
  switch (status) {
    case 'open': return 'yellow'
    case 'in_progress': return 'orange'
    case 'resolved': return 'green'
    case 'closed': return 'red'
    default: return 'grey'
  }
}

onMounted(async () => {
  await fetchBug()
  await fetchComments(1)
})
</script>

<style scoped>
.bug-page { background: #121212; min-height: 100vh; padding-top: 40px; }
.bug-card, .comments-card { background: #1a1a1a; color: #e0e0e0; }
.comment-item { background: #222; padding: 16px; border-radius: 8px; }
.bug-description { line-height: 1.6; color: #ccc; }
</style>