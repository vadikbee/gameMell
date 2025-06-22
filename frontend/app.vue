<template>
  <div class="main-color">
    <div class="main-bg">
      
       <div 
        v-for="(btn, index) in winButtons" 
        :key="index"
        class="button-win"
        :class="`button-win-${index + 1}`"
        @click="handleWinClick(btn.id)"
      ></div>
      <div class="tarakan-1"></div>
      <!-- Слой панели -->
      <div class="panel-layer">
        <div class="button-1"></div>
        <div class="button-2"></div>
        <div class="button-3"></div>

        <div class="panel-up">
          <!-- Элементы размещены внутри контейнера -->
          <div class="balance-container">
            <div class="balance-text">Balance</div>
            <div class="button-balans">228</div>
          </div>
            <div class="icon-button">
              <div class="icon-1"></div>
            </div>
        </div>
      </div>
      
      
      <!-- Контент страницы -->
      <NuxtPage />
    </div>
  </div>
</template>
<script setup>
// Реактивные данные для кнопок
const winButtons = ref([
  { id: 1, top: 690, right: 333 },
  { id: 2, top: 650, right: 300 },
  { id: 3, top: 720, right: 300 },
  { id: 4, top: 650, right: 366 },
  { id: 5, top: 720, right: 366 },
  { id: 6, top: 600, right: 333 },
  { id: 7, top: 770, right: 333 },
]);

// Обработчик клика
const handleWinClick = (btnId) => {
  console.log(`Clicked win button ${btnId}`);
  // Ваша логика обработки клика
};
</script>
<style scoped>
/* Стили для черной менюшки */
.panel-up {
  position: absolute;
  width: 390px;
  height: 43px;
  left: calc(50% - 390px/2);
  top: -695px; /* Фиксированная позиция вверху экрана */
  z-index: 30;
  background: #000000;
  /*border: 1px solid red !important; /* Для отладки */
}

/* Контейнер для элементов баланса */
.balance-container {
  position: relative;
  width: 100%;
  height: 100%; 
  gap: 10px; /* Расстояние между элементами */
}

