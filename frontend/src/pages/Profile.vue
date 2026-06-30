<template>
  <v-container class="profile-wrapper" v-if="user">
    <v-card class="profile-card" rounded="lg" elevation="2">
      <v-card-text>
        <div class="d-flex flex-column align-center text-center">
          <!-- Name & Role -->
          <h2 class="text-h5 font-weight-bold">{{ user?.name }}</h2>
          <div class="text-caption text-grey mb-6">{{ user?.role }}</div>

          <!-- Buttons Row -->
          <v-row class="mb-2" justify="center" dense>
            <!-- Report History -->
            <v-col cols="auto">
              <v-btn color="primary" @click="goToReportHistory">
                My Reports
              </v-btn>
            </v-col>

            <!-- Delete Account (non-super_admin only) -->
            <v-col cols="auto" v-if="user?.user_type !== 'super_admin'">
              <v-btn color="red-darken-2" @click="openDeleteAccountDialog">
                Delete Account
              </v-btn>
            </v-col>

            <!-- Assigned Bugs (admin or super_admin) -->
            <v-col cols="auto" v-if="isAdminOrSuperAdmin">
              <v-btn color="secondary" @click="goToAssignedBugs">
                Assigned Bugs
              </v-btn>
            </v-col>

            <!-- Close Resolved Bugs (admin or super_admin) -->
            <v-col cols="auto" v-if="isAdminOrSuperAdmin">
              <v-btn color="success" @click="openCloseDialog">
                Close Resolved Bugs
              </v-btn>
            </v-col>

            <!-- Manage Users (super_admin only) -->
            <v-col cols="auto" v-if="isSuperAdmin">
              <v-btn color="error" @click="goToManageUsers">
                Manage Users
              </v-btn>
            </v-col>

            <!-- Delete Closed Bugs (super_admin only) -->
            <v-col cols="auto" v-if="isSuperAdmin">
              <v-btn color="error" variant="outlined" @click="openDeleteDialog">
                Delete Closed Bugs
              </v-btn>
            </v-col>

            <!-- Manage Categories (super_admin only) -->
            <v-col cols="auto" v-if="isSuperAdmin">
              <v-btn color="purple" @click="openCategoryDialog">
                Manage Categories
              </v-btn>
            </v-col>
          </v-row>
        </div>

        <!-- Close Resolved Bugs Dialog -->
        <v-dialog v-model="closeDialog" max-width="400">
          <v-card>
            <v-card-title class="text-h6">Close Resolved Bugs</v-card-title>
            <v-card-text>
              <div class="mb-3">
                Close resolved bugs older than how many days?
              </div>
              <v-text-field v-model="days" type="number" label="Days" min="1" outlined dense />
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn text @click="closeDialog = false">Cancel</v-btn>
              <v-btn color="success" :loading="closing" @click="confirmCloseResolved">
                Confirm
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Account Dialog -->
        <v-dialog v-model="deleteAccountDialog" max-width="400">
          <v-card>
            <v-card-title class="text-h6">Delete Account</v-card-title>
            <v-card-text>
              <v-alert type="warning" variant="tonal" class="mb-3">
                This action will permanently delete your account. This cannot be undone!
              </v-alert>
              <div>Please type <strong>DELETE</strong> to confirm:</div>
              <v-text-field v-model="deleteAccountConfirm" label="Type DELETE" outlined dense />
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn text @click="deleteAccountDialog = false">Cancel</v-btn>
              <v-btn
                color="error"
                :disabled="deleteAccountConfirm !== 'DELETE'"
                :loading="deletingAccount"
                @click="confirmDeleteAccount"
              >
                Confirm
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Closed Bugs Dialog -->
        <v-dialog v-model="deleteDialog" max-width="400">
          <v-card>
            <v-card-title class="text-h6">Delete Closed Bugs</v-card-title>
            <v-card-text>
              <div class="mb-3">Delete closed bugs older than how many days?</div>
              <v-alert type="warning" variant="tonal" class="mb-3">
                This action permanently deletes bugs.
              </v-alert>
              <v-text-field v-model="deleteDays" type="number" label="Days" min="1" outlined dense />
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn text @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="confirmDeleteClosed">
                Confirm
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Manage Categories Dialog -->
        <v-dialog v-model="categoryDialog" max-width="500">
          <v-card>
            <v-card-title class="text-h6">Manage Categories</v-card-title>
            <v-card-text>
              <v-text-field v-model="newCategoryName" label="New Category Name" outlined dense class="mb-3" />
              <v-btn color="primary" @click="createCategory" :loading="creating">
                Create Category
              </v-btn>
              <v-divider class="my-4"></v-divider>
              <div v-if="categories.length">
                <div class="mb-2 font-weight-medium">Existing Categories:</div>
                <v-chip
                  v-for="cat in categories"
                  :key="cat.id"
                  class="ma-1"
                  closable
                  @click:close="deleteCategory(cat.id)"
                >
                  {{ cat.name }}
                </v-chip>
              </div>
              <div v-else>No categories yet.</div>
            </v-card-text>
            <v-card-actions class="justify-end">
              <v-btn text @click="categoryDialog = false">Close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-card-text>
    </v-card>
  </v-container>

  <!-- Fallback if user is null -->
  <div v-else class="text-center mt-12">Loading profile...</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authState } from '@/authState'
