<template>
  <div class="last-game-menu" :class="positionClass">
    <div class="history-header">
      <div class="history-title">{{ t('last_games') }}</div>
    </div>
    
    <div class="games-container">
      <div 
        v-for="(game, index) in gamesList" 
        :key="game.id"  
        class="game-item"
      >
        <div class="game-id">#{{ index + 1 }}</div>
        <div class="results-container">
          <!-- Отображаем результаты в фиксированном порядке -->
          <div 
            v-for="position in 7" 
            :key="position"
            class="result-item"
          >
            <div class="position">
              {{ getPositionText(game.results, position) }}
            </div>
            <div 
              class="bug-color" 
              :class="{ empty: !getPositionColor(game.results, position) }"
              :style="{ background: getPositionColor(game.results, position) || 'transparent' }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
  isCenterMenuOpen: Boolean,
  games: Array
});

const gamesList = computed(() => props.games || []);
const positionClass = computed(() => 
  props.isCenterMenuOpen ? 'top-left' : 'bottom-left'
);

// Функция для получения текста позиции
const getPositionText = (results, position) => {
  const result = results.find(r => r.position === position);
  return result ? result.position : '-';
};

// Функция для получения цвета таракана
const getPositionColor = (results, position) => {
  const result = results.find(r => r.position === position);
  return result ? result.color : null;
};
</script>
<style scoped>
/* Добавьте этот стиль */
.bug-color.empty {
  background: rgba(255, 255, 255, 0.1) !important;
  
}

.last-game-menu {
  position: absolute;
  width: auto;
  min-width: 300px;
  height: 210px;
  padding: 5px;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.8) 0%, rgba(30, 3, 30, 0.8) 100%);
  border-radius: 8px;
  z-index: 10;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  overflow-x: auto;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
  /* Новое позиционирование */
  left: 50%;
  bottom: 380%; /* Располагаем выше кнопок win */
  transform: translateX(-50%);
}


.bug-color.empty {
    background: rgba(255, 255, 255, 0.1) !important;
    
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
  margin-left: 20px;
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
  
  border-radius: 3px;
  box-sizing: border-box;
}

/* Для пустых результатов */
.bug-color.empty {
  background: rgba(255, 255, 255, 0.1) !important;
  
}

@media (max-width: 390px) {
  .last-game-menu{
 transform:  scale(0.7);
}
}


@media (max-width: 768px) {
  .last-game-menu{
 transform:  scale(0.95);
 left: 3.5%;
}
}
@media  (max-width: 400px) {
 .last-game-menu {
  transform:  scale(0.9);
  left: 4%;
 }
}



</style>