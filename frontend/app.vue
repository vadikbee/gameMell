<template>
  <div class="main-color">
    <!-- История ставок в основном интерфейсе -->
    
    <!-- Контейнер для масштабирования фона -->
    <div 
      class="main-bg-container" 
      :style="{ transform: `scale(${scaleFactor})` }"
    ></div>
 
    <!-- Основной игровой контейнер -->
    <div class="main-bg">
      <HistoryBets 
      v-if="historyBetsVisible && !historyBetsInsideCenter" 
      :isCenterMenuOpen="centerMenuVisible" 
      :title="t('history_bets')"
      class="outside-center"
    />
      <!-- Центральное фиксированное меню -->
      <div 
        v-if="centerWinMenuVisible" 
        class="win-menu-center"
      >
        <StavkiMenu
          :currentBet="currentBet"
          :stavkiButtons="stavkiButtons"
          @otmena-click="handleOtmenaButtonClick"
          @reset-click="handleResetClick"
          @group164-click="handleGroup164Click"
          @decrement-start="startDecrement"
          @increment-start="startIncrement"
          @stop-action="stopAction"
          @add-bet="addToBet"
          @x2-click="handleX2ButtonClick"
        />
        <div class="menu-container">
          <img 
            :src="`/images/menus/Frame 575-${activeWinMenuId}.png`" 
            alt="Menu" 
            class="menuWin-image-center"
          />
          
          <!-- Контейнер для интерактивных тараканов -->
          <div class="bug-buttons-container">
            <div 
              v-for="(bug, index) in menuBugs"
              :key="index"
              class="bug-button"
              :class="{
                'bug-button-hovered': hoveredBugIndex === index,
                'bug-button-clicked': clickedBugIndex === index
              }"
              @mouseenter="hoverBug(index)"
              @mouseleave="unhoverBug"
              @mousedown="clickBug(index)"
              @touchstart="clickBug(index)"
              :style="getBugStyle(index)"
            >
              <img 
                :src="`/images/tarakani/t-${bug.id}.png`" 
                :alt="`Таракан ${bug.id}`"
                class="bug-image"
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Фон лабиринта -->
      <div class="labirint-bg"></div>
      
      <!-- Контейнеры для кнопок победы -->
      <div 
        v-for="(btn, index) in winButtons" 
        :key="'btn-'+btn.id"
        class="button-win-container"
        :class="{
          'disabled': !isBettingPhase, 
          [`button-win-${index + 1}`]: true
        }"
      >
        <!-- Основная кнопка победы -->
        <div 
          class="button-win"
          @click="handleButtonClick(btn)"
          :style="{ 
            backgroundImage: btn.selected 
              ? `url('/images/buttons/knopka-win.png')` 
              : `url('/images/buttons/Property 1=Default.png')`,
            cursor: isRaceStarted ? 'default' : 'pointer'
          }"
        ></div>
       
        <!-- Индикатор победы -->
        <div 
          v-if="btn.occupied" 
          class="win-indicator"
          :style="{ backgroundImage: `url('/images/buttons/Property 1=Variant2.png')` }"
        ></div>
      </div>
      
      <!-- Тараканы -->
  
    <div 
      v-for="(bug, index) in bugs" 
      :key="'bug-'+bug.id"
    >
      <!-- Взрыв показываем только для не финишировавших -->
      <div 
        v-if="bug.explodeFrame !== undefined && !bug.finished"
        class="explosion"
        :style="{
          backgroundImage: `url('${explosionImages[bug.explodeFrame]}')`,
          left: `${bug.position[0]}px`,
          top: `${bug.position[1]}px`
        }"
      ></div>
  
  <!-- Таракан показываем если не взорван ИЛИ если он финишировал -->
   <div 
    v-else-if="!bug.exploded || bug.finished"
    class="tarakan"
    :class="{ dizzy: bug.dizzy }"
    :style="{
      backgroundImage: `url('/images/tarakani/Property 1=Default (${bug.id + 1}).png')`,
      left: `${bug.position[0]}px`,
      top: `${bug.position[1]}px`,
      transform: `translate(-50%, -50%) rotate(${bug.angle}rad)`
    }"
    @click="makeBugDizzy(index)" 
      ></div>
    </div>
      
      <!-- Нижняя панель управления -->
      <div class="panel-layer">
        <!-- Кнопки управления -->
      <div 
          class="button-1" 
          id="Button-1" 
          @click="toggleLastGameMenu"
        >
          <span class="button-text">LAST<br>GAME</span>
        </div>
        <!-- Меню последней игры -->
        <LastGameMenu 
          v-if="lastGameMenuVisible"
          :isCenterMenuOpen="centerMenuVisible"
          :games="lastGames"
        />
        <div 
          class="button-2" 
          id="Button-2" 
          @click="toggleCenterMenu"
        >
          <span class="button-text">MAKE A BET</span>
        </div>
        <div 
          class="button-3" 
          id="Button-3" 
          @click="toggleHistoryBets"
        >
          <span class="button-text">HISTORY<br>OF BETS</span>
        </div>
       
        <!-- Центральное меню -->
        <div 
          v-if="centerMenuVisible" 
          class="center-menu"
        >
          <img 
            src="/images/menus/center-buttom.png" 
            alt="Center Menu" 
            class="menu-image"
          >
           <div class="menu-tabs">
    <!-- Изменено: поменяны местами вкладки -->
        <div 
          class="tab-button overtaking-tab"
          :class="{ active: activeTab === 'overtaking' }"
          @click="setActiveTab('overtaking')"
        >
          OVERTAKING
        </div>
        <div 
          class="tab-button result-tab"
          :class="{ active: activeTab === 'result' }"
          @click="setActiveTab('result')"
        >
          RESULT
        </div>
      </div>
         <!-- Контейнер для кнопок в зависимости от активной вкладки -->
    <!-- app.vue -->
      <div class="menu-buttons-container">
          <div 
            v-for="btn in menuButtons" 
            :key="btn.id"
            class="menu-button"
            :class="{
              selected: btn.selected,
              'text-button': activeTab === 'result',
              'visible': isButtonVisible(btn)
            }"
            @click="toggleMenuButton(btn)"
          ></div>
        </div>

         <PodiumResults 
          v-if="lastGames.length > 0" 
          :games="lastGames" 
          class="group-image"
        />
          <HistoryBets 
            v-if="historyBetsVisible && historyBetsInsideCenter" 
            :isCenterMenuOpen="centerMenuVisible" 
            :insideCenter="true"
            :title="t('history_bets')"
            class="inside-center"
          />
          
          <!-- Меню ставок -->
          <div class="menu-stavki"> 
            <img 
              src="/images/menus/stavki.png" 
              alt="Stavki" 
              class="menu-image stavki-image"
            >
            <div class="bet-controls-container">
              <img 
                src="/images/buttons/otmena.png" 
                alt="Otmena"
                class="otmena-button"
                @click="handleOtmenaButtonClick"
              >
              <img 
                src="/images/buttons/reset.png" 
                alt="Reset" 
                class="reset-button"
                @click="handleResetClick"
              >
              <img 
                src="/images/menus/Group 164.png" 
                alt="Group 164" 
                class="group-164-button"
                @click="handleGroup164Click"
              >
            </div>
            <!-- Новый блок счетчика ставок.... -->
            <div class="bet-counter-container">
              <div 
                class="bet-button minus" 
                @mousedown="startDecrement" 
                @mouseup="stopAction"
                @mouseleave="stopAction"
                @touchstart="startDecrement"
                @touchend="stopAction"
                @touchcancel="stopAction"
              ></div>
              <div class="bet-display">{{ currentBet }}</div>
              <div 
                class="bet-button plus" 
                @mousedown="startIncrement" 
                @mouseup="stopAction"
                @mouseleave="stopAction"
                @touchstart="startIncrement"
                @touchend="stopAction"
                @touchcancel="stopAction"
              ></div>
            </div>
            <!-- ... остальной код ... -->
            <!-- Кнопки ставок -->
            <div class="stavki-buttons-container">
              <img 
                v-for="button in stavkiButtons"
                :key="button.id"
                :src="button.src"
                :alt="button.alt"
                class="stavki-button"
                @click="addToBet(button.amount)"
              >
              <img 
                src="/images/buttons/x2.png" 
                alt="x2"
                class="x2-button"
                @click="handleX2ButtonClick"
              >
            </div>
          </div> 
          
          <!-- Кнопки меню -->
          <div class="menu-buttons-container">
    <!-- Заменить текущее условие видимости -->
<div 
  v-for="btn in menuButtons" 
  :key="btn.id"
  class="menu-button"
  :class="{
    selected: btn.selected,
    'text-button': activeTab === 'result',
    'button-visible': activeTab === 'result' || 
                     (activeTab === 'overtaking' && !diagonalButtons.includes(btn.id))
  }"
  @click="toggleMenuButton(btn)"
></div>
  </div>
        </div>
        
        <!-- Верхняя панель баланса -->
        <div class="panel-up">
          <div class="language-switcher" @click="switchLanguage">
         {{ currentLanguage }}
        </div>
          <div class="balance-container">
            <div class="balance-text">{{ t('balance') }}</div>
            <div class="button-balans">228</div>
          </div>
          <div class="icon-button">
            <div class="icon-1"></div>
          </div>
           <!-- Добавлен прогресс-бар -->
          <!-- Прогресс-бар -->
      <div class="progress-container">
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: progress + '%' }"
          ></div>
        </div>
         <div v-if="breakInProgress" class="make-bets-label">
     {{ makeBetsText }}
   </div>
      </div>
    </div>
      </div> 
    </div>
  </div>
  <audio ref="dizzySoundElement" src="/sounds/star.mp3"></audio>
</template>


<script setup>

