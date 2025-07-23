<template>
  <div class="outer-container">
  <div class="podium-container">
    <div class="podium-title">{{ t('last_games') }}</div>
    
    <!-- Добавляем контейнер для выравнивания с номерами игр -->
    <div class="cups-container">
      <!-- Пустая колонка для выравнивания с номерами игр -->
      <div class="cup-column aligner"></div>
      
      <div class="cup-column">
        <div class="cup">
          <div class="cup-img cup-1"></div>
        </div>
        
      </div>
      <div class="cup-column">
        <div class="cup">
          <div class="cup-img cup-2"></div>
        </div>
        
      </div>
      <div class="cup-column">
        <div class="cup">
          <div class="cup-img cup-3"></div>
        </div>
        
      </div>
    </div>
    
    <div class="games-container">
      <div 
        v-for="(game, gameIndex) in podiums" 
        :key="gameIndex" 
        class="game-row"
      >
        <div class="game-id">#{{ gameIndex + 1 }}</div>
        <div class="results-container">
          <div 
            v-for="(result, index) in game" 
            :key="index" 
            class="result-column"
          >
            <div 
              v-if="result" 
              class="bug-color" 
              :style="{ background: result.color }"
            ></div>
            <div v-else class="bug-color empty"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import i18n from './i18n.js'
const { t } = i18n.global
const currentLanguage = ref(i18n.global.locale.value.toUpperCase());
const props = defineProps({
  games: Array
});

const podiums = computed(() => {
  if (!props.games || props.games.length === 0) {
    return Array(5).fill([null, null, null]);
  }
  
  return props.games.slice(0, 5).map(game => {
    if (!game.results) return [null, null, null];
    
    const places = [null, null, null];
    game.results.forEach(result => {
      if (result.position >= 1 && result.position <= 3) {
        places[result.position - 1] = result;
      }
    });
    return places;
  });
});
</script>

<style scoped>
.outer-container {
  
  display: flex;
  justify-content: center;
  width: 100%;
  padding: 0 10px; /* Добавляет отступы по бокам */
  box-sizing: border-box;
}
.podium-container {
  position: relative;
  width: 100%;
  max-width: 400px;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 0px;
  box-sizing: border-box;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.4) 0%, rgba(30, 3, 30, 0.4) 100%);
  border-radius: 8px;
}


.podium-title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 10px;
  color: #FFFFFF;
  text-transform: uppercase;
  text-align: center;
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 3px;
  padding: 3px 0;
  margin-bottom: 8px;
}

.cups-container {
  display: flex;
  justify-content: space-around;
  margin-bottom: 6px;
}

.cup-column {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 33%;
}

/* Новая колонка-выравниватель */
.cup-column.aligner {
  visibility: hidden; /* Скрываем, но сохраняем место */
  pointer-events: none; /* Игнорируем клики */
}

.cup {
  position: relative;
  width: 24px;
  height: 14px;
  margin-bottom: 5%;
}

.cup-img {
  width: 120%;
  height: 120%;
  background-image: url('/images/icons/kubki.png');
  background-size: 300% 100%;
  background-repeat: no-repeat;
  margin-left: -8px;
}

.cup-img.cup-1 { background-position: 0 0; }
.cup-img.cup-2 { background-position: 50% 0; }
.cup-img.cup-3 { background-position: 100% 0; }




.games-container {
  display: flex;
  flex-direction: column;
  gap: 2px;
  height: 100%;
  overflow-y: auto;
}

.game-row {
  display: flex;
  align-items: center;
  gap: 0px;
  padding: 0px 0;
}

.game-id {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 9px;
  color: #FFFFFF;
  min-width: 18px;
  text-align: center;
  background: rgba(0, 0, 0, 0.3);
  padding: 1px 0px;
  border-radius: 3px;
}

.results-container {
  display: flex;
  justify-content: space-around;
  flex-grow: 1;
  gap: 6px;
}

.result-column {
  width: 33%;
  display: flex;
  justify-content: center;
}

.bug-color {
  width: 16px;
  height: 16px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 2px;
  opacity: 0.8;
}

.bug-color.empty {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px dashed rgba(255, 255, 255, 0.3);
}

@media (max-width: 480px) {
  
  
  .podium-container{
    
    border: 1px solid rgb(243, 93, 0) !important;
  }
  .outer-container {
  
  display:  flex;
  justify-content: center;
  width: 100%;
  height: 100%;
  margin-top: 10%;
  margin-left: 35%;
  padding: 0 0px; /* Добавляет отступы по бокам */
  box-sizing: border-box;
  border: 1px solid rgb(243, 93, 0) !important;
}

}
</style>