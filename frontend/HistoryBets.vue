<template>
  <div class="history-bets" :class="positionClass">
    <div class="history-header">
      <div class="history-title">{{ title }}</div>
    </div>
    
    <div class="bets-list">
      <div v-for="(bet, index) in formattedBets" :key="index" class="bet-item">
        <div class="bet-colors">
          <div 
            v-for="(color, i) in getBetColors(bet)" 
            :key="i"
            class="color-indicator"
            :style="{ background: color }"
          ></div>
        </div>
        <div class="bet-info">
          <div class="bet-description">{{ getBetType(bet) }}</div>
          <div class="bet-amount">{{ bet.amount }}₽ × {{ getMultiplier(bet) }} = {{ calculateTotal(bet) }}₽</div>
        </div>
        <div class="bet-time">{{ formatTime(bet.time) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const props = defineProps({
  bets: Array,
  isCenterMenuOpen: Boolean,
  insideCenter: Boolean,
  title: String
});

// Позиционирование компонента
const positionClass = computed(() => {
  if (props.insideCenter) return 'inside-center';
  return props.isCenterMenuOpen ? 'top-right' : 'bottom-right';
});

// Форматирование времени (HH:MM)
const formatTime = (timeString) => {
  if (!timeString) return '';
  return timeString.split(':').slice(0, 2).join(':');
};

// Тип ставки на основе данных
const getBetType = (bet) => {
  if (bet.type === 'win') return t('position_bet');
  if (bet.type === 'place') return t('overtaking_bet');
  if (bet.type === 'trap') return t('section_bet');
  return t('bet');
};

// Цвета для индикаторов
const getBetColors = (bet) => {
  const colors = [];
  if (bet.color) {
    const matches = bet.color.match(/#[0-9A-Fa-f]{6}/g);
    if (matches) colors.push(...matches.slice(0, 2));
  }
  return colors.length > 0 ? colors : ['#FFFFFF'];
};

// Множитель ставки
const getMultiplier = (bet) => {
  return bet.type === 'win' ? 1 : bet.selection?.length || 1;
};

// Общая сумма ставки
const calculateTotal = (bet) => {
  return bet.amount * getMultiplier(bet);
};

// Обратный порядок ставок
const formattedBets = computed(() => {
  return [...(props.bets || [])].reverse();
});
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
  overflow: hidden;
}

/* Позиционирование */
.bottom-right {
  left: 36.15%;
  right: 2.31%;
  top: 63.03%;
  bottom: 19.19%;
}

.top-right {
  left: 36.15%;
  right: 2.31%;
  top: 0.15%;
  bottom: 82.07%;
}

.inside-center {
  position: relative;
  width: 100%;
  height: auto;
  margin-top: 20px;
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
  text-align: center;
}

.bets-list {
  height: calc(100% - 30px);
  overflow-y: auto;
  padding-right: 5px;
}

.bet-item {
  display: flex;
  align-items: center;
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 30px;
  padding: 5px 8px;
  margin-bottom: 4px;
  font-family: 'Bahnschrift', sans-serif;
  font-size: 9px;
}

.bet-colors {
  display: flex;
  margin-right: 8px;
}

.color-indicator {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  border: 1px solid #FFFFFF;
  margin-right: 2px;
}

.bet-info {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.bet-description {
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.2;
  margin-bottom: 2px;
}

.bet-amount {
  font-weight: 300;
  color: #FFFFFF;
  font-size: 8px;
}

.bet-time {
  font-weight: 300;
  color: #000000;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 30px;
  padding: 2px 5px;
  font-size: 8px;
  min-width: 30px;
  text-align: center;
}

/* Адаптация под мобильные устройства */
@media (max-width: 768px) {
  .history-bets {
    transform: scale(0.85);
    transform-origin: top right;
  }
  
  .bottom-right {
    top: auto;
    bottom: 70px;
    left: auto;
    right: 10px;
  }
  
  .top-right {
    top: 70px;
    left: auto;
    right: 10px;
  }
}
</style>