import { ref, computed, onMounted, onUnmounted } from 'vue';
import HistoryBets from './HistoryBets.vue';
import io from 'socket.io-client';
import StavkiMenu from './StavkiMenu.vue';
import LastGameMenu from './LastGameMenu.vue'; // Добавьте эту строку
import PodiumResults from './PodiumResults.vue';
// Исправляем работу со звуком
const dizzySoundElement = ref(null);
const soundVolume = ref(0.5);
// Добавьте новый импорт i18n
import i18n from './i18n.js'
const { t } = i18n.global
const currentLanguage = ref(i18n.global.locale.value.toUpperCase());
// Получаем локаль из URL
const urlParams = new URLSearchParams(window.location.search)
const langParam = urlParams.get('lang')
if (langParam && i18n.global.availableLocales.includes(langParam)) {
  i18n.global.locale.value = langParam
}

const switchLanguage = () => {
  const newLang = i18n.global.locale.value === 'en' ? 'ru' : 'en';
  i18n.global.locale.value = newLang;
  currentLanguage.value = newLang.toUpperCase();
  
  // Обновляем URL с новым параметром языка
  const url = new URL(window.location);
  url.searchParams.set('lang', newLang);
  window.history.replaceState(null, '', url);
};
// Добавляем переменные для звука

const dizzySound = ref(null);
// Добавьте состояние для истории ставок
const historyBetsVisible = ref(false);
// Добавляем phaseStartTime в разделе состояний
const phaseStartTime = ref(Date.now());
// Добавляем состояние для активной вкладки
const activeTab = ref('result');
// ===0===========================
// СОСТОЯНИЯ ПРИЛОЖЕНИЯ
// ==============================
const isLoading = ref(false);
const scaleFactor = ref(1);
const isRaceStarted = ref(false);
const areWinButtonsDisabled = ref(false);
const centerMenuVisible = ref(false);
const isBettingPhase = ref(true);
// ===1. Обновим состояния для управления паузой===
const isPaused = ref(false);
const pauseStartTime = ref(0);
const pausedDuration = ref(0);
// Добавьте эти переменные
// Добавляем состояние для прогресса
const progress = ref(0);

// Добавляем новые переменные для отслеживания времени
const raceStartTime = ref(0);
const lastUpdateTime = ref(0);
const accumulatedTime = ref(0);
const isTabActive = ref(true);
// Добавляем состояние для отслеживания взрыва
const explosionActive = ref(false);
// Добавить новые переменные состояния
const raceInterval = ref(12000); // 12 секунд гонка начало старт
const breakInterval = ref(7000);  // 7 секунд перерыв
const raceInProgress = ref(false);
const breakInProgress = ref(true);
const animationFrameId = ref(null);
const countdown = ref(7); // отсчет до начала гонки
const raceCountdown = ref(12); // отсчет до конца гонки
let cycleTimer = null;
let raceTimer = null;
// Новые состояния для взаимодействия с тараканами в меню
const hoveredBugIndex = ref(null);
const clickedBugIndex = ref(null);

// Новое состояние для центрального меню
const centerWinMenuVisible = ref(false);
const activeWinMenuId = ref(null);
const historyBetsInsideCenter = ref(false);

const betHistory = ref([]); // ИСТОРИЯ СТАВОК
const currentBet = ref(0);
const minBet = 0;
const maxBet = 10000;
const betStep = 1;
const tabInactiveTime = ref(0);
// Добавлено: переменные для управления зажатием кнопок
const actionInterval = ref(null);
const actionTimeout = ref(null);
const speedUpInterval = ref(null); // Добавлено для интервала ускорения
const actionSpeed = ref(300); // начальная скорость в ms
// Новое состояние для отслеживания реального начала гонки
const raceActualStartTime = ref(null)
// Прогресс и состояние тараканов
const bugProgress = ref([]);
const bugSpeeds = ref([]);
const lastSpeedChange = ref([]);
const speedChangeIntervals = ref([]);
const finishZones = ref([]);
const bugs = ref([]);
const animationFrame = ref(null);
const makeBetsText = computed(() => t('make_bets'));

// Добавляем состояние для меню последней игры
const lastGameMenuVisible = ref(false);
const lastGames = ref([
  { id: 1, results: [{ position: 1, color: '#FF0000' }, { position: null, color: '#00FF00' }] },
  { id: 2, results: [{ position: 3, color: '#0000FF' }, { position: 2, color: '#FFFF00' }] }
]);

