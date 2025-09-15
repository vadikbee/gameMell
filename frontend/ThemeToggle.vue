
<template>
  <div class="theme-toggle" @click="toggleTheme">
    <div class="theme-icon">{{ isDark ? '🌙' : '☀️' }}</div>
    
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const isDark = ref(false);

// Применение темы
const applyTheme = (dark) => {
  const body = document.body;
  if (dark) {
    body.classList.add('dark-theme');
    body.classList.remove('light-theme');
  } else {
    body.classList.add('light-theme');
    body.classList.remove('dark-theme');
  }
};

// Переключение темы
const toggleTheme = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  applyTheme(isDark.value);
};

// Инициализация темы при загрузке
onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    isDark.value = savedTheme === 'dark';
  } else {
    // Определение предпочтений системы
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = prefersDark;
  }
  applyTheme(isDark.value);
});
</script>

<style scoped>
.theme-toggle {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 0px 0px;
  border-radius: 20px;
  background: rgba(0, 0, 0, 0.1);
  left: 15px;
  transition: all 0.3s ease;
}

.theme-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
}

.theme-icon {
  transform: translate(20%, -0%);
  position: absolute;
  
}


/* Адаптация для темной темы */
:global(.dark-theme) .theme-toggle {
  background: rgba(0, 0, 0, 0.2);
}

:global(.dark-theme) .theme-toggle:hover {
  background: rgba(0, 0, 0, 0.3);
}
</style>
