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
            <v-btn text v-if="!mdAndDown" @click="$router.push('/Profile')">Profile</v-btn>
          </v-col>

          <!-- TITLE -->
          <v-col class="text-center" cols="auto">
            <h1 class="text-h6 font-weight-bold mb-0">Bug Tracker</h1>
          </v-col>

          <!-- RIGHT -->
          <v-col class="d-flex justify-end align-center" cols="auto">

            <!-- Desktop -->
            <v-btn text v-if="!mdAndDown" @click="$router.push('/BugReportPage')">Report A Bug</v-btn>
            <v-btn text v-if="!mdAndDown" @click="logout">Logout</v-btn>

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
                <v-list-item @click="$router.push('/Profile')">
                  <v-list-item-title>Profile</v-list-item-title>
                </v-list-item>
                <v-list-item @click="$router.push('/BugReportPage')">
                  <v-list-item-title>Report A Bug</v-list-item-title>
                </v-list-item>
                <v-list-item @click="logout">
                  <v-list-item-title>Logout</v-list-item-title>
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
  </v-app>
</template>

<script setup>
import { useDisplay } from 'vuetify'

const { mdAndDown } = useDisplay()

const logout = () => {
  localStorage.removeItem('auth_token')
  location.href = '/'
}
</script>
