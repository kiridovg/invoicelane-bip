import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-08-21',
  devtools: { enabled: true },
  telemetry: false,

  modules: ['@pinia/nuxt'],

  css: ['~/assets/css/main.css'],

  typescript: {
    strict: true,
    typeCheck: false,
  },

  vite: {
    plugins: [tailwindcss()],
  },

  runtimeConfig: {
    apiInternalUrl: import.meta.env.NUXT_API_INTERNAL_URL || 'http://localhost:8000',
  },
})
