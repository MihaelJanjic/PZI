<template>
  <v-container>
    <v-card class="pa-5" max-width="600" outlined>
      <v-card-title>Log in</v-card-title>

      <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submitForm">
        <v-text-field 
          v-model="user.email" 
          label="E-mail" 
          type="email"
          :rules="[required, validEmail]" 
        />

        <v-text-field
          v-model="user.password"
          label="Password"
          :type="showPassword ? 'text' : 'password'"
          :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showPassword = !showPassword"
          :rules="[required]"
          required
        />

        <v-btn
          color="primary"
          :disabled="!valid || loading"
          :loading="loading"
          type="submit"
          class="mt-4"
        >
          Log in
        </v-btn>
      </v-form>

      <v-alert v-if="alert.show" :type="alert.type" class="mt-4" dense outlined>
        {{ alert.message }}
      </v-alert>
    </v-card>
  </v-container>
</template>






<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { authState } from '@/authState'

const router = useRouter()

const form = ref(null)
const valid = ref(false)
const loading = ref(false)
const showPassword = ref(false)

const user = ref({
  email: '',
  password: '',
})

const alert = ref({
  show: false,
  message: '',
  type: 'success'
})

const required = v => !!v || 'This field is required'
const validEmail = v => /.+@.+\..+/.test(v) || 'Must be a valid email'

const submitForm = async () => {
  if (!valid.value) return

  loading.value = true
  alert.value.show = false

  try {
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/login`,
      user.value
    )

    // Save token
    const { token, user: returnedUser } = response.data
    localStorage.setItem('auth_token', token)
    localStorage.setItem('auth_user', JSON.stringify(returnedUser))

    authState.user = returnedUser
    authState.token = token

    alert.value = {
      show: true,
      message: 'Logged in successfully!',
      type: 'success'
    }

    window.location.href = '/BugsPage'

    // Reset form
    user.value = { email: '', password: '' }
    form.value?.resetValidation()

  } catch (error) {
    let message = 'Login failed. Please try again.'

    if (error.response?.data?.message) {
      message = error.response.data.message
    }

    if (error.response?.data?.errors) {
      message = Object.values(error.response.data.errors).flat().join(' ')
    }

    alert.value = {
      show: true,
      message,
      type: 'error'
    }
  } finally {
    loading.value = false
  }
}
</script>