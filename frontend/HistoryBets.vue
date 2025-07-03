<template>
  <div class="history-bets" :class="positionClass">
    <!-- Заголовок -->
    <div class="history-header">
      <div class="history-title">History of bets</div>
    </div>
    
    <!-- Список ставок -->
    <div class="bets-list">
      <div v-for="(bet, index) in bets" :key="index" class="bet-item">
        <div class="bet-color-indicator" :style="{ background: bet.color }"></div>
        <div class="bet-info">
          <div class="bet-description">{{ bet.description }}</div>
          <div class="bet-amount">{{ bet.amount }}</div>
        </div>
        <div class="bet-time">{{ bet.time }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  isCenterMenuOpen: Boolean
});

// Вычисляемое свойство для позиционирования
const positionClass = computed(() => {
  return props.isCenterMenuOpen ? 'top-right' : 'bottom-right';
});

// Заглушка данных (в реальном приложении будет запрос к API)
const bets = [
  { 
    color: 'linear-gradient(180deg, #FDF735 0%, #FD7E00 100%)', 
    description: 'win', 
    amount: '25₽ x 6 = 150₽', 
    time: '15:35' 
  },
  { 
    color: 'linear-gradient(180deg, #FF170F 0%, #FF005E 100%)', 
    description: 'will come', 
    amount: '25₽ x 6 = 150₽', 
    time: '15:30' 
  },
  // ... другие ставки
];
</script>

<style scoped>
.history-bets {
  position: absolute;
  width: 236px;
  height: 150px;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.4) 0%, rgba(30, 3, 30, 0.4) 100%);
  border-radius: 8px;
  z-index: 10;
  padding: 5px;
  box-sizing: border-box;
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
</style>