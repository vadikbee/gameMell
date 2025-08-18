<template>
  <div 
    class="win-lose-notification"
    :class="{ 'win': type === 'win', 'expanded': expanded }"
    @click="expand"
  >
    <div class="notification-header">
      <div class="gradient-border"></div>
      <div class="icon-container">
        <div v-if="type === 'win'" class="win-icon"></div>
      </div>
      <div class="title">{{ type === 'win' ? t('you_win') : t('you_lose') }}</div>
      <div class="time">{{ currentTime }}</div>
    </div>
    
    <div class="notification-body">
      <div class="amount-display">
        <span class="label">{{ type === 'win' ? t('winning_amount') : t('losing_amount') }}</span>
        <span class="amount win">{{ formattedAmount }}</span>
      </div>
    </div>
    
    <div v-if="expanded" class="details">
      <div class="bets-summary">
        {{ t('bets_details') }}
      </div>
      <div v-for="(bet, index) in bets" :key="index" class="bet-item">
        <div class="bet-color-indicator" :style="{ background: bet.color }"></div>
        <div class="bet-description">
          {{ bet.description }}
        </div>
        <div class="bet-amount">
          {{ bet.amount }}₽
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
  type: String, // 'win' or 'lose'
  amount: Number,
  bets: Array,
  timestamp: Number
});

const emit = defineEmits(['close']);

const expanded = ref(false);
const currentTime = ref('');

// Форматирование суммы
const formattedAmount = computed(() => {
  return new Intl.NumberFormat('ru-RU').format(props.amount) + '₽';
});

// Обновление времени каждую минуту
const updateTime = () => {
  const date = new Date(props.timestamp);
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  currentTime.value = `${hours}:${minutes}`;
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
  
  // Автозакрытие через 3 секунды если не развернуто
  if (!expanded.value) {
    autoCloseTimer = setTimeout(() => {
      emit('close');
    }, 3000);
  }
});

onUnmounted(() => {
  clearTimeout(autoCloseTimer);
});
</script>

<style scoped>
.win-lose-notification {
  position: absolute;
  width: 372px;
  height: 100px;
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
}

.win-lose-notification.win {
  border-left: 4px solid #3C42E3;
}

.win-lose-notification.expanded {
  height: auto;
  max-height: 80vh;
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
  left: 15px;
  width: 4px;
  height: 20px;
  background: linear-gradient(180deg, #3C42E3 0%, #FF4300 100%);
  opacity: 0.7;
  border-radius: 2px;
}

.icon-container {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
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
}

.amount-display {
  display: flex;
  align-items: center;
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

.details {
  padding: 15px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.bets-summary {
  font-family: 'Bai Jamjuree', sans-serif;
  font-weight: 500;
  margin-bottom: 10px;
  color: #333;
}

.bet-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
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
}

.bet-amount {
  font-weight: 500;
  font-size: 14px;
  font-family: 'Bai Jamjuree', sans-serif;
}

.close-button {
  display: block;
  margin: 15px auto 5px;
  padding: 8px 20px;
  background: rgba(0, 0, 0, 0.1);
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s;
  font-family: 'Bai Jamjuree', sans-serif;
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
</style>