import axios from 'axios'

const router = useRouter()
const user = authState.user

// Permission checks
const isSuperAdmin = computed(() => user?.user_type === 'super_admin')
const isAdminOrSuperAdmin = computed(() => ['admin', 'super_admin'].includes(user?.user_type))

// Navigation functions
const goToReportHistory = () => router.push('/BugsPage?userHistory=true')
const goToAssignedBugs = () => router.push({ path: '/BugsPage', query: { assigned_admin: user?.id } })
const goToManageUsers = () => router.push('/ManageUsers')

// --- Close Resolved Bugs ---
const closeDialog = ref(false)
const days = ref(7)
const closing = ref(false)
const openCloseDialog = () => { days.value = 7; closeDialog.value = true }

const confirmCloseResolved = async () => {
  if (!days.value || days.value < 1) return
  closing.value = true
  try {
    await axios.post(`${import.meta.env.VITE_API_URL}/api/admin/bugs/close-resolved`,
      { older_than_days: days.value },
      { headers: { Authorization: `Bearer ${authState.token}` } }
    )
    closeDialog.value = false
  } catch (err) { console.error(err) } finally { closing.value = false }
}

// --- Delete Closed Bugs ---
const deleteDialog = ref(false)
const deleteDays = ref(7)
const deleting = ref(false)
const openDeleteDialog = () => { deleteDays.value = 7; deleteDialog.value = true }
const confirmDeleteClosed = async () => {
  if (!deleteDays.value || deleteDays.value < 1) return
  deleting.value = true
  try {
    await axios.post(`${import.meta.env.VITE_API_URL}/api/superadmin/bugs/delete-closed`,
      { older_than_days: deleteDays.value },
      { headers: { Authorization: `Bearer ${authState.token}` } }
    )
    deleteDialog.value = false
  } catch (err) { console.error(err) } finally { deleting.value = false }
}

// --- Manage Categories ---
const categoryDialog = ref(false)
const newCategoryName = ref('')
const categories = ref([])
const creating = ref(false)
const openCategoryDialog = async () => { categoryDialog.value = true; await loadCategories() }
const loadCategories = async () => {
  try {
    const res = await axios.get(
      `${import.meta.env.VITE_API_URL}/api/superadmin/categories`, 
      { headers: { Authorization: `Bearer ${authState.token}` } }
    )
    categories.value = res.data || []
  } catch (err) { console.error(err) }
}
const createCategory = async () => {
  if (!newCategoryName.value.trim()) return
  creating.value = true
  try {
    const res = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/superadmin/categories`, 
      { name: newCategoryName.value }, { headers: { Authorization: `Bearer ${authState.token}` } }
    )
    categories.value.push(res.data)
    newCategoryName.value = ''
  } catch (err) { console.error(err) } finally { creating.value = false }
}
const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category?')) return
  try {
    await axios.delete(
      `${import.meta.env.VITE_API_URL}/api/superadmin/categories/${id}`, 
      { headers: { Authorization: `Bearer ${authState.token}` } }
    )
    categories.value = categories.value.filter(c => c.id !== id)
  } catch (err) { console.error(err) }
}

// --- Delete Account ---
const deleteAccountDialog = ref(false)
const deleteAccountConfirm = ref('')
const deletingAccount = ref(false)
const openDeleteAccountDialog = () => { deleteAccountConfirm.value = ''; deleteAccountDialog.value = true }

const confirmDeleteAccount = async () => {
  if (!user?.id) return
  deletingAccount.value = true
  try {
    await axios.delete(`${import.meta.env.VITE_API_URL}/api/users/${user.id}`, {
      headers: { Authorization: `Bearer ${authState.token}` }
    })
    authState.user = null
    authState.token = null
    router.push('/')
  } catch (err) { console.error(err) } finally {
    deletingAccount.value = false
    deleteAccountDialog.value = false
  }
}
</script>

<style scoped>
.profile-wrapper { max-width: 600px; margin: 40px auto; padding: 0 16px; }
.profile-card { padding: 24px; transition: transform 0.25s ease, box-shadow 0.25s ease; }
.profile-card:hover { transform: scale(1.01); }
.text-grey { color: #7a7a7a; }
</style>