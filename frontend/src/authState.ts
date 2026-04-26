import { reactive } from 'vue'

const storedUser = localStorage.getItem('auth_user')

export const authState = reactive({
  token: localStorage.getItem('auth_token'),
  user: storedUser ? JSON.parse(storedUser) : null,
  showLogin: false,
  showRegister: false
})
