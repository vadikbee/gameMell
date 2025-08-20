<template>
  <div 
    class="win-lose-notification"
    :class="{ 'expanded': expanded }"
    @click="expand"
  >
    <div class="notification-header">
      <div class="gradient-border"></div>
      <div class="icon-container">
        <div class="win-icon"></div>
      </div>
      <div class="title">{{ t('you_win') }}</div>
      <div class="time">{{ currentTime }}</div>
    </div>
    
    <div class="notification-body">
      <div class="amount-display">
        <span class="label">{{ t('winning_amount') }}</span>
        <span class="amount win">{{ formattedTotalAmount }}</span>
      </div>
      <div v-if="multipleWins && !expanded" class="win-count">
        +{{ bets.length - 1 }} {{ t('other_wins') }}
      </div>
    </div>
    
    <div v-if="expanded" class="details">
    <div class="bets-summary">
      {{ t('bets_details') }}
    </div>
    <div class="bets-list">
      <div v-for="(bet, index) in bets" :key="index" class="bet-item">
        <!-- Индикатор ловушки -->
        <div class="section-indicator">
          <img :src="`/images/trap-${bet.trapId}.png`" alt="Trap" class="trap-icon">
        </div>
        
        <!-- Цвета выигравших тараканов -->
        <!-- WinLoseNotification.vue - в блоке winning-bugs -->
<div class="winning-bugs">
  <div 
    v-for="(bug, bugIndex) in bet.winningBugs" 
    :key="bugIndex"
    class="bug-color-indicator"
    :style="{ background: bug.color }"
  ></div>
</div>
        
        <div class="bet-description">
          {{ formatBetDescription(bet) }}
        </div>
        <div class="bet-amount">
          +{{ new Intl.NumberFormat('ru-RU').format(bet.winAmount) }}₽
        </div>
      </div>
    </div>
      <button class="close-button" @click.stop="close">{{ t('close') }}</button>
    </div>
    
    <div v-if="!expanded" class="expand-hint">
      {{ t('press_for_details') }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
  bets: {
    type: Array,
    required: true,
    default: () => []
  },
  timestamp: {
    type: Number,
    default: Date.now()
  }
});

const emit = defineEmits(['close']);

const expanded = ref(false);
const currentTime = ref('');

// Исправляем вычисление общей суммы выигрыша
const formattedTotalAmount = computed(() => {
  const total = props.bets.reduce((sum, bet) => sum + bet.winAmount, 0);
  return new Intl.NumberFormat('ru-RU').format(total) + '₽';
});

// Проверяем, несколько ли выигрышей
const multipleWins = computed(() => props.bets.length > 1);

// Обновление времени
const updateTime = () => {
  const date = new Date(props.timestamp);
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  currentTime.value = `${hours}:${minutes}`;
};
// Добавляем функцию для форматирования описания ставки
const formatBetDescription = (bet) => {
  // WinLoseNotification.vue - в функции formatBetDescription
if (bet.type === 'section') {
  const bugNames = bet.winningBugs.map(bug => 
    t(`bug_${bug.id}`)
  ).join(', ');
  
  return t('section_bet_won', { 
    trap: bet.trapId,
    count: bet.winningBugs.length,
    bugs: bugNames
  });
}
  return bet.description;
};
// Развернуть/свернуть детали
const expand = () => {
  expanded.value = true;
  clearTimeout(autoCloseTimer);
};

// Закрыть уведомление
const close = () => {
  emit('close');
};

let autoCloseTimer;
onMounted(() => {
  updateTime();
  
  // Автозакрытие через 5 секунд если не развернуто
  if (!expanded.value) {
    autoCloseTimer = setTimeout(() => {
      emit('close');
    }, 5000);
  }
});

onUnmounted(() => {
  clearTimeout(autoCloseTimer);
});
</script>

<style scoped>
.win-lose-notification {
  position: fixed;
  width: 372px;
  min-height: 100px;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: height 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-left: 4px solid #3C42E3;
}

.win-lose-notification.expanded {
  height: 205px;
  overflow-y: auto;
}

