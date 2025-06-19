<template>
  <div class="game-container">
    <!-- Шапка с балансом -->
    <header class="game-header">
      <div class="balance">
        <span>Баланс: {{ balance }} {{ currency }}</span>
      </div>
      <button class="account-btn">Личный кабинет</button>
    </header>

    <!-- Прогресс бар гонки -->
    <div class="progress-container">
      <div class="progress-bar" :style="{ width: progress + '%' }"></div>
    </div>

    <!-- Игровое поле -->
    <RaceTrack 
      :maze="currentMaze" 
      :cockroaches="cockroaches" 
      v-if="isRaceStarted"
    />

    <!-- Кнопки управления -->
    <div class="controls">
      <button @click="openModal('lastGames')">Last Game</button>
      <button @click="openModal('makeBet')">Make a Bet</button>
      <button @click="openModal('historyBets')">History Bets</button>
    </div>

    <!-- Модальные окна -->
    <LastGamesModal v-if="activeModal === 'lastGames'" @close="closeModal" />
    <MakeBetModal v-if="activeModal === 'makeBet'" @close="closeModal" />
    <HistoryBetsModal v-if="activeModal === 'historyBets'" @close="closeModal" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useWebSocket } from '@vueuse/core';

// Основные данные игры
const balance = ref(1000);
const currency = ref('USD');
const progress = ref(0);
const isRaceStarted = ref(false);
const activeModal = ref(null);
const currentMaze = ref('default');
const cockroaches = ref([]);

// Инициализация игры
onMounted(() => {
  startNewRace();
});

// Подключение к WebSockets
const { data } = useWebSocket('ws://your-backend-url/socket', {
  autoReconnect: true,
});

// Обработка сообщений от сервера
watch(data, (newData) => {
  if (newData.type === 'RACE_UPDATE') {
    updateRaceProgress(newData.progress);
  }
});

function startNewRace() {
  // Запрос к API для получения данных о новой гонке
  fetch('/api/race/start')
    .then(res => res.json())
    .then(data => {
      currentMaze.value = data.maze;
      cockroaches.value = data.cockroaches;
      isRaceStarted.value = true;
    });
}

function updateRaceProgress(value) {
  progress.value = value;
}

function openModal(modalName) {
  activeModal.value = modalName;
}

function closeModal() {
  activeModal.value = null;
}
</script>

<style scoped>
.game-container {
  background: #0f0c29; /* Космический градиент */
  background: linear-gradient(to bottom, #0f0c29, #302b63, #24243e);
  min-height: 100vh;
  color: white;
  padding: 20px;
}

.game-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 20px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 10px;
  margin-bottom: 20px;
}

.progress-container {
  height: 10px;
  background: #333;
  border-radius: 5px;
  margin-bottom: 20px;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #00c9ff, #92fe9d);
  border-radius: 5px;
  transition: width 0.3s ease;
}

.controls {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-around;
  padding: 15px;
  background: rgba(0, 0, 0, 0.7);
}

.controls button {
  padding: 10px 20px;
  border: none;
  border-radius: 20px;
  background: #5e35b1;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.controls button:hover {
  background: #7e57c2;
  transform: translateY(-3px);
}
</style>