// Обновляем функцию startRaceCycle
const startRaceCycle = () => {
  isPaused.value = false; // Сброс паузы при начале цикла
  breakInProgress.value = true;
  raceInProgress.value = false;
  phaseStartTime.value = Date.now();
};
// Функция для переключения вкладок
const setActiveTab = (tab) => {
  activeTab.value = tab;
  // Здесь можно добавить логику для отображения соответствующего контента
  console.log(`Active tab changed to: ${tab}`);
};
// Функция для взрыва всех тараканов
const explodeAllBugs = () => {
  if (!isTabActive.value) return; // Не взрываем если вкладка неактивна
  if (bugs.value.length === 0) return;
  
  // Помечаем всех тараканов для взрыва
  bugs.value.forEach(bug => {
    if (!bug.finished) {
      bug.explodeFrame = 0;
      bug.exploded = false;
    }
  });
  

// При монтировании проверяем параметр lang в URL
onMounted(() => {
  
  dizzySound.value = new Audio('/sounds/star.mp3');
  dizzySound.value.volume = soundVolume.value;
  
  // Предзагрузка звука
  dizzySound.value.load();
  const urlParams = new URLSearchParams(window.location.search);
  const langParam = urlParams.get('lang');
  if (langParam && (langParam === 'en' || langParam === 'ru')) {
    i18n.global.locale.value = langParam;
    currentLanguage.value = langParam.toUpperCase();
  }
});
// Функция для регулировки громкости
const setVolume = (volume) => {
  soundVolume.value = volume;
  if (dizzySound.value) {
    dizzySound.value.volume = volume;
  }
};
// Запускаем анимацию взрыва
  startExplosionAnimation();
};
// Вычисляемое свойство для LastGameMenu
const processedGames = computed(() => {
  return (props.games || []).map(game => ({
    ...game,
    sortedResults: sortedResults(game.results)
  }));
});
const isButtonVisible = (btn) => {
  return (activeTab.value === 'result') || 
         (activeTab.value === 'overtaking' && 
          !diagonalButtons.value.includes(btn.id));
};
// Вычисляемое свойство для PodiumResults
const podiums = computed(() => {
  return (lastGames.value || []).slice(0, 5).map(game => {
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
// Добавляем обработчик головокружения
const makeBugDizzy = (index) => {
  // Проверяем что bugs.value существует и index в пределах массива
  if (!bugs.value || index >= bugs.value.length) return;
  
  const bug = bugs.value[index];
  
  // Проверяем наличие необходимых свойств
  if (!bug || bug.finished || bug.exploded || 
     (bug.explodeFrame !== undefined && !bug.finished)) {
    return;
  }
  
  // Устанавливаем состояние головокружения
  bug.dizzy = true;
  bug.dizzyUntil = performance.now() + 1000; // 1 секунда
  if (dizzySound.value) {
    dizzySound.value.currentTime = 0; // Перематываем в начало
    dizzySound.value.play().catch(e => console.error("Ошибка воспроизведения:", e));
  }

  // Для отладки
  console.log(`Tarakan ${index} is dizzy!`);
if (dizzySoundElement.value) {
    try {
      // Сбрасываем позицию воспроизведения
      dizzySoundElement.value.currentTime = 0;
      
      // Устанавливаем громкость
      dizzySoundElement.value.volume = soundVolume.value;
      
      // Пытаемся воспроизвести
      const playPromise = dizzySoundElement.value.play();
      
      // Обрабатываем возможные ошибки воспроизведения
      if (playPromise !== undefined) {
        playPromise.catch(e => {
          console.error("Ошибка воспроизведения:", e);
          // Показываем пользователю сообщение при необходимости
        });
      }
    } catch (e) {
      console.error("Ошибка воспроизведения:", e);
    }
  }
};
// Функция регулировки громкости
const setVolume = (volume) => {
  soundVolume.value = volume;
  if (dizzySoundElement.value) {
    dizzySoundElement.value.volume = volume;
  }
};
// ==============================
// МЕТОДЫ ДЛЯ УПРАВЛЕНИЯ СТАВКАМИ
// ==============================
// Конфигурация расстояния
const gap = 0.5; // % расстояния между кнопками

const menuBugs = ref([
  { id: 1, left: 5, top: 25, width: 12, height: 50 },
  { id: 2, left: 18 + gap, top: 25, width: 12, height: 50 },
  { id: 3, left: 31 + gap * 2, top: 25, width: 12, height: 50 },
  { id: 4, left: 44 + gap * 3, top: 25, width: 12, height: 50 },
  { id: 5, left: 57 + gap * 4, top: 25, width: 12, height: 50 },
  { id: 6, left: 70 + gap * 5, top: 25, width: 12, height: 50 },
  { id: 7, left: 83 + gap * 6, top: 25, width: 12, height: 50 }
]);
// В секции <script setup>
const bugColors = ref([
  '#FFFF00', // Желтый (1)
  '#FFA500', // Оранжевый (2)
  '#FF0000', // Красный (3)
  '#0000FF', // Синий (4)
  '#8B0000', // Темно-красный (5)
  '#800080', // Фиолетовый (6)
  '#00FF00'  // Зеленый (7)
]);
// ===2. Упрощенная функция обработки видимости===
// 2. Упрощенный обработчик видимости вкладки
const handleVisibilityChange = () => {
  if (document.visibilityState === 'hidden') {
    pauseStartTime.value = Date.now();
    isPaused.value = true;
  } else {
    pausedDuration.value = Date.now() - pauseStartTime.value;
    isPaused.value = false;
    
    // Корректируем временные метки
    phaseStartTime.value += pausedDuration.value;
    if (raceActualStartTime.value) {
      raceActualStartTime.value += pausedDuration.value;
    }
  }
};
// В методе saveGameResults
const saveGameResults = async () => {
    // Фильтруем только финишировавших тараканов и сортируем по времени финиша
    const finishedBugs = bugs.value
        .filter(bug => bug.finishTime !== null) // Только с фиксированным временем
        .sort((a, b) => a.finishTime - b.finishTime); // Сортировка по времени финиша
    const results = bugs.value.map((bug) => {
        // Находим позицию в отсортированном массиве финишировавших
        const positionIndex = finishedBugs.findIndex(fBug => fBug.id === bug.id);
        const position = positionIndex >= 0 ? positionIndex + 1 : null;

        return {
            position: position,
            color: bugColors.value[bug.id]
        };
    });

    try {
        const response = await fetch('http://127.0.0.1:8000/api/game-history/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ results })
        });
        
        if (!response.ok) throw new Error('Save failed');
        loadGameHistory();
    } catch (error) {
        console.error('Error saving game results:', error);
    }
};

// Добавляем состояние для анимации взрыва
const animationExplodeFrame = ref(null);
const explosionImages = ref(Array.from({length: 6}, (_, i) => `/images/bax/bax-${i+1}.png`));
// Загрузка истории
// В методе loadGameHistory
const loadGameHistory = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/game-history/last');
    lastGames.value = await response.json();
  } catch (error) {
    console.error('Error loading game history:', error);
  }
};

watch(lastGameMenuVisible, (visible) => {
  if (visible) loadGameHistory();
});

// Обновляем функцию анимации
const startAnimation = () => {
  console.log('Starting animation...');
  isRaceStarted.value = true;
  raceStartTime.value = performance.now();
  lastUpdateTime.value = performance.now();
  accumulatedTime.value = 0;
isPaused.value = false;
  pausedDuration.value = 0;
  winButtons.value.forEach(btn => {
    btn.occupied = false; // Сбрасываем занятость кнопок
    btn.menuVisible = false;
    btn.selected = false;
  });

  // ... предыдущий код ...
const animate = () => {
  if (isPaused.value) {
    animationFrame.value = requestAnimationFrame(animate);
    return;
  }

  const now = performance.now();
  let deltaTime = now - lastUpdateTime.value;
  lastUpdateTime.value = now;

  if (!isTabActive.value) {
    accumulatedTime.value += deltaTime;
    animationFrame.value = requestAnimationFrame(animate);
    return;
  }

  if (accumulatedTime.value > 0) {
    deltaTime += accumulatedTime.value;
    accumulatedTime.value = 0;
  }

  const safeDeltaTime = Math.min(deltaTime, 100) * 0.25;
  let activeBugs = 0;

  bugs.value.forEach((bug, index) => {
    if (bug.finished) return;
    activeBugs++;

    // Если взорван - пропускаем
    if (bug.explodeFrame !== undefined) return;
    // Проверяем истекло ли время головокружения
    if (bug.dizzy && performance.now() > bug.dizzyUntil) {
      bug.dizzy = false;
    }
    
    // Вычисляем вектор направления
    let dx = 0, dy = 0;

    // Фаза гонки
    if (bug.phase === 'racing') {
    // ПЕРЕМЕЩАЕМ ВЫЧИСЛЕНИЕ ИНДЕКСОВ В НАЧАЛО БЛОКА
    const totalSteps = bug.path.length;
    const currentIndex = Math.floor(bugProgress.value[index] * (totalSteps - 1));
    const safeIndex = Math.max(0, Math.min(currentIndex, totalSteps - 1));
    
    // Теперь используем вычисленные индексы
    const nextIndex = Math.min(currentIndex + 1, totalSteps - 1);
    
    dx = bug.path[nextIndex][0] - bug.path[currentIndex][0];
    dy = bug.path[nextIndex][1] - bug.path[currentIndex][1];
    bug.angle = Math.atan2(dy, dx) - Math.PI /2; // Изменение здесь
    
      
      // Обновление скорости
      if (now - lastSpeedChange.value[index] > speedChangeIntervals.value[index]) {
        bugSpeeds.value[index] = 0.0004 + Math.random() * 0.0004;
        lastSpeedChange.value[index] = now;
        speedChangeIntervals.value[index] = 500 + Math.random() * 1500;
      }
      
      // Обновление позиции
      bugProgress.value[index] += bugSpeeds.value[index] * (safeDeltaTime / 16);
      bugProgress.value[index] = Math.max(0, Math.min(bugProgress.value[index], 1));
      
     
      const point = bug.path[safeIndex];
      
      if (Array.isArray(point) && point.length >= 2) {
        bug.position = [point[0], point[1]];
      } else {
        console.error(`Invalid path point`, point);
        bug.finished = true;
      }
      
      // Проверка финиша (достигли конца пути)
       // Проверка финиша (достигли конца пути)
      if (bugProgress.value[index] >= 1 && !bug.finished) {
        const bugX = bug.position[0];
        const zone = finishZones.value.find(zone => 
          bugX >= zone.minX && bugX < zone.maxX
        );
        
        if (zone) {
          bug.targetButtonId = zone.buttonId;
          bug.phase = 'to_blue_point'; // Переходим в фазу движения к точке
          bug.bluePointProgress = 0;
          bug.finishTime = now;
        } else {
          // Если зона не найдена - взрываем
          bug.explodeFrame = 0;
        }
      }
    }
    // Фаза движения к точке финиша
    // Используйте простое движение без проверок:
else if (bug.phase === 'to_blue_point') {
  const button = winButtons.value.find(b => b.id === bug.targetButtonId);
  if (button) {
    const [targetX, targetY] = button.bluePoint;
    const dx = targetX - bug.position[0];
    const dy = targetY - bug.position[1];
    const distance = Math.sqrt(dx * dx + dy * dy);
    const speed = 200; // пикселей в секунду
    
    // Рассчитываем шаг на основе времени
    const step = Math.min(distance, speed * (deltaTime / 1000));
    
    if (step > 0) {
      // Обновляем позицию
      bug.position[0] += (dx / distance) * step;
      bug.position[1] += (dy / distance) * step;
      // Обновляем угол
      bug.angle = Math.atan2(dy, dx) - Math.PI / 2;
    }
    
    // Если осталось мало до цели, считаем финиш
    if (distance <= 5) {
      bug.finished = true;
      button.occupied = true;
    }
  }
}
  });
  
  if (activeBugs > 0) {
    animationFrame.value = requestAnimationFrame(animate);
  } else {
    cancelAnimationFrame(animationFrame.value);
    animationFrame.value = null;
    setTimeout(saveGameResults, 1000);
  }
};
// ... остальной код ...
  
  animationFrame.value = requestAnimationFrame(animate);
};


// Загружаем историю при открытии меню
watch(lastGameMenuVisible, (visible) => {
  if (visible) {
    loadGameHistory();
  }
});
// Получение стилей для позиционирования кнопки таракана
const getBugStyle = (index) => {
  const bug = menuBugs.value[index];
  return {
    left: `${bug.left}%`,
    top: `${bug.top}%`,
    width: `${bug.width}%`,
    height: `${bug.height}%`,
  };
};

// Обработчики событий
const hoverBug = (index) => {
  hoveredBugIndex.value = index;
};

const unhoverBug = () => {
  hoveredBugIndex.value = null;
};

const clickBug = (index) => {
  clickedBugIndex.value = index;
  
  // Сбрасываем состояние клика через 300 мс
  setTimeout(() => {
    clickedBugIndex.value = null;
  }, 300);
  
  // Здесь можно добавить логику при клике на таракана
  console.log(`Clicked on bug ${menuBugs.value[index].id}`);
};


const loadBetHistory = async () => {
  try {
    const response = await fetch('/api/v1/gameplay/games/bets/latest', {
      headers: { Authorization: `Bearer ${authToken}` }
    });
    
    if (!response.ok) throw new Error('Failed to load bet history');
    
    bets.value = await response.json();
  } catch (error) {
    console.error('Error loading bet history:', error);
  }
};

// Вызывать при открытии окна
watch(historyBetsVisible, (visible) => {
  if (visible) {
    loadBetHistory();
  }
});

// Обработчик для кнопки истории ставок
const toggleHistoryBets = () => {
  if (centerMenuVisible.value) {
    // Закрываем историю игр при открытии ставок внутри центра
    if (lastGameMenuVisible.value) {
      lastGameMenuVisible.value = false;
    }
    historyBetsVisible.value = !historyBetsVisible.value;
    historyBetsInsideCenter.value = true;
  } else {
    // Закрываем историю игр при открытии внешней истории ставок
    if (lastGameMenuVisible.value) {
      lastGameMenuVisible.value = false;
    }
    
    if (historyBetsInsideCenter.value) {
      historyBetsVisible.value = false;
      historyBetsInsideCenter.value = false;
    }
    historyBetsVisible.value = !historyBetsVisible.value;
  }
};

// Закрытие истории при открытии центрального меню
watch(centerMenuVisible, (newVal) => {
  if (!newVal && historyBetsInsideCenter.value) {
    // Закрываем историю при закрытии центрального меню
    historyBetsVisible.value = false;
    historyBetsInsideCenter.value = false;
  }
});

const saveCurrentBet = () => {
  betHistory.value.push(currentBet.value);
};

// Восстанавливаем предыдущую ставку из истории
const restorePreviousBet = () => {
  if (betHistory.value.length > 0) {
    currentBet.value = betHistory.value.pop();
  }
};
// Функция переключения видимости
const toggleLastGameMenu = () => {
  if (centerMenuVisible.value) return;
  
  // Закрываем историю ставок при открытии истории игр
  if (historyBetsVisible.value) {
    historyBetsVisible.value = false;
  }
  
  lastGameMenuVisible.value = !lastGameMenuVisible.value;
};
// Закрываем меню при открытии центрального
watch(centerMenuVisible, (newVal) => {
  if (newVal) {
    lastGameMenuVisible.value = false;
  }
});
// Исправляем логику сброса ставки (теперь это отмена последнего действия)
const handleResetClick = () => {
  stopAction(); // Останавливаем любые активные интервалы
  restorePreviousBet();
  
  // Анимация
  const resetBtn = document.querySelector('.reset-button');
  if (resetBtn) {
    resetBtn.classList.add('reset-clicked');
    setTimeout(() => resetBtn.classList.remove('reset-clicked'), 300);
  }
};

// Функция для удвоения ставки
const handleX2ButtonClick = () => {
  saveCurrentBet();
  const newBet = currentBet.value * 2;
  currentBet.value = newBet > maxBet ? maxBet : newBet;
  
  // Анимация кнопки
  const x2Btn = document.querySelector('.x2-button');
  if (x2Btn) {
    x2Btn.classList.add('x2-clicked');
    setTimeout(() => x2Btn.classList.remove('x2-clicked'), 300);
  }
};

// Функция для сброса ставки
const handleResetBetClick = () => {
  // Восстанавливаем предыдущее значение
  if (previousBet.value !== null) {
    currentBet.value = previousBet.value;
  }
  
  // Анимация кнопки
  const resetBtn = document.querySelector('.reset-button');
  if (resetBtn) {
    resetBtn.classList.add('reset-clicked');
    setTimeout(() => resetBtn.classList.remove('reset-clicked'), 300);
  }
};

// Добавлено: функция для добавления фиксированных сумм
const addToBet = (amount) => {
  saveCurrentBet();
  currentBet.value = Math.min(currentBet.value + amount, maxBet);
};

// Обновленные функции изменения ставки с сохранением состояния
const incrementBet = () => {
  // Сохраняем только при первом вызове
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  currentBet.value = Math.min(currentBet.value + betStep, maxBet);
};

const decrementBet = () => {
  // Сохраняем только при первом вызове
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  currentBet.value = Math.max(currentBet.value - betStep, minBet);
};

// Функция для сброса ставки к нулю
const handleOtmenaButtonClick = () => {
  stopAction(); // Останавливаем любые активные интервалы
  saveCurrentBet(); // Сохраняем текущее значение
  currentBet.value = 0;
  
  // Анимация
  const otmenaBtn = document.querySelector('.otmena-button');
  if (otmenaBtn) {
    otmenaBtn.classList.add('otmena-clicked');
    setTimeout(() => otmenaBtn.classList.remove('otmena-clicked'), 300);
  }
};

// Функции для управления зажатием кнопок
const startAction = (action) => {
  // Первое срабатывание сразу
  action();
  
  // Задержка перед началом быстрого повторения
  actionTimeout.value = setTimeout(() => {
    // Быстрое повторение
    actionInterval.value = setInterval(action, actionSpeed.value);
    
    // Ускорение с течением времени
    speedUpInterval.value = setInterval(() => {
      actionSpeed.value = Math.max(50, actionSpeed.value - 50);
      clearInterval(actionInterval.value);
      actionInterval.value = setInterval(action, actionSpeed.value);
    }, 500);
  }, 500);
};

const startIncrement = () => startAction(incrementBet);
const startDecrement = () => startAction(decrementBet);

const stopAction = () => {
  if (actionTimeout.value) {
    clearTimeout(actionTimeout.value);
    actionTimeout.value = null;
  }
  if (actionInterval.value) {
    clearInterval(actionInterval.value);
    actionInterval.value = null;
  }
  if (speedUpInterval.value) {
    clearInterval(speedUpInterval.value);
    speedUpInterval.value = null;
  }
  actionSpeed.value = 300;
};

// ==============================
// ОБНОВЛЕНИЕ КНОПОК СТАВОК
// ==============================
// Изменено: добавлены суммы для кнопок
const stavkiButtons = ref([
  { id: '25', amount: 25, src: '/images/buttons/25.png', alt: '25' },
  { id: '50', amount: 50, src: '/images/buttons/50.png', alt: '50' },
  { id: '100', amount: 100, src: '/images/buttons/100.png', alt: '100' },
  { id: '500', amount: 500, src: '/images/buttons/500.png', alt: '500' },
]);

const menuButtons = ref(
  Array.from({ length: 49 }, (_, index) => ({
    id: index,
    selected: false
  }))
);

// Кнопки победы
const winButtons = ref([
  { id: 7, occupied: false, hovered: false, top: 687, right: 4, bluePoint: [360, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 6, occupied: false, hovered: false, top: 687, right: 59, bluePoint: [305, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 5, occupied: false, hovered: false, top: 687, right: 114, bluePoint: [250, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 4, occupied: false, hovered: false, top: 687, right: 169, bluePoint: [195, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 3, occupied: false, hovered: false, top: 687, right: 224, bluePoint: [140, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 2, occupied: false, hovered: false, top: 687, right: 279, bluePoint: [85, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 1, occupied: false, hovered: false, top: 687, right: 334, bluePoint: [30, 687], menuVisible: false, menuTimer: null, selected: false },
]);

// ==============================
// ВЫЧИСЛЯЕМЫЕ СВОЙСТВА
// ==============================
// app.vue
// Вычисляемое свойство для диагональных кнопок
const diagonalButtons = computed(() => {
  const diagonals = [];
  for (let i = 0; i < 7; i++) {
    diagonals.push(i * 8); // 0, 8, 16, 24, 32, 40, 48
  }
  return diagonals;
});

const toggleMenuButton = (btn) => {
  // Разрешаем выделение для обеих вкладок
  btn.selected = !btn.selected;
};

// ==============================
// МЕТОДЫ УПРАВЛЕНИЯ ИНТЕРФЕЙСОМ
// ==============================
// Переключение кнопки меню


// Измененный метод для переключения центрального меню
const toggleCenterMenu = () => {
  const wasOpen = centerMenuVisible.value;
  centerMenuVisible.value = !wasOpen;

  // Добавьте вотчер для сброса состояния кнопок
  watch(centerWinMenuVisible, (isVisible) => {
    if (!isVisible) {
      // Сбрасываем выделение у всех кнопок победы
      winButtons.value.forEach(btn => {
        btn.selected = false;
      });
    }
  });
  
  // Закрываем меню победы при открытии нижнего меню
  if (!wasOpen) {
    centerWinMenuVisible.value = false;
  }
  if (centerMenuVisible.value && historyBetsVisible.value) {
    historyBetsVisible.value = false;
  }
};

// Обновите метод закрытия меню
const closeWinMenu = () => {
  centerWinMenuVisible.value = false;
};

// Добавьте вотчер для автоматического закрытия меню победы
watch(centerMenuVisible, (newVal) => {
  if (newVal) {
    centerWinMenuVisible.value = false;
  }
});

// Обработчики кликов по кнопкам
// Обработчик кликов по кнопкам победы (УПРОЩЕН)
const handleButtonClick = (btn) => {
   if (!isBettingPhase.value) return;
  
  if (btn.selected) {
    btn.selected = false;
    centerWinMenuVisible.value = false;
    return;
  }
  
  winButtons.value.forEach(b => b.selected = false);
  btn.selected = true;
  
  // Активируем новое центральное меню
  centerWinMenuVisible.value = true;
  activeWinMenuId.value = btn.id;
};

// Закрываем центральное меню при начале гонки
watch(isRaceStarted, (newVal) => {
  if (newVal) {
    centerWinMenuVisible.value = false;
  }
});

// ==============================
// ЛОГИКА ИГРЫ
// ==============================
// Генерация путей для тараканов
const generatePaths = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/generate-paths', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        bug_count: 7,
        duration: 10000,
        max_moves: 200,
        include_grid: true
      }),
    });

    if (!response.ok) throw new Error('Network error');
    return await response.json();
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
};

// Обновленная функция запуска гонки
const handleGenerateClick = async () => {
  if (animationExplodeFrame.value) {
    cancelAnimationFrame(animationExplodeFrame.value);
    animationExplodeFrame.value = null;
  }
  
  explosionActive.value = false;
  bugs.value = [];
  try {
    areWinButtonsDisabled.value = false;
    isRaceStarted.value = false;
    isLoading.value = true;
    bugs.value = []; // Важно: очищаем предыдущих тараканов
    
    // Сброс состояния кнопок
    winButtons.value.forEach(btn => {
      btn.occupied = false;
      btn.menuVisible = false;
      btn.hovered = false;
      btn.selected = false;
      if (btn.menuTimer) clearTimeout(btn.menuTimer);
    });

    // Сброс состояния тараканов
    bugProgress.value = [];
    bugSpeeds.value = [];
    lastSpeedChange.value = [];
    speedChangeIntervals.value = [];
    
    // Генерация новых путей
    const data = await generatePaths();
    // Добавить проверку
    if (!data || !data.paths) {
      throw new Error('Invalid response from path generator');
    }
    // Настройка зон финиша
    const cellSize = data.grid.cell_size;
    const offsetX = data.grid.offset_x;
    const totalWidth = data.grid.cols * cellSize;
    const zoneWidth = totalWidth / 7;

    finishZones.value = Array.from({ length: 7 }, (_, i) => ({
      minX: i * zoneWidth + offsetX,
      maxX: (i + 1) * zoneWidth + offsetX,
      buttonId: i + 1
    }));
    
    // Инициализация тараканов
    bugs.value = data.paths.map((path, index) => {
      bugProgress.value[index] = 0;
      bugSpeeds.value[index] = 0.004 + Math.random() * 0.004;
      lastSpeedChange.value[index] = Date.now();
      speedChangeIntervals.value[index] = 500 + Math.random() * 1500;
      
      if (!path || path.length === 0) {
        console.error(`Путь для таракана ${index} пустой!`);
        return null;
    }
    
    return {
        id: index,
        position: [...path[0]], // Первая точка пути
        path: path,
        finished: false,
        phase: 'racing',
        targetButtonId: null,
        bluePointProgress: 0,
        finishTime: null,
        explodeFrame: undefined,
        exploded: false
        
    };
}).filter(bug => bug !== null); // Фильтруем невалидных тараканов
    
    // Фиксируем реальное время начала гонки
    raceActualStartTime.value = Date.now();
    startAnimation();
    
  } catch (error) {
    console.error('Ошибка генерации путей:', error);
  } finally {
    isLoading.value = false;
  }
  // В функции generatePaths после получения данных
// После получения данных генерации
if (data.grid) {
    const cellSize = data.grid.cell_size;
    const offsetX = data.grid.offset_x;
    
    // Рассчитываем финишные зоны
    finishZones.value = data.grid.finish.map(f => ({
        minX: f.x * cellSize + offsetX,
        maxX: (f.x + 1) * cellSize + offsetX,
        buttonId: f.id
    }));
}
};
// Новая функция обновления прогресс-бара
const updateProgress = () => {
  if (isPaused.value) {
    animationFrameId.value = requestAnimationFrame(updateProgress);
    return;
  }
  const now = Date.now();
  
  if (breakInProgress.value) {
    // Прогресс перерыва
    const breakElapsed = now - phaseStartTime.value;
    progress.value = Math.min(100, (breakElapsed / breakInterval.value) * 100);
    isBettingPhase.value = true;

    if (breakElapsed >= breakInterval.value) {
      // Переход к фазе гонки
      breakInProgress.value = false;
      raceInProgress.value = true;
      phaseStartTime.value = now;
      handleGenerateClick(); // Добавленный вызов
    }
  } else if (raceInProgress.value) {
    // Прогресс гонки с учетом реального времени начала
    if (raceActualStartTime.value) {
      const raceElapsed = now - raceActualStartTime.value;
      progress.value = Math.min(100, (raceElapsed / raceInterval.value) * 100);
      centerWinMenuVisible.value = false; // Скрываем меню при начале гонки
      isBettingPhase.value = false;

      if (raceElapsed >= raceInterval.value) {
        // Завершение гонки
        raceInProgress.value = false;
        breakInProgress.value = true;
        phaseStartTime.value = now;
        raceActualStartTime.value = null;
        // Остановка анимации гонки
    if (animationFrame.value) {
      cancelAnimationFrame(animationFrame.value);
      animationFrame.value = null;
    }
        explodeAllBugs();
      }
    } else {
      // Если гонка официально началась, но тараканы еще не готовы
      // Показываем 0% прогресса до их появления
      progress.value = 0;
    }
  }
  
  animationFrameId.value = requestAnimationFrame(updateProgress);
};

// Обновленная функция анимации взрыва
const startExplosionAnimation = () => {
    if (!isTabActive.value) return; // Не запускаем анимацию если вкладка неактивна
  if (explosionActive.value) return;
  explosionActive.value = true;
  
  const explosionStartTime = performance.now();
  
  const animateExplosion = (timestamp) => {
    const elapsed = timestamp - explosionStartTime;
    
   // Обновляем кадры взрыва только для не финишировавших
    bugs.value.forEach(bug => {
      if (bug.explodeFrame !== undefined && !bug.finished) {
        const frame = Math.min(5, Math.floor(elapsed / 100));
        bug.explodeFrame = frame;
      }
    });
    
    if (elapsed < 600) {
      animationExplodeFrame.value = requestAnimationFrame(animateExplosion);
    } else {
      // Удаляем только взорванных тараканов, финишировавших оставляем
      bugs.value = bugs.value.filter(bug => bug.finished);
       // Сбрасываем занятость кнопок для не финишировавших
      winButtons.value.forEach(btn => {
        if (!bugs.value.some(bug => bug.targetButtonId === btn.id)) {
          btn.occupied = false;
        }
      });
      explosionActive.value = false;
      animationExplodeFrame.value = null;
    }
  };
  
  animationExplodeFrame.value = requestAnimationFrame(animateExplosion);
};
// ==============================
// ЖИЗНЕННЫЕ ЦИКЛЫ
// ==============================
// Масштабирование интерфейса
const updateScale = () => {
  const baseWidth = 390;
  const baseHeight = 844;
  const widthRatio = window.innerWidth / baseWidth;
  const heightRatio = window.innerHeight / baseHeight;
  scaleFactor.value = Math.min(widthRatio, heightRatio, 1);
};

onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibilityChange);
  updateScale();
  window.addEventListener('resize', updateScale);
  startRaceCycle(); // Запускаем цикл при монтировании
  loadGameHistory(); // Загружаем историю игр
  updateProgress(); // Запускаем прогресс-бар
   // Запускаем обновление прогресса
  animationFrameId.value = requestAnimationFrame(updateProgress);

  // Отслеживание изменения масштаба
  let lastDevicePixelRatio = window.devicePixelRatio;
  const updateZoomFactor = () => {
    const currentRatio = window.devicePixelRatio;
    if (currentRatio !== lastDevicePixelRatio) {
      lastDevicePixelRatio = currentRatio;
      document.documentElement.style.setProperty('--zoom-factor', currentRatio);
    }
  };
  
  updateZoomFactor();
  window.addEventListener('resize', updateZoomFactor);
  window.addEventListener('wheel', (e) => {
    if (e.ctrlKey) updateZoomFactor();
  });
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  // Очищаем таймеры при уничтожении компонента
  clearInterval(cycleTimer);
  clearTimeout(raceTimer);
  window.removeEventListener('resize', updateScale);
 if (animationFrameId.value) {
    cancelAnimationFrame(animationFrameId.value);
  }
});
const textButtonStyle = {
  backgroundImage: 'none',
  backgroundColor: 'rgba(255, 255, 255, 0.9)',
  borderRadius: '3px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
};

