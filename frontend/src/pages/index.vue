<template>
  <v-container class="fill-height" max-width="900">
    <div>
      <v-img
        class="mb-4"
        height="150"
        src="@/assets/logo.png"
      />

      <div class="mb-8 text-center">
        <div class="text-body-2 font-weight-light mb-n1">Welcome to</div>
        <h1 class="text-h2 font-weight-bold">Bug Tracker</h1>
      </div>

      <v-row>
        <v-col cols="6">
          <v-card
            class="py-4"
            color="surface-variant"
            image="https://cdn.vuetifyjs.com/docs/images/one/create/feature.png"
            prepend-icon="mdi-rocket-launch-outline"
            rounded="lg"
            variant="tonal"
            @click="handleClick"
          >
            <template #image>
              <v-img position="top right" />
            </template>

            <template #title>
              <h2 class="text-h5 font-weight-bold">
                Report A Bug
              </h2>
            </template>

            <template #subtitle>
              <div class="text-subtitle-1">
                Help us improve.
              </div>
            </template>
          </v-card>
        </v-col>

        <v-col cols="6">
          <v-card
            class="py-4"
            color="surface-variant"
            image="https://cdn.vuetifyjs.com/docs/images/one/create/feature.png"
            prepend-icon="mdi-rocket-launch-outline"
            rounded="lg"
            variant="tonal"
            href="/BugsPage"
          >
            <template #image>
              <v-img position="top right" />
            </template>

            <template #title>
              <h2 class="text-h5 font-weight-bold">
                Browse Bugs
              </h2>
            </template>

            <template #subtitle>
              <div class="text-subtitle-1">
                See if your issue is already documented.
              </div>
            </template>
          </v-card>
        </v-col>

        <v-col cols="12" >
          <v-card elevation="2" class="pa-5">
          
            <h1 class="text-h4 font-weight-bold">Bug Tracker</h1>
            <p class="text-subtitle-1 mt-3" style="text-align: left;">
              &#9679 Report any bug you find with Bug Tracker. <br>
              &#9679 Allow our developers easy access to information about the bugs. <br>
              &#9679 Check the progress of your reports. <br>
              &#9679 Get in contact with our developers. <br>
            </p> <br>
          </v-card>
        </v-col>
      </v-row>

      <v-dialog v-model="showRegisterForm" max-width="600">
        <RegisterForm />
      </v-dialog>

      <v-dialog v-model="showLoginForm" max-width="600">
        <LoginForm />
      </v-dialog>
    </div>
  </v-container>
</template>


<script setup>
  import RegisterForm from '@/components/RegisterForm.vue'
  import LoginForm from '@/components/LoginForm.vue'
  import { authState } from '@/authState'
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'

  const router = useRouter()

  const showRegisterForm = ref(false)
  const showLoginForm = ref(false)

  const handleClick = () => {
  if (authState.token) {
    router.push("/BugReportPage")
  } else {
    authState.showLogin = true
  }
}
</script>
