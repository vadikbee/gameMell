<!-- StavkiMenu.vue -->
<template>
    <div class="menu-stavki" :class="customClass">
  <div class="menu-stavki">
    <div v-if="showNextRaceNotice" class="next-race-notice">
      {{ nextRaceNoticeText }}
    </div>
    
    <!-- Основное изображение меню -->
    <img src="/images/menus/stavki.png" alt="Stavki" class="menu-image stavki-image">
    
    <!-- Контейнер для всех элементов управления ставками -->
    <div class="stavki-content-container">
      
      <!-- Контейнер для кнопок управления -->
      <div class="bet-controls-container">
        <img 
          src="/images/buttons/otmena.png" 
          alt="Otmena"
          class="control-button otmena-button"
          @click="$emit('otmena-click')"
        >
        <div 
          class=" x2-button"
          @click="handleX2Click"
        >
          Х2
        </div>
        <img 
          src="/images/buttons/reset.png" 
          alt="Reset" 
          class="control-button reset-button"
          @click="$emit('reset-click')"
        >
        
        <!-- === НАЧАЛО НОВОГО БЛОКА КНОПОК === -->
        

<div class="control-button group-164-button" @click="handleGroup164Click">

    <!-- Шаг 2: ВНУТРИ родителя размещаем логику v-if / v-else -->

    <!-- Вариант для классического режима -->
    <div v-if="gameMode === 'classic'" class="classic-bet-button-container">
        <!-- Задний слой: фон кнопки -->
        <img 
            src="/images/menus/Group 164.png" 
            alt="Bet button background" 
            class="button-background-image"
        >
        <!-- Передний слой: изображение текста -->
        <img 
            src="/images/menus/Rectangle.png" 
            alt="Place Bet" 
            class="bet-text-image"
        >
    </div>

    <!-- Вариант для остальных режимов -->
    <div v-else class="action-bet-button-container">
        <img 
            src="/images/menus/Frame 165.png" 
            alt="Action Bet BG" 
            class="action-button-image"
        >
        <span class="action-button-text">
            {{ gameMode === 'random' ? t('Random Bet') : t('Favorite Bet') }}
        </span>
    </div>

</div>
</div>
      
      <!-- Контейнер для счетчика ставок -->
      <div class="bet-counter-container">
        <div 
          class="bet-button minus" 
          @mousedown="$emit('decrement-start')"
          @mouseup="$emit('stop-action')"
          @mouseleave="$emit('stop-action')"
        ></div>
        <div class="bet-display">{{ currentBet }}</div>
        <div 
          class="bet-button plus" 
          @mousedown="$emit('increment-start')"
          @mouseup="$emit('stop-action')"
          @mouseleave="$emit('stop-action')"
        ></div>
      </div>
      
      <!-- Контейнер для кнопок ставок -->
      <div class="stavki-buttons-container">
        <div 
          v-for="button in stavkiButtons"
          :key="button.id"
          class="stavki-button"
          @click="handleButtonClick(button.amount)"
        >
          {{ button.amount }}
        </div>
        
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
// 1. ИМПОРТИРУЕМ useI18n
import { useI18n } from 'vue-i18n';
import { computed, onMounted } from 'vue'; // `computed` у вас уже должен быть, если нет - добавьте.

// 2. ИНИЦИАЛИЗИРУЕМ ФУНКЦИЮ ПЕРЕВОДА t
const { t } = useI18n();
const { data: gameConfig } = useFetch('/api/gameplay/games/instances/cockroaches-space-maze');
// Сохраняем результат defineProps в переменную
const props = defineProps({
  currentBet: Number,
  minBet: Number,
  maxBet: Number,
  showNextRaceNotice: Boolean,
  nextRaceNoticeText: String,
  betStep: Number,
  currency: String,
  playBetClick: Function,
  playStakeActionClick: Function, // Добавляем эту строку
  context: String, // Добавьте эту строку
    customClass: {
    type: String,
    default: ''
  },
   
   gameMode: {
    type: String,
    default: 'classic' // Режим по умолчанию
  }
});
// ДОБАВЬТЕ ЭТОТ БЛОК:
onMounted(() => {
  console.log('StavkiMenu загружено. Текущий режим игры:', props.gameMode);
});
const emit = defineEmits([
  'decrement-start',
  'increment-start',
  'stop-action',
  'add-bet',
  'x2-click',
  'win-bet-click',
  'group164-click', // Для 'classic'
  'random-bet-click', // Для 'random'
  'favorite-bet-click' // Для 'favorite'
]);

