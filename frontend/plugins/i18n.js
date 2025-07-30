import { createI18n } from 'vue-i18n'
import en from '~/en.json'
import ru from '~/ru.json'

export default defineNuxtPlugin((nuxtApp) => {
  const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: { en, ru },
    globalInjection: true,
    warnHtmlInMessage: 'off'
  })

  nuxtApp.vueApp.use(i18n)
  return {
    provide: { i18n }
  }
})