// Вычисляемый стиль для кнопок
const getButtonStyle = (btn) => {
  // Для режима Result - сохраняем стиль knopka-menu, но добавляем белый фон
  if (activeTab.value === 'result') {
    return {
      backgroundImage: "url('/images/buttons/knopka-menu.png')",
      backgroundColor: 'rgba(255, 255, 255, 0.9)',
      backgroundSize: 'cover',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center'
    };
  }
  return {}; // Для overtaking - стандартный стиль
};
</script>
<style scoped>
.language-switcher {
  position: absolute;
  top: 7px;
  right: 350px; /* Слева от иконки */
  z-index: 10;
  width: 30px;
  height: 30px;
  background-color: #000000;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}

.language-switcher:hover {
  transform: scale(1.05);
  filter: brightness(1.2);
}
/* Показываем диагональные кнопки в режиме Result */
.menu-button.visible-diagonal {
  visibility: visible;
  pointer-events: auto;
}
/* Показываем недиагональные кнопки в режиме Overtaking */
.menu-button.visible-non-diagonal {
  visibility: visible;
  pointer-events: auto;
}

/* Обновляем стили для вкладок */
.menu-tabs {
  display: flex;
  flex-direction: row-reverse; /* Инвертируем порядок */
}

/* Стиль для вкладки Result (теперь справа) */
.result-tab {
  border-radius: 0 50px 50px 0; /* Закругление справа */
  margin-left: 1px; /* Расстояние между вкладками */
}

/* Стиль для вкладки Overtaking (теперь слева) */
.overtaking-tab {
  border-radius: 50px 0 0 50px; /* Закругление слева */
}


/* Стиль для активной вкладки */
.tab-button.active {
  box-shadow: 0 0 8px rgba(255, 255, 0, 0.8);
  filter: brightness(1.2);
}
/* Добавим стиль для заблокированных кнопок */
.button-win-container.disabled .button-win {
  cursor: not-allowed !important;
  pointer-events: none;
  opacity: 1;
  filter: grayscale(-10%);
}
/* Существующие стили прогресс-бара */
.progress-container {
  position: absolute;
  top: 50px;
  left: 0;
  right: 0;
  width: 100%;
  padding: 0 10px;
  box-sizing: border-box;
  z-index: 5;
}

.progress-bar {
  height: 10px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 5px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #FFD700, #FFA500);
  border-radius: 5px;
  transition: width 0.1s linear; /* Более плавное обновление */
}

.make-bets-label {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 12px;
  color: #FFFFFF;
  text-align: center;
  margin-top: 5px;
  text-transform: uppercase;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.7);
  animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
  0% { opacity: 0.7; }
  100% { opacity: 1; }
}