// Используем параметры из API
const stavkiButtons = computed(() => {
  // Добавляем проверку на 'options' для надежности
  if (!gameConfig.value || !gameConfig.value.options) return [];
  return gameConfig.value.options.bet_buttons.map(amount => ({
    id: amount.toString(),
    amount: amount
  }));
});

// ОБНОВЛЕННЫЙ обработчик клика по главной кнопке
const handleGroup164Click = () => {
  props.playStakeActionClick();
  
  if (props.gameMode === 'random') {
    emit('random-bet-click');
  } else if (props.gameMode === 'favorite') { // <-- ОБНОВЛЕНА ЛОГИКА
    emit('favorite-bet-click');
  } else if (props.context === 'win') {
    emit('win-bet-click');
  } else {
    emit('group164-click');
  }
};
const handleButtonClick = (amount) => {
  props.playStakeActionClick(); // Добавляем звук
  emit('add-bet', amount);
};

// Обработчик для кнопки X2
const handleX2Click = () => {
  props.playStakeActionClick(); // Добавляем звук
  emit('x2-click', props.currentBet);
};
</script>

<style scoped>
.menu-stavki {
  position: relative;
  width: 100%;
  max-width: 360px;
  
  z-index: 10;
  transform-origin: top center;
  transition: all 0.3s ease;
  
}

/* Основное изображение меню */
.stavki-image {
  width: 100%;
  max-width: 370px;
  height: auto;
  object-fit: contain;
  
}

/* Контейнер для элементов управления ставками */
.bet-controls-container {
  position: absolute;
  width: 100%;
  height: 60px; /* Фиксированная высота для контейнера управления */
  top: 55%; /* Поднимаем контейнер над изображением */
}

/* Счетчик ставок */
.bet-counter-container {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 12;
  width: 60%;
  max-width: 200px;
}

/* Кнопки быстрых ставок */
.stavki-buttons-container {
  position: absolute;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  width: 67%;
  top: 10px;
  
  right: 117px;
  z-index: 9;
  padding: 0 10px;
  box-sizing: border-box;
}

.group-164-button {
  position: absolute;
  bottom: 35px;
  right: 5px;
  z-index: 15; /* Высокий z-index, чтобы быть поверх всего */
  cursor: pointer;
  width: 112px;
  height: 68px;
  /* background свойство полностью удалено! */
}

.control-button {
  transition: transform 0.3s ease, filter 0.3s ease;
}
.control-button:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Новая кнопка для режима Random Bet */
.random-bet-button {
  background: url('/images/menus/Frame 165.png') no-repeat center center;
  background-size: contain;
  border: none;
  color: white;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 16px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.6);
}

/* Кнопка для Favorite (наследует стили, но можно задать свои) */
.favorite-bet-button {
  background: url('/images/menus/Group 164.png') no-repeat center center;
  background-size: contain;
  border: none;
  color: white;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 16px;
  text-shadow: 0 1px 3px rgba(0,0,0,0.6);
}

/* Уведомление о следующей гонке */
.next-race-notice {
  position: absolute;
  top: -30px;
  left: 0;
  right: 0;
  text-align: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 20px;
  color: #FFD700;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
  z-index: 15;
  animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
  0% { opacity: 0.7; transform: scale(1); }
  100% { opacity: 1; transform: scale(1.05); }
}

