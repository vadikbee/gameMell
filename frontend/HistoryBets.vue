<template>
  <template>
  <div class="history-bets" :class="positionClass">
    <div class="history-header">
      <div class="history-title">{{ title }}</div>
    </div>
    
    <div class="bets-list">
      <div v-for="(bet, index) in formattedBets" :key="index" class="bet-item">
        <div 
          class="bet-color-indicator" 
          :style="{ background: getFirstColor(bet) }"
        ></div>
        <div class="bet-info">
          <div class="bet-description">{{ getBetType(bet) }}</div>
          <div 
            class="bet-amount" 
            :class="{ 'win-amount': bet.result === 'win', 'lose-amount': bet.result === 'lose' }"
          >
            {{ formatAmount(bet) }}
          </div>
        </div>
        <div class="bet-time">
          {{ formatTime(bet.timestamp) }}
        </div>
      </div>
    </div>
  </div>
</template>
</template>

<script setup>
import { computed,defineProps  } from 'vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const props = defineProps({
  bets: Array,
  isCenterMenuOpen: Boolean,
  insideCenter: Boolean, // Добавляем новый пропс
  title: String
});
// Определение типа ставки
const getBetType = (bet) => {
  switch (bet.type) {
    case 'position': return t('position_bet');
    case 'overtaking': return t('overtaking_bet');
    case 'section': 
      // +++ ПОДДЕРЖКА СТАВОК НА СЕКЦИЮ +++
      return `${t('section_bet')} ${bet.trapId}`;
    default: return t('bet');
  }
};
const positionClass = computed(() => {
  if (props.insideCenter) return 'inside-center'; // Для истории внутри меню
  return props.isCenterMenuOpen ? 'top-right' : 'bottom-right'; // Для истории снаружи
});
// Заглушка данных (в реальном приложении будет запрос к API)
// Форматирование времени
const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  return `${hours}:${minutes}`;
};

// Получение первого цвета ставки
const getFirstColor = (bet) => {
  if (bet.bugColors && bet.bugColors.length > 0) {
    return bet.bugColors[0];
  }
  return '#FFFFFF'; // Цвет по умолчанию
};



// Форматирование суммы
const formatAmount = (bet) => {
  if (bet.result === 'win') {
    return `+${bet.winAmount}₽`;
  }
  return `-${bet.amount}₽`;
};

// Обратный порядок ставок (последние сверху)
const formattedBets = computed(() => {
  return [...props.bets].reverse();
});
</script>

<style scoped>
.win-amount {
  color: #28a745; /* Зеленый для выигрыша */
}

.lose-amount {
  color: #dc3545; /* Красный для проигрыша */
}

.bet-color-indicator {
  width: 15px;
  height: 15px;
  border-radius: 2px;
  margin-right: 8px;
  border: 1px solid #FFFFFF;
}
/* .history-bets обе менюшки  */
.history-bets {
  position: absolute;
  width: 236px;
  height: 150px;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.4) 0%, rgba(30, 3, 30, 0.4) 100%);
  border-radius: 8px;
  z-index: 10;
  padding: 5px;
  box-sizing: border-box;
  top: 57.3%;
  margin-left: -6%;
  
}

/* Позиционирование */
.bottom-right {
  right: 2.31%;
  bottom: 77.96%;
}

.top-right {
  right: 2.31%;
  top: 0.15%;
}

.history-header {
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 10px;
  padding: 4px 18px;
  margin-bottom: 5px;
}

.history-title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 10px;
  color: #FFFFFF;
  text-transform: uppercase;
}

.bets-list {
  height: calc(100% - 30px);
  overflow-y: auto;
  
}

.bet-item {
  display: flex;
  align-items: center;
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 30px;
  padding: 3px 8px;
  margin-bottom: 4px;
  font-family: 'Bahnschrift', sans-serif;
  font-size: 10px;
}

.bet-color-indicator {
  width: 15px;
  height: 15px;
  border-radius: 2px;
  margin-right: 8px;
  border: 1px solid #FFFFFF;
}

.bet-info {
  flex-grow: 1;
  display: flex;
  justify-content: space-between;
}

.bet-description {
  font-weight: 700;
  color: #FFFFFF;
}

.bet-amount {
  font-weight: 300;
  color: #FFFFFF;
}

.bet-time {
  font-weight: 300;
  color: #000000;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 30px;
  padding: 2px 5px;
  margin-left: 5px;
}
@media (max-width: 390px) {
  .history-bets{
    transform: translate(-7%) scale(0.75) translateY(-23%);
    
    
  }
}

@media (max-width: 768px) {
  .history-bets{
    transform: translate(10%) scale(0.9) translateY(50%);
    
    
  }
}

@media (max-width: 480px) {
  .history-bets{
    transform: translate(5%) scale(0.9) translateY(9%);
  }
}
</style>