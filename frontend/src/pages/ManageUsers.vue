<template>
  <v-container class="manage-wrapper">
    <v-card class="manage-card" rounded="lg" elevation="2">
      <v-card-text>
        <div class="d-flex flex-column align-center text-center">
          <h2 class="text-h5 font-weight-bold mb-4">
            Change User Role
          </h2>

          <v-form @submit.prevent="submitForm" class="w-100">

            <v-text-field
              v-model="email"
              label="Enter User Email"
              variant="outlined"
              class="mb-4"
              clearable
            />

            <v-select
              v-model="selectedRole"
              :items="roles"
              label="Select New Role"
              variant="outlined"
              :disabled="!email"
              class="mb-6"
            />

            <v-btn
              color="primary"
              type="submit"
              :loading="loading"
              :disabled="!email || !selectedRole"
              block
            >
              Update Role
            </v-btn>

            <v-btn
              color="red-darken-2"
              prepend-icon="mdi-delete"
              class="mt-3"
              :loading="deleteLoading"
              :disabled="!email"
              block
              @click="openDeleteDialog"
            >
              Remove User
            </v-btn>

          </v-form>

          <v-alert
            v-if="message"
            :type="messageType"
            class="mt-4"
          >
            {{ message }}
          </v-alert>

        </div>
      </v-card-text>
    </v-card>

    <v-dialog v-model="deleteUserDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h6">Remove User</v-card-title>
        <v-card-text>
          <v-alert type="warning" variant="tonal" class="mb-3">
            This action will permanently remove the user <strong>{{ email }}</strong>. This cannot be undone!
          </v-alert>
          <div>Please type <strong>DELETE</strong> to confirm:</div>
          <v-text-field
            v-model="deleteUserConfirm"
            label="Type DELETE"
            outlined
            dense
          />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn text @click="deleteUserDialog = false">Cancel</v-btn>
          <v-btn
            color="error"
            :disabled="deleteUserConfirm !== 'DELETE'"
            :loading="deleteLoading"
            @click="confirmRemoveUser"
          >
            Confirm
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>


<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { authState } from '@/authState'

const email = ref('')
const selectedRole = ref(null)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

const deleteLoading = ref(false)

const roles = ['user', 'admin', 'super_admin']

watch(email, (val) => {
  if (!val) selectedRole.value = null
})

const submitForm = async () => {
  if (!email.value || !selectedRole.value) return

  try {
    loading.value = true
    message.value = ''

    const normalizedEmail = email.value.trim().toLowerCase()

    const res = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/users/change-role`,
      {
        email: normalizedEmail,
        user_type: selectedRole.value
      },
      {
        headers: {
          Authorization: `Bearer ${authState.token}`
        }
      }
    )

    message.value = res.data.message || 'Role updated successfully'
    messageType.value = 'success'

    email.value = ''
    selectedRole.value = null

  } catch (error) {
    message.value = error.response?.data?.message || 'Something went wrong'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}

// Delete user dialog
const deleteUserDialog = ref(false)
const deleteUserConfirm = ref('')

const openDeleteDialog = () => {
  deleteUserConfirm.value = ''
  deleteUserDialog.value = true
}

const confirmRemoveUser = async () => {
  if (deleteUserConfirm.value !== 'DELETE') return
  deleteUserDialog.value = false
  deleteLoading.value = true
  message.value = ''

  try {
    const normalizedEmail = email.value.trim().toLowerCase()
    const res = await axios.delete(
      `${import.meta.env.VITE_API_URL}/api/users/remove`,
      {
        data: { email: normalizedEmail },
        headers: { Authorization: `Bearer ${authState.token}` }
      }
    )

    message.value = res.data.message || 'User removed successfully'
    messageType.value = 'success'
    email.value = ''
    selectedRole.value = null
    deleteUserConfirm.value = ''

  } catch (error) {
    message.value = error.response?.data?.message || 'Failed to remove user'
    messageType.value = 'error'
  } finally {
    deleteLoading.value = false
  }
}
</script>


<style scoped>
.manage-wrapper {
  max-width: 600px;
  margin: 40px auto;
  padding: 0 16px;
}

.manage-card {
  padding: 24px;
}
</style>
