// nuxt.config.ts
import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  devServer: {
    port: 3000,
    host: 'localhost'
  },

  // Эта конфигурация полезна, чтобы не писать '/api' каждый раз
  runtimeConfig: {
    public: {
      apiBase: '/api'
    }
  },
  
  // Оставляем только этот способ для прокси в режиме разработки
  nitro: {
    devProxy: {
      // Ключ - это префикс, который мы хотим перенаправить
      '/api': {
        // Указываем, куда перенаправлять запросы
        target: 'http://localhost:8000/api', // URL вашего Laravel-сервера
        changeOrigin: true, // Важно для корректной работы прокси
      }
    }
  },

  // Все остальное оставляем как было
  devtools: {
    enabled: true,
    vscode: {},
    timeline: {
      enabled: true
    }
  },

  ssr: false,

  experimental: {
    componentIslands: true,
    asyncContext: true
  },

  vue: {
    compilerOptions: {
      isCustomElement: (tag) => tag.startsWith('Suspense')
    }
  },

  modules: [
    '@nuxt/devtools'
  ]
})