.win-menu-center .menu-stavki {
  position: absolute;
  bottom: 79%;
  left: 56%;
  transform: translateX(-50%);
  margin-bottom: 3px;
  height: 50%;
  width: 130%;
  max-width: 640px;
  border: 1px solid red !important;
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .win-menu-center .menu-stavki,
  .bottom-menu {
    transform: translateX(-50%) scale(0.9);
    margin-bottom: 15px;
  }
}
/* Добавляем стили для изображений */
.bug-image {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80%; /* Размер изображения относительно кнопки */
  height: auto;
  z-index: 13; /* Выше кнопки */
  pointer-events: none; /* Чтобы не блокировало клики по кнопке */
  transition: transform 0.3s ease;
}

/* Увеличиваем изображение при наведении */
.bug-button-hovered .bug-image {
  transform: translate(-50%, -50%) scale(1.1);
}

@keyframes bug-click {
  0% { transform: translate(-50%, -50%) scale(1.1); }
  50% { transform: translate(-50%, -50%) scale(0.9); }
  100% { transform: translate(-50%, -50%) scale(1.1); }
}

/* Для мобильных устройств */
@media (max-width: 768px) {
  .bug-image {
    width: 70%;
  }
}
/* Контейнер для меню с относительным позиционированием */
.menu-container {
  position: relative;
  display: inline-block;
  width: 100%;
  height: 100%;
   border: 1px solid red !important;
}

/* Контейнер для кнопок тараканов */
.bug-buttons-container {
  position: absolute;
  top: 33%;
  left: -17%;
  width: 130%;
  height: 60%;
  z-index: 10; /* Поверх изображения меню */
   border: 1px solid red !important;
   
}

/* Стиль кнопки таракана */
.bug-button {
  position: absolute;
  background-color: transparent;
  cursor: pointer;
  border-radius: 5px;
  
  transition: all 0.3s ease;
  transform-origin: center;
  z-index: 11;
   border: 1px solid rgb(2, 247, 255) !important;
}

