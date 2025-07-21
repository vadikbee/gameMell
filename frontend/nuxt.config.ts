import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  devServer: {
    port: 3000,
    host: 'localhost'
  },
  
  // ИСПРАВЛЕННАЯ КОНФИГУРАЦИЯ ПРОКСИ
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://localhost:8000/api',
        changeOrigin: true,
        prependPath: true,
      }
    }
  },
  
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
  ],
  
  runtimeConfig: {
    public: {
      apiBase: '/api'
    }
  }
})