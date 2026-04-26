<template>
  <v-app>
    <v-app-bar
      color="rgba(10, 10, 10, 0.95)"
      dark
      elevate-on-scroll
      app
      height="64"
    >
      <v-container>
        <v-row
          align="center"
          justify="space-between"
          class="w-100"
          no-gutters
        >

          <!-- LEFT (desktop only) -->
          <v-col class="d-flex align-center" cols="auto">
            <v-btn text v-if="!mdAndDown" @click="$router.push('/BugsPage')">Home</v-btn>
            <v-btn text v-if="!mdAndDown" @click="authState.showLogin = true">Profile</v-btn>
          </v-col>

          <!-- TITLE -->
          <v-col class="text-center" cols="auto">
            <h1 class="text-h6 font-weight-bold mb-0">Bug Tracker</h1>
          </v-col>

          <!-- RIGHT -->
          <v-col class="d-flex justify-end align-center" cols="auto">

            <!-- Desktop -->
            <v-btn text v-if="!mdAndDown" @click="authState.showLogin = true">Login</v-btn>
            <v-btn text v-if="!mdAndDown" @click="authState.showRegister = true">Register</v-btn>

            <!-- Mobile menu -->
            <v-menu v-if="mdAndDown" bottom left>
              <template #activator="{ props }">
                <v-btn icon v-bind="props">
                  <v-icon>mdi-menu</v-icon>
                </v-btn>
              </template>

              <v-list>
                <v-list-item @click="$router.push('/BugsPage')">
                  <v-list-item-title>Home</v-list-item-title>
                </v-list-item> 
                <v-list-item @click="authState.showLogin = true">
                  <v-list-item-title>Profile</v-list-item-title>
                </v-list-item>
                <v-list-item @click="authState.showLogin = true">
                  <v-list-item-title>Login</v-list-item-title>
                </v-list-item>
                <v-list-item @click="authState.showRegister = true">
                  <v-list-item-title>Register</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>

          </v-col>
        </v-row>
      </v-container>
    </v-app-bar>

    <v-main>
      <slot />
    </v-main>

    <v-dialog v-model="authState.showRegister" max-width="600">
      <RegisterForm />
    </v-dialog>

    <v-dialog v-model="authState.showLogin" max-width="600">
      <LoginForm />
    </v-dialog>
  </v-app>
</template>

<script setup>
import RegisterForm from '@/components/RegisterForm.vue'
import LoginForm from '@/components/LoginForm.vue'
import { useDisplay } from 'vuetify'
import { authState } from '@/authState'

const { mdAndDown } = useDisplay()
</script>