/* Эффекты при наведении */
.bug-button-hovered {
  transform: scale(1.15);
  position: relative;

  filter: 
    drop-shadow(0 0 8px rgba(255, 255, 0, 0.8))
    drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
  z-index: 12; /* Поднимаем над остальными при наведении */
}

/* Анимация при клике */
.bug-button-clicked {
  animation: bug-click 0.3s ease;
}

@keyframes bug-click {
  0% { transform: scale(1.15); }
  50% { transform: scale(0.95); }
  100% { transform: scale(1.15); }
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .bug-button-hovered {
    transform: scale(1.1);
    filter: 
      drop-shadow(0 0 5px rgba(255, 255, 0, 0.7))
      drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
  }
  
  @keyframes bug-click {
    0% { transform: scale(1.1); }
    50% { transform: scale(0.9); }
    100% { transform: scale(1.1); }
  }
}
/* Центральное фиксированное меню */
/* Обновленные стили для меню */
.win-menu-center {
  position: absolute;
  top: 72%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
  width: 80%;
  max-width: 360px;
  max-height: 80%;
  display: flex;
  justify-content: center;
  align-items: center;
}
.menu-container {
  position: relative;
  width: 100%;
  height: 80%;
}

.menuWin-image-center {
  width: 100%;
  height: auto;
  max-height: 100%;
  object-fit: contain;
}

/* Адаптация для маленьких экранов */
@media (max-width: 480px) {
  .win-menu-center {
    width: 95%;
    max-width: 320px;
  }
}
/* Желтая центральная подсветка */
.bug-button-hovered::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: radial-gradient(
    circle, 
    rgba(255, 255, 0, 0.8) 0%, 
    rgba(255, 255, 0, 0.4) 50%, 
    rgba(255, 255, 0, 0) 70%
  );
  z-index: 11;
  animation: pulse-glow 1s infinite alternate;
  pointer-events: none;
}

/* Анимация пульсации подсветки */
@keyframes pulse-glow {
  0% {
    opacity: 0.7;
    transform: translate(-50%, -50%) scale(0.9);
  }
  100% {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
  }
}

.menuWin-image-center {
  width: 125%; /* 100% от родительского контейнера */
  height: auto; /* Сохраняем пропорции */
  object-fit: contain; /* Сохраняем пропорции изображения */
  filter: drop-shadow(0 0 20px rgba(0, 0, 0, 0.7));
  animation: menu-appear 0.3s ease-out;
  margin-left: -12.5%;
  margin-top: 10%;
}

@keyframes menu-appear {
  0% { opacity: 0; transform: scale(0.9); }
  100% { opacity: 1; transform: scale(1); }
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .win-menu-center {
    width: 300px;
    height: 180px;
  }
}

@media (max-width: 480px) {
  .win-menu-center {
    width: 260px;
    height: 160px;
  }
}

/* Добавляем новые стили для позиционирования */
/* Позиция внутри центрального меню history-bets (в главном меню)*/
.history-bets.inside-center {
  position: absolute;
  top: -163%;
  margin-left: 38%;
  width: 240px;
  height: 150px;
  z-index: 1;
  transform: none !important;
}

/* Позиция снаружи (стандартная) */
.history-bets.outside-center {
  position: absolute;
  top: 57.3%;
  left: 43.5%;
  z-index: 10;
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .history-bets.inside-center {
    top: -162%;
    right: -5%;
    transform: scale(0.9);
  }
  
  .history-bets.outside-center {
    top: 50%;
    left: 40%;
  }
}

/* Обновляем переходы для плавности */
.x2-button, .reset-button {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.bet-counter-container {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  margin-top: -11.1%; /* Позиционирование по вертикали */
  left: 37.5%;
  transform: translateX(-50%);
  z-index: 12; /* Поверх других элементов */
  width: 23%;
   border: 1px solid red !important; 
}

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
  z-index: 9;
  background-image: url('/images/buttons/kryg-pravo.png');
}

.bet-button.plus {
  z-index: 9;
  background-image: url('/images/buttons/kryg-leva.png');
}

.bet-button:hover {
  transform: scale(1.1);
  filter: drop-shadow(0 0 3px rgba(255, 255, 0, 0.8));
}

.bet-button:active {
  transform: scale(0.95);
}

.bet-display {
  width: 70px;
  height: 23px;
  background-image: url('/images/buttons/seredina.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  align-items: flex-end;
  padding-top: 0px;
  justify-content: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 17px;
  color: #000000;
  margin: 0 -13px;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.8);
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .bet-counter-container {
    top: 30%;
     margin-top: 98%; /* Позиционирование по вертикали */
    left: 37%;
  }
  
  .bet-display {
    width: 80px;
    font-size: 17px;
    margin: 0 -18px;
    
  }
}

@media (max-width: 480px) {
  .bet-counter-container {
    top: 25%;
    transform: translateX(-50%) scale(0.9);
  }
  
  .bet-display {
    width: 70px;
   height: 23px;
    font-size: 12px;
  }
  
  .bet-button {
    width: 20px;
    height: 20px;
  }
}
.x2-button {
  position: relative;
  width: 54px;
  height: 40px;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  flex: none;
 margin-top: -13.5%;
  left: 27.7%;
  z-index: 9;
  flex-grow: 0;
   /* Для правильного отображения изображения */
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}.x2-button:hover {
  transform: scale(1.05);
  filter: 
    brightness(1.1)
    drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}
.stavki-buttons-container {
  position: absolute;
  display: flex;
  flex-direction: row;
  justify-content: center;
   gap: var(--stavki-gap, 10px); /* Используем CSS-переменную для управления расстоянием */
  width: 89%;
  height: 0%;
  left: -19%;
  z-index: 9;
  padding: 0px;
  border: 1px solid rgb(36, 223, 15) !important; 
}

.stavki-button {
  width: 50px;
  height: 26px;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  flex: none;
  order: 1;
  flex-grow: 0;
  margin-top: -23%;
}

.stavki-button:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.stavki-button:active {
  transform: scale(0.95);
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .stavki-buttons-container {
    top: 404%;
    left: -20%;
    width: 85%;
    transform: translateX(0); /* Сбрасываем transform если был */
    padding-left: 15px; /* Регулируйте значение по необходимости */
    box-sizing: border-box; /* Чтобы padding не увеличивал общую ширину */
  }
  .stavki-button {
    margin-top: 0%;
    transform: none;
    
  }
   .x2-button {
    width: 55px;
    height: 32px;
    left: 31%;
    margin-top: 11.1%;
  }
  
}

@media (max-width: 480px) {
  .stavki-buttons-container {
    top: 395%;
    left: -68%;
    gap: var(--stavki-gap, 5px); 
    

  }
  
  .stavki-button {
    width: 48px;
    height: 31px;
    
  }
  .x2-button {
    width: 45px;
    height: 32px;
    left: 20%;
  }
}

.otmena-button {
  position: absolute;
  z-index: 10;
  cursor: pointer;
  width: 25px; /* Настройте по размеру изображения */
  height: 25px;
  margin-top: -11%;
  transform: translateX(-50%);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  margin-left: 8%;
  
  /* Для правильного отображения изображения */
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}

.otmena-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.otmena-button:active {
  transform: translateX(-50%) scale(0.95);
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .otmena-button {
    width: 25px;
    height: 25px;
    left: 0%;
  }
}

@media (max-width: 480px) {
 .otmena-button {
    width: 24px;
    height: 24px;
    left: -35.4%;
    top: 382%;
  }
}
/* Стиль для скрытия диагональных кнопок */
.menu-button.diagonal {
  visibility: hidden; /* Скрываем кнопку, но сохраняем место */
  /* Или можно полностью убрать: */
  /* display: none; */
}

/* Альтернатива: сделать диагональные кнопки прозрачными и не кликабельными */
.menu-button.diagonal {
  opacity: 0;
  pointer-events: none;
}
/* Стили для новой кнопки сброса */
.reset-button {
  position: absolute;
  z-index: 10;
  cursor: pointer;
  width: 48px;
  height: 24px;
  margin-top: -11%; /* Центрируем по вертикали */
  margin-left: 50%;
  
}

.reset-button:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .reset-button {
    width: 50px;
    height: 24px;
    top: 392%;
    left: 56%;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
}
/* Анимации для кнопок */
@keyframes resetPulse {
  0% { transform: translateX(-50%) scale(1); }
  50% { transform: translateX(-50%) scale(0.92); }
  100% { transform: translateX(-50%) scale(1); }
}
@keyframes otmenaPulse {
  0% { transform: scale(1); }
  50% { transform: scale(0.92); }
  100% { transform: scale(1); }
}

@media (max-width: 480px) {
  .reset-button {
    width: 41px;
    height: 21px;
    top: 382%;
    left: 8%;
  }
}
.menu-buttons-container {
  
  position: absolute;
  top: 227%; /* Регулируйте по необходимости */
  left: 55.1%;
  transform: translate(-50%, -50%);
  
  display: grid;
  grid-template-columns: repeat(7, 1fr); /* 7 колонок */
  grid-template-rows: repeat(7, 1fr); /* 7 строк */
  z-index: 4; /* Поверх изображений меню */
  --button-width: 42px; /* Ширина кнопок по умолчанию */
  --button-height: 30px; /* Высота кнопок по умолчанию */
   /* Разделяем горизонтальное и вертикальное расстояние */
  --column-gap: 4px; /* Горизонтальное расстояние */
  --row-gap: 11px;   /* Вертикальное расстояние (можно увеличивать отдельно) */
   
  /* Рассчитываем размер контейнера */
  width: calc((var(--button-width) * 7) + (var(--column-gap) * 6));
  height: calc((var(--button-height) * 7) + (var(--row-gap) * 6));
  
  display: grid;
  grid-template-columns: repeat(7, var(--button-width));
  grid-template-rows: repeat(7, var(--button-height));
  
  /* Разделяем горизонтальное и вертикальное расстояние */
  column-gap: var(--column-gap);
  row-gap: var(--row-gap);
  
}
/* Стили для взрыва */
.explosion {
  position: absolute;
  width: 50px;
  height: 50px;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  transform: translate(-50%, -50%);
  z-index: 5; /* Выше обычных тараканов */
  pointer-events: none;
  animation: explosion-pulse 0.1s infinite alternate;
}

