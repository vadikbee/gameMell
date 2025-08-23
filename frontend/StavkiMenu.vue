<!-- StavkiMenu.vue -->
<template>
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
        
        <img 
          src="/images/menus/Group 164.png" 
          alt="Group 164" 
          class="control-button group-164-button"
          @click="handleGroup164Click"
          @mousedown="isGroup164Clicked = true"
          @mouseup="isGroup164Clicked = false"
          @mouseleave="isGroup164Clicked = false"
        >
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
</template>

<script setup>
const { data: gameConfig } = useFetch('/api/v1/gameplay/games/instances/cockroaches-space-maze');
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
  context: String // Добавьте эту строку
});

const emit = defineEmits([
  'otmena-click',
  'reset-click',
  'group164-click',
  'decrement-start',
  'increment-start',
  'stop-action',
  'add-bet',
  'x2-click',
  'win-bet-click'
]);

// Используем параметры из API
const stavkiButtons = computed(() => {
  if (!gameConfig.value) return [];
  
  return gameConfig.value.bet_buttons.map(amount => ({
    id: amount.toString(),
    amount: amount
  }));
});

// Новые методы внутри компонента StavkiMenu
const handleGroup164Click = () => {
  if (props.playBetClick) {
    props.playBetClick();
  }
  
  props.context === 'win' 
    ? emit('win-bet-click') 
    : emit('group164-click');
};

const handleButtonClick = (amount) => {
  if (props.playBetClick) {
    props.playBetClick();
  }
  emit('add-bet', amount);
};

// Обработчик для кнопки X2
const handleX2Click = () => {
  if (props.playBetClick) {
    props.playBetClick();
  }
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
  gap: 6px;
  width: 100%;
  top: 10px;
  
  right: 90px;
  z-index: 9;
  padding: 0 10px;
  box-sizing: border-box;
}

/* Кнопка Group 164 (основная кнопка ставки) */
.group-164-button {
  position: absolute;
  bottom: 15px;
  right: -15px ;
  transform: translateX(-50%);
  z-index: 8;
  cursor: pointer;
  width: 112px;
  height: 68px;
  transition: transform 0.3s ease, filter 0.3s ease;
}

.group-164-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
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
  top: 10px;
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
  top: 11px;
  left: 105px;
  z-index: 10;
  cursor: pointer;
  width: 49px;
  height: 25px;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.reset-button:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

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
  width: 47px;
  height: 24px;
  top: 11px;
  left: 50px;
  
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
  right: 60px;
  top: 45px;
  position: absolute;
  z-index: 10;
  background-image: url('/images/buttons/kryg-pravo.png');
}

.bet-button.plus {
  top: 45px;
  left: 62px;
  position: absolute;
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
  width: 70px;
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
  top: 45px;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.8);
}

/* Адаптация для планшетов */
@media (max-width: 768px) {
  .menu-stavki {
    max-width: 300px;
  }
  
  .stavki-buttons-container {
    
    gap: 6px;
  }
  
  .stavki-button {
    width: 42px;
    height: 22px;
    font-size: 14px;
  }
  
  .x2-button {
    width: 42px;
    height: 22px;
  }
  .reset-button {
    width: 42px;
    height: 22px;
  }
  .bet-display {
    width: 60px;
    font-size: 15px;
  }
  
  
}

/* Адаптация для мобильных устройств */
@media (max-width: 480px) {
  .menu-stavki .stavki-image{
    max-width: 365px;
    height: 130%;
    
    
    transform-origin: top center;
    border: 3px solid blanchedalmond;
  }
  
  .next-race-notice {
    font-size: 16px;
    top: -25px;
  }
  
  .bet-controls-container {
    height: 50px;
    top: 52%;
  }
  
  .stavki-buttons-container {
    top: 20%;
    right: 20%;
    gap: 10px;
  }
  
  .stavki-button {
    width: 44px;
    height: 24px;
    font-size: 12px;
  }
  
  .x2-button {
    width: 42px;
    height: 22px;
    font-size: 12px;
    top: 18px;
    left: 50px;
  }
  
  .otmena-button {
    width: 25px;
    height: 23px;
    left: 15px;
    top: 18px;
  }
  
  .reset-button {
    width: 40px;
    height: 20px;
    left: 105px;
    top: 18px;
  }
  
  .bet-button.minus {
    width: 20px;
    height: 20px;
    right: 60px;
    top: 50px;
  }
  
  .bet-button.plus {
    width: 20px;
    height: 20px;
    left: 60px;
    top: 50px;
  }
  
  .bet-display {
    width: 70px;
    height: 20px;
    font-size: 16px;
    
    top: 50px;
  }
  
  .group-164-button {
    width: 95px;
    height: 62px;
    bottom: 10px;
    right: -0px;
  }
}
/* Адаптация для очень маленьких экранов */
@media (max-width: 360px) {
  .menu-stavki {
    max-width: 250px;
  }
  
  .next-race-notice {
    font-size: 14px;
    top: -20px;
  }
  
  .stavki-buttons-container {
    top: 45px;
    gap: 4px;
  }
  
  .stavki-button {
    width: 36px;
    height: 18px;
    font-size: 12px;
  }
  
  .x2-button {
    width: 36px;
    height: 18px;
  }
  
  .otmena-button {
    width: 20px;
    height: 20px;
    left: 10px;
  }
  
  .reset-button {
    width: 40px;
    height: 20px;
    right: 10px;
  }
  
  .bet-button {
    width: 18px;
    height: 18px;
  }
  
  .bet-display {
    width: 50px;
    height: 18px;
    font-size: 13px;
  }
  
  .group-164-button {
    width: 80px;
    height: 50px;
  }
}
</style>