.notification-header {
  position: relative;
  display: flex;
  align-items: center;
  padding: 10px 15px;
  height: 39px;
  background: rgba(255, 255, 255, 0.6);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.gradient-border {
  position: absolute;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, #3C42E3 0%, #FF4300 100%);
  opacity: 0.7;
  border-radius: 2px 0 0 2px;
}

.icon-container {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
  margin-left: 15px;
  z-index: 1;
}

.win-icon {
  width: 18px;
  height: 18px;
  background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
  clip-path: polygon(
    50% 0%, 
    61% 35%, 
    98% 35%, 
    68% 57%, 
    79% 91%, 
    50% 70%, 
    21% 91%, 
    32% 57%, 
    2% 35%, 
    39% 35%
  );
}

.title {
  flex: 1;
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  font-size: 15px;
  line-height: 19px;
  text-transform: uppercase;
  background: linear-gradient(180deg, #3C42E3 0%, #FF4300 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.time {
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 300;
  font-size: 15px;
  line-height: 19px;
  color: #000000;
}

.notification-body {
  padding: 12px 15px;
  position: relative;
  height: 61px;
  box-sizing: border-box;
}

.amount-display {
  display: flex;
  align-items: center;
  margin-top: 10px;
}

.label {
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  font-size: 15px;
  line-height: 19px;
  text-transform: uppercase;
  color: #000000;
  margin-right: 10px;
}

.amount {
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  font-size: 18px;
  line-height: 19px;
  text-transform: uppercase;
}

.amount.win {
  color: #000000;
}

.win-count {
  position: absolute;
  right: 15px;
  bottom: 12px;
  font-family: 'Bai Jamjuree', sans-serif;
  font-style: italic;
  font-size: 13px;
  color: #000000;
  opacity: 0.7;
}

.details {
  padding: 0 15px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  height: calc(205px - 100px);
  box-sizing: border-box;
}

.bets-summary {
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  margin: 10px 0;
  color: #333;
  font-size: 15px;
}

.bets-list {
  height: 100px;
  overflow-y: auto;
}

.bet-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  margin-bottom: 2px;
  padding: 0 10px;
}

.bet-color-indicator {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  margin-right: 10px;
}

.bet-description {
  flex: 1;
  font-size: 14px;
  font-family: 'Bai Jamjuree', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.bet-amount {
  font-weight: 500;
  font-size: 15px;
  font-family: 'Bai Jamjuree', sans-serif;
  color: #000000;
  margin-left: 10px;
}

.close-button {
  display: block;
  margin: 10px auto;
  padding: 8px 20px;
  background: rgba(0, 0, 0, 0.1);
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s;
  font-family: 'Bai Jamjuree', sans-serif;
  font-size: 15px;
}

.close-button:hover {
  background: rgba(0, 0, 0, 0.2);
}

.expand-hint {
  padding: 5px 15px 10px;
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  font-size: 13px;
  line-height: 16px;
  color: #000000;
  opacity: 0.7;
  font-style: italic;
}

/* Анимация появления */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}

/* Стили для скроллбара */
.bets-list::-webkit-scrollbar {
  width: 4px;
}

.bets-list::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 2px;
}

.bets-list::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 2px;
}

/* Специальные стили для ставок на секцию */
.section-indicator {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 8px;
}

.trap-icon {
  width: 20px;
  height: 20px;
}

/* Стили для списка ставок */
.bet-item {
  display: flex;
  align-items: center;
  padding: 8px 10px;
  margin-bottom: 2px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  height: 20px;
}

.bet-color-indicator {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  margin-right: 10px;
}

.bet-description {
  flex: 1;
  font-size: 14px;
  font-family: 'Bai Jamjuree', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 5px;
}

.bet-amount {
  font-weight: 500;
  font-size: 15px;
  font-family: 'Bai Jamjuree', sans-serif;
  color: #000000;
  min-width: 100px;
  text-align: right;
}

.winning-bugs {
  display: flex;
  margin-right: 10px;
}

.bug-color-indicator {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  margin-right: 4px;
  border: 1px solid rgba(0, 0, 0, 0.2);
}
</style>