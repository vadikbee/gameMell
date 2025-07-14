import { createI18n } from 'vue-i18n'
import en from './en.json'
import ru from './ru.json'  // Добавлен русский язык

const messages = {
  en,
  ru  // Только английский и русский
}

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages,
  globalInjection: true,
  warnHtmlInMessage: 'off'
})

export default i18n