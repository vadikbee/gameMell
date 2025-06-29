import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  // Настройки Nuxt
  devServer: {
    port: 3000,
    host: 'localhost'
  },
  
  // Настройка прокси для API - ИСПРАВЛЕННАЯ ВЕРСИЯ
  routeRules: {
    '/api/**': {
      proxy: 'http://localhost:8000/**'
    }
  },
  
  // Настройки для DevTools
  devtools: {
    enabled: true,
    vscode: {},
    timeline: {
      enabled: true
    }
  },
  
  // Отключите SSR если не нужно
  ssr: false,
  
  // Экспериментальные фичи (обновленные)
  experimental: {
    componentIslands: true,
    asyncContext: true
  },
  
  // Настройки для работы с Vue
  vue: {
    compilerOptions: {
      // Отключите предупреждение для Suspense
      isCustomElement: (tag) => tag.startsWith('Suspense')
    }
  },
  
  // Настройки модулей
  modules: [
    '@nuxt/devtools'
  ],
  
  // Настройки сборки
  build: {
    transpile: [
      // Добавьте сюда пакеты, которые нужно транспилировать
    ]
  },
  
  // Полифиллы для Node.js (исправленные)
  nitro: {
    esbuild: {
      options: {
        target: 'esnext'
      }
    }
  },
  
  // Настройки времени выполнения
  runtimeConfig: {
    public: {
      apiBase: '/api' // Базовый путь для API
    }
  }
})