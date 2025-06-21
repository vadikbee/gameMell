<template>
  <div class="main-color">
    <div class="main-bg">
      <!-- Слой панели -->
      <div class="panel-layer"></div>
      
      <!-- Контент страницы -->
      <NuxtPage />
    </div>
  </div>
</template>

<style scoped>
.main-color {
  background-color: #1E031E;
  min-height: 100vh;
  position: relative; /* Для позиционирования */
}

.main-bg {
  background-image: url('/images/background/Frame 560.png');
  background-position: center;
  background-attachment: scroll;
  background-repeat: no-repeat;
  min-height: 100vh;
  overflow: hidden; /* Скрывает обрезанные части */
  background-size: contain; /* Важно: оригинальный размер */
  background-origin: content-box; /* Защищает от смещения */
  width: 100%;
  margin: 0 auto;
  position: relative; /* Для позиционирования слоев */
  z-index: 1; /* Основной фон под панелью */
}

/* Стили для панели */
.panel-layer {
  background-attachment: relative; /* скролится */
  position: fixed; /* Прижимаем к низу */
  bottom: 0; /* Прижимаем к низу */
  left: 0;  /* Прижимаем к низу */
  right: 0; /* Прижимаем к низу */
  background-size: auto; /* Важно: оригинальный размер */
  background-origin: content-box; /* Защищает от смещения */
  height: 120px; /* Высота панели */
    background-image: url('/images/2sloy/Rectangle 191.png'); /* Путь к изображению панели */
  overflow: hidden; /* Скрывает обрезанные части */
  background-position: center bottom;
  background-repeat: no-repeat;
  z-index: 2; /* Слой между фоном и контентом */
  max-height: 120px;
  min-height: 120px;
  flex-shrink: 0; /* Запрещаем изменение размера */
  /* Плавное появление */
  animation: fadeIn 0.8s ease-out forwards;
  
}

/* Гарантируем, что контент будет поверх панели */
.layout-page {
  position: relative;
  z-index: 3;
}

/* Анимация появления */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Адаптивность */
@media (max-width: 1024px) {
  .panel-layer {
    height: 100px;
  }
}

@media (max-width: 768px) {
  .panel-layer {
    height: 80px;
    background-size: contain;
  }
}

@media (max-width: 480px) {
  .panel-layer {
    height: 60px;
  }
}
</style>