@keyframes explosion-pulse {
  0% { transform: translate(-50%, -50%) scale(1); }
  100% { transform: translate(-50%, -50%) scale(1.1); }
}
/* Стили для новой кнопки Group 164 */
.group-164-button {
  position: absolute;
  margin-top: 85%;
  margin-left: 32%;
  z-index: 8; /* Выше других элементов меню */
  cursor: pointer;
  width: 112px; /* Размер по вашему изображению */
  height: 68px;
  
  /* Позиционируем кнопку поверх изображения stavki.png */
  top: 45%; /* Регулируйте по необходимости */
  left: 48.5%;
  transform: translateX(-50%);
  
  /* Анимация при наведении */
  transition: transform 0.3s ease, filter 0.3s ease;
}

.group-164-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.group-164-button:active {
  transform: translateX(-50%) scale(0.95);
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .group-164-button {
    width: 113px;
    top: 45%;
    left: 48.5%;
  }
}
/*@media (max-width: 768px) {
  .main-color {
    
    
  }
}*/
@media (max-width: 480px) {
  .group-164-button {
    width: 80px;
    top: 41%;
    
  }
}
.menu-button {
  background-image: url('/images/buttons/knopka-menu.png');
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  cursor: pointer;
  transition: filter 0.2s ease;
   border-radius: 2px; /* Регулируйте это значение по вкусу */
  overflow: hidden; /* Важно для корректного отображения закругления */
  /*border: 1px solid red !important;*/
  
}

.menu-button.selected {
  /* Изменяем цвет на желтый с помощью фильтра */
  filter: 
    brightness(1.2) 
    sepia(1) 
    saturate(5) 
    hue-rotate(5deg);
}

/* Адаптация для мобильных */
@media (max-width: 768px) {
  .menu-buttons-container {
    width: 200px;
    height: 175px;
    grid-gap: 2px;
    top: 230%; /* Корректировка позиции */
    left: 55%;
     /* Разделяем горизонтальное и вертикальное расстояние */
  --column-gap: 4px; /* Горизонтальное расстояние */
  --row-gap: 11px;   /* Вертикальное расстояние (можно увеличивать отдельно) */
   
  /* Рассчитываем размер контейнера */
  width: calc((var(--button-width) * 7) + (var(--column-gap) * 6));
  height: calc((var(--button-height) * 7) + (var(--row-gap) * 6));
  
  display: grid;
  grid-template-columns: repeat(7, var(--button-width));
  grid-template-rows: repeat(7, var(--button-height));
  
  /* Разделяем горизонтальное и вертикальное расстояние */
  column-gap: var(--column-gap);
  row-gap: var(--row-gap);
  }
  
  
}

@media (max-width: 480px) {
  .menu-buttons-container {
    top: 219%;
    left: 4%;
    --column-gap: 4px;
    --row-gap: 6px;
    --button-width: 36px;
    --button-height: 30px;
    width: calc((var(--button-width) * 7) + (var(--column-gap) * 6));
    height: calc((var(--button-height) * 7) + (var(--row-gap) * 6));
    display: grid;
    grid-template-columns: repeat(7, var(--button-width));
    grid-template-rows: repeat(7, var(--button-height));
    -moz-column-gap: var(--column-gap);
    column-gap: var(--column-gap);
    row-gap: var(--row-gap);
    }
  
  .menu-button {
    background-size: contain;
  }
}
/* Добавляем стили для заблокированных кнопок */
.button-win-container.disabled {
  
  cursor: default !important;
  pointer-events: none;
  filter: grayscale(70%);
}
/* Отключаем эффекты при блокировке */
.button-win-container.disabled .button-win {
  cursor: default !important;
  pointer-events: none;
}
.group-image {
  position: absolute;
  top: -165%;
  left: 0;
  width: 119px;
  height: 151px;
  z-index: 3;
}

@media (max-width: 480px) {
  .group-image {
    top: -160%;
    left: -10%;
    transform: scale(0.8);
  }
}

.main-bg-container {
  transform-origin: top center;
  width: 100%;
  height: 100%;
  margin: 0 auto;
  position: relative;
}
.labirint-bg {
  position: relative;
  top: 8%;
  left: 0;
  width: 100%; /* Занимает всю ширину родителя */
  max-width: 100%; /* Максимальная ширина как в эталоне */
  height: auto; /* Автоматическая высота */
  aspect-ratio: 390 / 689; /* Сохраняем соотношение сторон 390x689 */
  background-image: url('/images/background/labirint.png');
  background-size: contain; /* Изображение полностью помещается в элемент */
  background-position: center;
  background-repeat: no-repeat;
  z-index: 3;
  opacity: 1;
  /* Адаптивные корректировки */
  @media (max-width: 390px) {
    max-width: 100%; /* На маленьких экранах занимает всю ширину */
  }
  
  /* Для очень широких экранов */
  @media (min-width: 768px) {
    left: 50%;
    transform: translateX(-50%); /* Центрируем по горизонтали */
  }
}



/* Стили для черной менюшки */
.panel-up {
  position: relative;
  width: 390px;
  height: 43px;
  left: calc(50% - 390px/2);
  margin-top: -695px; /* Фиксированная позиция вверху экрана */
  z-index: 4;
  background: #000000;
  border: 1px solid red !important; /* Для отладки */
}

/* Контейнер для элементов баланса */
.balance-container {
  position: relative;
  width: 100%;
  height: 100%; 
  gap: 10px; /* Расстояние между элементами */
  
}

.main-color {
  background-color: #1E031E;
  min-height: 100vh;
  position: relative; /* Для позиционирования */
}

.tarakan {
  position: absolute; /* Вместо relative */
  width: 32px;
  height: 38px;
  transform: translate(-50%, -50%); /* Центрирование */
  z-index: 3;
  transition: left 0.1s linear, top 0.1s linear, filter 0.3s ease; /* Плавное движение */ /* Полупрозрачный фон */
    border: 1px solid red !important;
filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.8))
          drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
 
/* Анимация пульсации свечения */
  animation: glow-pulse 2s infinite alternate;          
}
/* Анимация головокружения */
.tarakan.dizzy {
  animation: spin 0.5s linear infinite;
}

@keyframes spin {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}
/* Усиление свечения при наведении */
.tarakan:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 255, 1))
          drop-shadow(0 0 10px rgba(255, 255, 255, 0.8));
}

/* Контейнер для кнопки и индикатора */
.button-win-container {
  position: absolute;
  width: 52px;
  height: 64px;
  z-index: 4;
  border: 1px solid red !important;
}

/* Позиции контейнеров */
.button-win-1 { top: 81.4%; right: 4px; }
.button-win-2 { top: 81.4%; right: 59px; }
.button-win-3 { top: 81.4%; right: 114px; }
.button-win-4 { top: 81.4%; right: 169px; }
.button-win-5 { top: 81.4%; right: 224px; }
.button-win-6 { top: 81.4%; right: 279px; }
.button-win-7 { top: 81.4%; right: 334px; }

/* Стили для кнопок победы */
.button-win {
  position: absolute;
  width: 100%;
  height: 100%;
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  cursor: pointer;
  z-index: 3;
   opacity: 1; /* Гарантируем нормальную прозрачность */
  transition: all 0.3s ease;
}
/* Адаптация для маленьких экранов */


@media (max-width: 390px) {
  .button-win-1,.button-win-2,.button-win-3,.button-win-4,.button-win-5,.button-win-6,.button-win-7 {
    transform: scale(0.9); /* Уменьшаем размер контейнера */
    margin-top: 10%;
  }
}