/* Кнопка Отмена */
.otmena-button {
  position: absolute;
  top: 0px;
  left: 2%;
  z-index: 10;
  cursor: pointer;
  width: 30px;
  height: 28px;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.otmena-button:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Кнопка Сброс */
.reset-button {
  position: absolute;
  top: 0px;
  right: 125px;
  z-index: 10;
  cursor: pointer;
  width: 44px;
  height: 21px;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.reset-button:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Стили для контейнера меню ставок в центре */
/* Стили для центрального меню */


/* Контейнер для кнопки режима Random/Favorite */
.action-bet-button-container {
  /* position: relative - это база для абсолютного позиционирования текста */
  position: relative; 
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Фоновое изображение кнопки */
.action-button-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

/* Стили для самого текста */
.action-button-text {
  /* position: absolute - вынимает текст из потока и позволяет разместить его поверх картинки */
  position: absolute;

  /* Классический трюк для идеального центрирования */
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  
  /* Дополнительные стили для красоты и читаемости */
  width: 100%; /* Занимает всю ширину, чтобы text-align работал */
  text-align: center;
  color: white;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 14px;
  text-transform: uppercase;
  text-shadow: 0 1px 3px rgba(0,0,0,0.7);
  
  /* Важно: чтобы текст не мешал клику по кнопке */
  pointer-events: none; 
}
/* Стили для меню ставок в центральном меню *//* Стили для меню ставок в центральном меню */
/* Кнопки быстрых ставок */
.stavki-button {
  display: flex;
  
  justify-content: center;
  align-items: center;
  width: 47px;
  height: 24px;
  background: rgba(0, 0, 0, 0.9);
  box-shadow: 3px 6px 4px rgba(0, 0, 0, 0.4);
  border-radius: 4px;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 400;
  font-size: 16px;
  color: #FFFFFF;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
}

.stavki-button:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Кнопка X2 */
.x2-button {
  position: absolute;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 44px;
  height: 22px;
  top: 0px;
  left: 42.5px;
  
  background: rgba(0, 0, 0, 0.9);
  box-shadow: 3px 6px 4px rgba(0, 0, 0, 0.4);
  border-radius: 4px;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 400;
  font-size: 16px;
  color: #FFFFFF;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
}

.x2-button:hover {
  transform: scale(1.05);
  filter: brightness(1.1) drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Кнопки +/- */
.bet-button {
  width: 23px;
  height: 23px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  
}

.bet-button.minus {
  left: 14px;
  top: 50px;
  position: absolute;
  z-index: 10;
  background-image: url('/images/buttons/kryg-pravo.png');
}

.bet-button.plus {
  top: 50px;
  right: 100px;
  
  position: absolute;
  z-index: 10;
  background-image: url('/images/buttons/kryg-leva.png');
}

.bet-button:hover {
  transform: scale(1.1);
  filter: drop-shadow(0 0 3px rgba(255, 255, 0, 0.8));
}

.bet-button:active {
  transform: scale(0.95);
}

/* Дисплей текущей ставки */
.bet-display {
  width: 65px;
  position: absolute;
  height: 23px;
  background-image: url('/images/buttons/seredina.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 17px;
  color: #000000;
  top: 50px;
  left: 25px;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.8);
}
/* Контейнер для слоев классической кнопки */
.classic-bet-button-container {
  position: relative; /* Создает контекст для позиционирования слоев */
  width: 100%;
  height: 100%;
}
/* Стиль для заднего слоя (фон кнопки) */
.button-background-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  z-index: 1; /* Находится сзади */
}

/* Стиль для переднего слоя (текст-картинка) */
.bet-text-image {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Идеально центрирует */
  
  /* Здесь можно задать точный размер картинки с текстом, если нужно */
  /* Например: width: 80px; height: 20px; */

  z-index: 2; /* Находится спереди */
}
/* Адаптация для планшетов */

/* Адаптация для мобильных устройств */

/* Адаптация для очень маленьких экранов */

</style>