.main-color {
  background-color: #1E031E;
  min-height: 100vh;
  position: relative; /* Для позиционирования */
}
.tarakan-1{
   background-image: url('/images/tarakani/Property 1=Default (1).png');
  background-size: contain;
  background-repeat: no-repeat;
  position: absolute;
  width: 32px; /* Предположительные размеры */
  height: 38px;
  top: 250px; /* Пример позиции */
  left: 50px; /* Пример позиции */
  z-index: 15;
}
.button-win {
  /* border: 1px solid red !important; /* Временная рамка */
  /* Остальные стили */
  background-image: url('/images/buttons/Property 1=Default.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  position: absolute;
  width: 52px;
  height: 64px;
     /* Новые координаты - более логичные значения */
  z-index: 25; /* Больше чем у других элементов */
    cursor: pointer;
  transition: all 0.3s ease;
}

.button-win-1 { top: 687px; right: 4px; }
.button-win-2 { top: 687px; right: 59px; }
.button-win-3 { top: 687px; right: 114px; }
.button-win-4 { top: 687px; right: 169px; }
.button-win-5 { top: 687px; right: 224px; }
.button-win-6 { top: 687px; right: 279px; }
.button-win-7 { top: 687px; right: 334px; }
/* Текст "Balance" */

/* ОБНОВЛЕННЫЕ СТИЛИ ДЛЯ ЭЛЕМЕНТОВ БАЛАНСА */
.balance-text {
  position: absolute;
  /* Используем CSS-переменные для позиционирования */
  top: var(--text-top, 16px); /* Значение по умолчанию 10px */
  right: var(--text-right, 120px); /* Значение по умолчанию 80px */
  
  /* Стили текста */
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 350;
  font-size: 10px;
  line-height: 12px;
  text-transform: uppercase;
  color: #FFFFFF;
  text-align: right;
  white-space: nowrap;
  z-index: 20;
  
}
.icon-1 {
  background-image: url('/images/icons/Group.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center; /* Центрирование фонового изображения */
  
  /* Размеры иконки должны быть меньше кнопки */
  width: 24px;  /* Уменьшаем ширину */
  height: 17px; /* Уменьшаем высоту */
  
  /* Убираем лишние отступы */
  margin: 0;
  padding: 0;
}
.icon-button {
  position: absolute;
  top: 7px; /* Позиция как у иконки */
  right: 10px;
  z-index: 20;
  
  /* Размеры черного квадрата */
  width: 30px; 
  height: 30px;
  
  /* Черный фон */
  background-color: #000000;
  border-radius: 8px; /* Небольшое скругление углов */
  
  /* Центрирование содержимого */
  display: flex;
  justify-content: center;
  align-items: center;
  
  /* Эффекты */
  box-shadow: 0 2px 4px rgba(0,0,0,0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}
.main-bg {
  background-image: url('/images/background/Frame 560.png');
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
  
  /* Фиксированные размеры контейнера */
  width: 390px;
  min-height: 844px;
  height: 100vh;
  max-height: 844px;
  
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  z-index: 1;
}
.button-1 {
  position: absolute;
  background-image: url('/images/buttons/Group 256.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  
  height: 50px;
  cursor: pointer;
  z-index: 11;
  
  /* Точное позиционирование без трансформаций */
  top: 22px;
  left: 8px; /* Фиксированный отступ от левого края */
}
.button-2 {
   position: absolute;
  background-image: url('/images/buttons/Group 168.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 170px;
  height: 100px;
  cursor: pointer;
  z-index: 11;
  
  /* Точное позиционирование без трансформаций */
  top: 22px;
  right: 110px; /* Фиксированный отступ от правого края */
}
.button-3 {
   position: absolute;
  background-image: url('/images/buttons/Group 255.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  
  height: 50px;
  cursor: pointer;
  z-index: 11;
  
  /* Точное позиционирование без трансформаций */
  top: 22px;
  left: 291px; /* Фиксированный отступ от левого края */
}

/* Стили для панели */
.panel-layer {
position: absolute;
  width: 390px; /* Фиксированная ширина */
  height: 93px;
  left: 0; /* Прижимаем к левому краю */
  bottom: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, #000000 100%);
  z-index: 10;
  
}


.button-balans {
  position: absolute;
  /* Используем CSS-переменные для позиционирования */
  top: var(--button-top, 10px); /* Значение по умолчанию 10px */
  right: var(--button-right, 45px); /* Значение по умолчанию 20px */
  
  /* Стили кнопки */
  display: inline-flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 49px;
  height: 19px;
  min-width: 49px;
  min-height: 19px;
  padding: 3px 6px;
  background: linear-gradient(180deg, #7F00FE 0%, #66008F 98.9%);
  box-shadow: 0px 1px 0px #191E47;
  border-radius: 49px;
  color: white;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  z-index: 20;
}


/* Эффекты при наведении */
.button-1:hover, .button-2:hover, .button-3:hover {
  filter: 
    brightness(1.1) /* Увеличение яркости */
    drop-shadow(0 0 8px rgba(178, 56, 58, 0.8));
}

/* Эффект при нажатии */
.button-1:active, .button-2:active {
  transform: scale(0.98);
  filter: brightness(0.95);
}
/* Гарантируем, что контент будет поверх панели */
/* Адаптивность для маленьких экранов */
/* Адаптация для мобильных */
@media (max-width: 390px) {
  .black-menu {
    gap: 20px; /* Уменьшаем расстояние между элементами */
    padding: 4px 10px; /* Уменьшаем боковые отступы */
    left: 0;
    right: 0;
  }
}
@media (max-width: 390px) {
  .main-bg {
    width: 100vw;
    max-width: 390px;
    background-size: contain;
  }
  
  .panel-layer {
    width: 100%;
  }
  
  /* Уменьшаем кнопки пропорционально */
  .button-1 {
    width: 70px;
    height: 40px;
    top: 20px;
    left: 15px;
  }
  
  .button-2 {
    width: 130px;
    height: 40px;
    top: 20px;
    right: 15px;
  }

  .button-3 {
    width: 130px;
    height: 40px;
    top: 20px;
    right: 15px;
  }
}

/* Адаптивность по высоте */
@media (max-height: 844px) {
  .main-bg {
    height: 100vh;
    max-height: none;
  }
}

</style>
