<template>
  <v-container>
    <v-card class="pa-5" max-width="600" outlined>
      <v-card-title>Register</v-card-title>

      <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submitForm">
        <v-text-field 
          v-model="user.name" 
          label="Name" 
          :rules="[required]" 
          required 
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

        <v-text-field 
          v-model="user.email" 
          label="E-mail" 
          type="email" :rules="[required, validEmail]" 
        />

        <v-btn 
          color="primary" 
          :disabled="!valid || loading" 
          :loading="loading"
          type="submit"
          class="mt-4"
        >
          Register
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
  name: '',
  email: '',
  password: ''
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
      `${import.meta.env.VITE_API_URL}/api/register`,
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
      message: 'Registered successfully!',
      type: 'success'
    }

    router.push('/BugsPage')

    // Reset form
    user.value = { name: '', email: '', password: '' }
    form.value?.resetValidation()

  } catch (error) {
    console.error(error.response?.data || error)

    alert.value = {
      show: true,
      message: error.response?.data?.message ||
               error.response?.data?.errors?.[0] ||
               'Registration failed. Please try again.',
      type: 'error'
    }
  }
}
</script>