/* Индикатор поверх кнопки */
.win-indicator {
  position: absolute;
  width: 100%;
  height: 100%;
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  top: -55px; /* Сдвиг вверх */
  left: 0;
  z-index: 2; /* Выше основной кнопки */
  pointer-events: none; /* Игнорирует клики */
}
/* ОБНОВЛЕННЫЕ СТИЛИ ДЛЯ ЭЛЕМЕНТОВ БАЛАНСА *//* Текст "Balance" ...*/
.balance-text {
  position: absolute;
  /* Используем CSS-переменные для позиционирования */
  top: var(--text-top, 16px); /* Значение по умолчанию 10px */
  right: var(--text-right, 120px); /* Значение по умолчанию 80px */
  
  /* Стили текста */
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 350;
  font-size: 10px;
  line-height: 12px;
  text-transform: uppercase;
  color: #FFFFFF;
  text-align: right;
  white-space: nowrap;
  z-index: 3;
}
.icon-1 {
  background-image: url('/images/icons/Group.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center; /* Центрирование фонового изображения */
  
  /* Размеры иконки должны быть меньше кнопки */
  width: 24px;  /* Уменьшаем ширину */
  height: 17px; /* Уменьшаем высоту */
  
  /* Убираем лишние отступы */
  margin: 0;
  padding: 0;
}
.icon-button {
  position: absolute;
  top: 7px; /* Позиция как у иконки */
  right: 10px;
  z-index: 3;
  /* Размеры черного квадрата */
  width: 30px; 
  height: 30px;
  /* Черный фон */
  background-color: #000000;
  border-radius: 8px; /* Небольшое скругление углов */
  /* Центрирование содержимого */
  display: flex;
  justify-content: center;
  align-items: center;
  /* Эффекты */
  box-shadow: 0 2px 4px rgba(0,0,0,0.5);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid red !important;
}


/* Анимация выделения */
.menu-button.selected {
  filter: 
    brightness(1.2) 
    sepia(1) 
    saturate(5) 
    hue-rotate(5deg);
}

/* Анимация наведения */
.menu-button:hover {
  transform: scale(1.05);
  filter: 
    drop-shadow(0 0 8px rgba(255, 255, 0, 0.8))
    drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
}

/* Анимация нажатия */
.menu-button:active {
  transform: scale(0.95);
}

/* Специфичные стили для диагональных кнопок в Result */
.menu-button.diagonal {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
  border: 1px solid #FF8C00;
  box-shadow: 0 0 8px rgba(255, 165, 0, 0.6);
}
.main-bg {
  background-image: url('/images/background/Frame 560.png');
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
  /* Фиксированные размеры контейнера ///*/
  width: 390px;
  min-height: 844px;
  height: 100vh;
  max-height: 844px;
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  z-index: 1;
}
/* Обновленные стили для кнопок */
.button-1 {
  position: absolute;
  background-image: url('/images/buttons/Group 255.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  height: 50px;
  cursor: pointer;
  z-index: 3;
  top: 22px;
  left: 8px;
  border: 1px solid red !important;
}
/* Корректируем позиционирование кнопки */
.button-2 {
  position: absolute;
  background-image: url('/images/buttons/Group 168.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 170px;
  height: 46px;
  cursor: pointer;
  z-index: 3;
  top: 22px;
  right: 110px; /* Исправленное позиционирование */
  border: 1px solid red !important; 
  
}

.button-3 {
   position: absolute;
  background-image: url('/images/buttons/Group 255.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  height: 50px;
  cursor: pointer;
  z-index: 3;
  /* Точное позиционирование без трансформаций */
  top: 22px;
  left: 291px; /* Фиксированный отступ от левого края */
  border: 1px solid red !important;
}
.menu-button {
  visibility: hidden;
  pointer-events: none;
}
.button-text {
  font-family: 'Hero', 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 14x;
  line-height: 17px;
  display: flex;
  align-items: center;
  text-align: center;
  text-transform: uppercase;
  color: #FFFFFF;
  text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
  white-space: pre-line; /* Для обработки переносов через <br> */
}

/* Специфичные стили для каждой кнопки */
.button-1 .button-text {
  padding: 0 10px;
  text-align: center;
}

.button-2 .button-text {
  font-size: 24px; /* Чуть больше для центральной кнопки */
  padding: 0 15px;
}

.button-3 .button-text {
  padding: 0 10px;
  text-align: center;
}
.menu-button.visible {
  visibility: visible;
  pointer-events: auto;
}
/* Стили для панели */
.panel-layer {
position: absolute;
  width: 100%; /* Фиксированная ширина */
  height: 11%;
  bottom: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, #000000 100%);
  z-index: 4;
 /* box-shadow: 0 0 0 2px red; /* Обводка без размытия */
}
/* Стили для центрального меню */
.center-menu {
  position: absolute;
  bottom: calc(464%); /*  над кнопкой */
  left: 50%;
  transform: translateX(-50%);
  z-index: 5; /* Выше других элементов */
  width: 100%; /* Размеры меню */
  height: 100%;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  /*gap: 10px; /* Расстояние между изображениями */
  cursor: default;
   /* Расстояние между изображениями */
  pointer-events: auto;
  border: 1px solid red !important;
}
/* Специфичные стили для изображения stavki */
.stavki-image {
  margin-left: 2%;
  top: -1%; /* Поднимаем изображение ближе к основному */
  width: 95%; /* Уменьшаем ширину */
  max-width: 100%; /* Максимальная ширина */
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  cursor: default;
  border: 1px solid red !important;
  
}


.menu-image {
  width: 100%;
  max-width: 390px; /* Максимальная ширина основного изображения */
  height: auto;
  object-fit: contain;
  border: 1px solid red !important;
}

.stavki-image {
  width: 95%; /* Ширина относительно основного изображения */
  max-width: 370px; /* Чуть меньше основного */
  margin-top: -1%; /* Поднимаем ближе к основному */
  object-fit: contain;
  
}
/* Адаптация для мобильных устройств */
@media (max-width: 768px) {
  .center-menu {
    bottom: calc(400%); /* Корректируем позицию */
    left: 50%;
    top: -407px;
     /* Уменьшаем размер */
     transform: translateX(-50%) scale(0.9);
  }
  
  .menu-image {
    max-width: 300px;
  }
  
  .stavki-image {
    max-width: 285px;
  }
  .group-image{
    max-width: 285px;
  }
}

@media (max-width: 480px) {
  .center-menu {
    bottom: calc(350%); /* Дополнительная корректировка */
    margin-top: 0%;
    left: 95%;
    transform: translateX(-50%) scale(0.9); /* Еще меньше */
  }
  /* Стили для диагональных кнопок в режиме Result */
.menu-button.diagonal {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
  border: 1px solid #FF8C00 !important;
  box-shadow: 0 0 8px rgba(255, 165, 0, 0.6) !important;
}

.menu-button.diagonal:hover {
  transform: scale(1.1) !important;
  filter: 
    drop-shadow(0 0 8px rgba(255, 255, 0, 0.8))
    drop-shadow(0 0 15px rgba(255, 215, 0, 0.6)) !important;
}

.menu-button.diagonal.selected {
  background: linear-gradient(135deg, #FF8C00 0%, #FF4500 100%) !important;
  box-shadow: 0 0 12px rgba(255, 69, 0, 0.8) !important;
  animation: pulse-diagonal 0.5s infinite alternate;
}

@keyframes pulse-diagonal {
  0% { transform: scale(1); }
  100% { transform: scale(1.05); }
}
  .menu-image {
    max-width: 250px;
    transform: translateX(-50%) scale(0.9);
  }
  
  .stavki-image {
    
    max-width: 240px;
    transform: translateX(-50%) scale(0.9);
  }
  
  .group-image {
    margin-top: 9%;
    left: -32%;
    max-width: 240px;
    transform: translateX(-50%) scale(0.8);
  }
}
.menu-button.button-visible {
  visibility: visible;
  pointer-events: auto;
}
.button-balans {
  position: absolute;
  /* Используем CSS-переменные для позиционирования */
  top: var(--button-top, 10px); /* Значение по умолчанию 10px */
  right: var(--button-right, 45px); /* Значение по умолчанию 20px */
  /* Стили кнопки */
  display: inline-flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 49px;
  height: 19px;
  min-width: 49px;
  min-height: 19px;
  padding: 3px 6px;
  background: linear-gradient(180deg, #7F00FE 0%, #66008F 98.9%);
  box-shadow: 0px 1px 0px #191E47;
  border-radius: 49px;
  color: white;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  z-index: 3;
}

/* Эффекты при наведении */
.button-1:hover, .button-2:hover, .button-3:hover {
  filter: 
    brightness(1.1) /* Увеличение яркости (первое число - размытие, второе - прозрачность).*/
    drop-shadow(6 6 8px rgba(178, 56, 58, 0.8)); 
}

/* Эффект при нажатии */
.button-1:active, .button-2:active,.button-3:active {
  transform: scale(0.98);
  filter: brightness(0.95);
}
/* Гарантируем, что контент будет поверх панели */
/* Адаптивность для маленьких экранов */
/* Адаптация для мобильных */


.menu-image {
  max-width: 80vw;
  max-height: 80vh;
  object-fit: contain;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
  transition: opacity 0.2s ease;
}
.button-win-disabled {
  opacity: 1;
  filter: grayscale(70%);
}

/* *****************************************  БЛОК ДЛЯ @  **********************************/
@media (max-width: 390px) {
  .black-menu {
    gap: 20px; /* Уменьшаем расстояние между элементами */
    padding: 4px 10px; /* Уменьшаем боковые отступы */
    left: 0;
    right: 0;
  }
}

@media (max-width: 390px) {
  .main-bg {
    width: 100vw;
    max-width: 390px;
    background-size: contain;
  }
  
  .panel-layer {
    width: 100%;
  }
  
  /* Уменьшаем кнопки пропорционально */
  .button-1 {
    width: 70px;
    height: 40px;
    top: 20px;
    left: 15px;
  }
  
  .button-2 {
    width: 130px;
    height: 40px;
    top: 20px;
    right: 15px;
  }

  .button-3 {
    width: 130px;
    height: 40px;
    top: 20px;
    right: 15px;
    
  }
}
/* Стили для контейнера вкладок */
.menu-tabs {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  height: 24px; /* Высота кнопок */
  z-index: 15; /* Поверх других элементов */
}

/* Общие стили для кнопок вкладок */
.tab-button {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 12px;
  line-height: 14px;
  text-transform: uppercase;
  color: #FFFFFF;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 50px;
  padding: 4px 0;
}

/* Стиль для вкладки Result */
.result-tab {
  background: rgba(0, 0, 0, 0.4);
  margin-right: 1px;
}

/* Стиль для вкладки Overtaking */
.overtaking-tab {
  background: linear-gradient(180deg, rgba(127, 0, 254, 0.7) 0%, rgba(102, 0, 143, 0.7) 98.9%);
  border: 1px solid #6B51FF;
}

/* Стиль для активной вкладки */
.tab-button.active {
  box-shadow: 0 0 8px rgba(255, 255, 0, 0.8);
  filter: brightness(1.2);
}

/* Эффекты при наведении */
.tab-button:hover {
  filter: brightness(1.1);
}

.tab-button:active {
  transform: scale(0.98);
}
/* Для кнопок добавляем transform для улучшения производительности */
.button-1,
.button-2,
.button-3 {
  transform: translateZ(0);
  will-change: transform;
  display: flex;
  justify-content: center; /* Горизонтальное центрирование */
  align-items: center;     /* Вертикальное центрирование */
}

/* Адаптивность по высоте */
@media (max-height: 844px) {
  .main-bg {
    height: 100vh;
    max-height: none;
  }
}


/* Анимация пульсации */
@keyframes glow-pulse {
  0% {
    filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.8))
            drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
  }
  100% {
    filter: drop-shadow(0 0 5px rgba(255, 255, 255, 1))
            drop-shadow(0 0 10px rgba(255, 255, 255, 0.7));
  }
}
@media (max-width: 768px) {
  .labirint-bg {
    background-size: contain;
    background-position: top center;
  }
}

</style>
