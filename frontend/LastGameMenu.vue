<!-- LastGameMenu.vue -->
<template>
  <div class="last-game-menu" :class="positionClass">
    <div class="history-header">
      <div class="history-title">Last games</div>
    </div>
    
    <div class="games-container">
      <div 
        v-for="(game, index) in gamesList" 
        :key="index" 
        class="game-item"
      >
        <div class="game-id">#{{ game.id }}</div>
        <div class="results-container">
    <div 
      v-for="(result, resultIndex) in game.results" 
      :key="resultIndex" 
      class="result-item"
    >
      <div class="position">
        {{ result.position !== null ? result.position : '-' }}
      </div>
      <div 
        class="bug-color" 
        :class="{ empty: result.position === null }"
        :style="{ background: result.color }"
      ></div>
    </div>
  </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  isCenterMenuOpen: Boolean,
  games: Array
});

const gamesList = computed(() => props.games || []);

const positionClass = computed(() => {
  return props.isCenterMenuOpen ? 'top-left' : 'bottom-left';
});
</script>


<style scoped>
.last-game-menu {
  position: absolute;
  width: auto; /* Ширина по содержимому */
  min-width: 300px;
  height: 210px;
  padding: 5px;
  top: -350%;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.8) 0%, rgba(30, 3, 30, 0.8) 100%);
  border-radius: 8px;
  z-index: 10;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  overflow-x: auto; /* Горизонтальная прокрутка при необходимости */
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
}

/* Позиционирование */
.bottom-left {
  left: 2%;
  bottom: 22%;
}
.bug-color.empty {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px dashed rgba(255, 255, 255, 0.3);
}
.top-left {
  left: 2%;
  top: 0.15%;
}

.history-header {
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 10px;
  padding: 4px 18px;
  margin-bottom: 10px;
}

.history-title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: #FFFFFF;
  text-transform: uppercase;
  text-align: center;
}

/* Контейнер для игр */
.games-container {
  display: flex;
  gap: 15px; /* Расстояние между играми */
  padding: 0 10px;
}

/* Стиль для одной игры */
.game-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 60px;
}

/* Идентификатор игры */
.game-id {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 10px;
  color: #FFFFFF;
  margin-bottom: 5px;
  background: rgba(0, 0, 0, 0.3);
  padding: 2px 5px;
  border-radius: 3px;
}

/* Контейнер результатов */
.results-container {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

/* Элемент результата */
.result-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 5px;
}

.position {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: #FFFFFF;
  min-width: 20px;
  text-align: center;
}

.bug-color {
  width: 18px;
  height: 18px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 3px;
  box-sizing: border-box;
}

/* Для пустых результатов */
.bug-color.empty {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px dashed rgba(255, 255, 255, 0.3);
}
</style>