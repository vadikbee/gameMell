<template>
  <div class="main-color">
    <!-- История ставок в основном интерфейсе -->
     <audio ref="backgroundMusic" src="/sounds/backgroundMusic.mp3" loop></audio>
    <!-- Контейнер для масштабирования фона -->
    
    <div 
      class="main-bg-container" 
      
    ></div>
 
    <!-- Основной игровой контейнер -->
    <div class="main-bg">
      <HistoryBets 
      v-if="historyBetsVisible && !historyBetsInsideCenter" 
      ref="historyBetsRef"
      :isCenterMenuOpen="centerMenuVisible" 
      :bets="betHistoryFromApi"
      :title="t('history_bets')"
      class="outside-center"
    />
      <!-- Центральное фиксированное меню -->
      <div v-if="centerWinMenuVisible" class="win-menu-center" style="z-index: 100" ref="winMenuCenterRef">
    <StavkiMenu
      :currentBet="currentBet"
      :stavkiButtons="stavkiButtons"
      context="win"
      @win-bet-click="placeSectionBet"
      @otmena-click="resetAllBets"
      @reset-click="undoLastBet"
      @decrement-start="startDecrement"
      @increment-start="startIncrement"
      @stop-action="stopAction"
      @add-bet="addToBet"
      @x2-click="multiplyBet"
      :playBetClick="playBetClick" 
      @button-click="playStakeActionClick"
      @group164-click="placeBet"
    />
    
    <div class="menu-container">
      <div class="win-menu-title">
        {{ t('who_reaches_section') }}
      </div>
       <div v-if="raceInProgress" class="next-race-notice">
      {{ t('next_round_bet') }}
    </div>
      <img 
        :src="`/images/menus/Frame 575-${activeWinMenuId}.png`" 
        alt="Menu" 
        class="menuWin-image-center"
      />
      
      <!-- Обновленный контейнер для тараканов с обработчиком выбора -->
      <div class="bug-buttons-container">
        <div 
          v-for="(bug, index) in menuBugs"
          :key="index"
          class="bug-button"
          :class="{
            'bug-button-hovered': hoveredBugIndex === index,
            'bug-button-clicked': clickedBugIndex === index,
            'selected': selectedBugs.includes(bug.id) && selectedTrap === activeWinMenuId,
            'locked': lockedBugsArray.includes(bug.id)
          }"
          @mouseenter="hoverBug(index)"
          @mouseleave="unhoverBug"
          @click="toggleBugSelection(bug.id)"
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
  </div>\

      
      <!-- Фон лабиринта -->
      <div class="labirint-bg"></div>
      
      <!-- Контейнеры для кнопок победы -->
      <div 
      v-for="(btn, index) in winButtons" 
      :key="'btn-'+btn.id"
      class="button-win-container"
      :ref="el => setButtonWinContainerRef(btn.id, el)"
      :class="{
        'initial-animation': initialAnimationActive,
        [`button-win-${index + 1}`]: true
      }"
    >
         <!-- Контейнер для цветных индикаторов (обновлено) -->
 
<div class="color-indicators" v-if="btn.bugs.length > 0">
  <div 
    v-for="(bugId, bugIndex) in btn.bugs" 
    :key="bugIndex"
    class="color-indicator"
    :style="{ 
      width: `${100 / btn.bugs.length}%`,
      left: `${bugIndex * (100 / btn.bugs.length)}%`,
      backgroundImage: 'url(' + getColorImage(bugColors[bugId], btn.bugs.length) + ')'
    }"
  ></div>
</div>

            <!-- Индикатор победы -->
        <div 
          v-if="btn.occupied" 
          class="win-indicator"
          :style="{ backgroundImage: `url('/images/buttons/Property 1=Variant2.png')` }"
        ></div>

        <!-- Основная кнопка победы -->
        <div 
          class="button-win"
          @click="handleButtonClick(btn)"
          :style="{ 
            backgroundImage: btn.selected 
              ? `url('/images/buttons/knopka-win.png')` 
              : `url('/images/buttons/Property 1=Default.png')`,
            cursor: 'pointer'
          }"
        ></div>
          
        <div v-if="btn.betAmount" class="trap-bet-amount">
    {{ btn.betAmount }}₽
  </div>
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
          ref="button1Ref"
          class="button-1" 
          id="Button-1" 
          :class="{ 'initial-animation': initialAnimationActive }"
          @click="!centerWinMenuVisible && toggleLastGameMenu()"
        >
          <span class="button-text bth-1-text">{{ t('last_games') }}</span>
        </div>
        <!-- Меню последней игры -->
        <LastGameMenu 
          v-if="lastGameMenuVisible"
          ref="lastGameMenuRef"
          :isCenterMenuOpen="centerMenuVisible"
          :games="lastGames"
        />
        <div 
          ref="button2Ref"
          class="button-2" 
          id="Button-2" 
          :class="{ 'initial-animation': initialAnimationActive }"
          @click="!centerWinMenuVisible && toggleCenterMenu()"
        >
          <span class="button-text bth-2-text">{{ t('make_a_bet') }}</span>
        </div>
        <div 
          ref="button3Ref"
          class="button-3" 
          id="Button-3" 
          :class="{ 'initial-animation': initialAnimationActive }"
          @click="!centerWinMenuVisible && toggleHistoryBets()"
        >
          <span class="button-text">{{ t('history_bets') }}</span>
        </div>
       
        <!-- Центральное меню -->
        <div v-if="centerMenuVisible" class="center-menu" ref="centerMenuRef">
        <!-- Изменение здесь: динамический src -->
        <img 
          :src="activeTab === 'overtaking' ? '/images/menus/center-buttom.png' : '/images/menus/center-buttom-result.png'" 
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
          {{ t('OVERTAKING') }}
        </div>
        <div 
          class="tab-button result-tab"
          :class="{ active: activeTab === 'result' }"
          @click="setActiveTab('result')"
        >
          {{ t('RESULT') }}
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
      'button-visible': isButtonVisible(btn),
      'has-bet': btn.betAmount > 0, // Добавляем класс для ставок
      'locked': lockedBugs.has(Math.floor(btn.id / 7)) // Добавляем класс блокировки
    }"
    @click="toggleMenuButton(btn)"
  >
    <!-- Отображаем сумму ставки если есть -->
    <div v-if="btn.betAmount" class="bet-amount-display">
      {{ btn.betAmount }}₽
    </div>
  </div>
</div>

         <PodiumResults 
          v-if="lastGames.length > 0" 
          :games="lastGames" 
          class="group-image"
        />
            <HistoryBets 
            v-if="historyBetsVisible && historyBetsInsideCenter" 
            ref="historyBetsRef"
            :isCenterMenuOpen="centerMenuVisible" 
            :insideCenter="true"
            :title="t('history_bets')"
            :bets="betHistoryFromApi"
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
              :class="{ 'group-164-clicked': isGroup164Clicked }"
              @click="() => { playBetClick(); placeBet(); }"
              @mousedown="isGroup164Clicked = true"
              @mouseup="isGroup164Clicked = false"
              @mouseleave="isGroup164Clicked = false"
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
            </div>
            <div class="x2-button-container">
            <img 
              src="/images/buttons/x2.png" 
              alt="x2"
              class="x2-button"
              :class="{ 'x2-clicked': isX2Clicked }"
              @click="handleX2ButtonClick"
              @mousedown="isX2Clicked = true"
              @mouseup="isX2Clicked = false"
              @mouseleave="isX2Clicked = false"
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
         <!-- Обновленный элемент управления музыкой -->
  <div class="music-control" @click="toggleMusic">
    <div class="music-icon" :class="{ 'music-off': !isMusicPlaying }"></div>
    <div v-if="!isMusicPlaying" class="music-hint">!</div>
  </div>
          <div class="language-switcher" @click="switchLanguage">
         {{ currentLanguage }}
        </div>
           <!-- Обновленный контейнер баланса -->
          <div class="balance-container">
            <div class="balance-text">{{ t('balance') }}</div>
            <div class="button-balans">{{ formattedBalance }}</div>
          </div>
          <div 
            class="icon-button"
            @click="openPersonalAccount"
            :class="{ 'icon-button-clicked': isIconClicked }"
          >
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
   <transition name="slide-fade">
  <div 
    v-if="winNotificationVisible && winData" 
    class="win-notification"
    :class="{ expanded: expandedWinDetails }"
    @click="!expandedWinDetails ? expandedWinDetails = true : null"
  >
    <div class="notification-header">
      <div class="win-icon"></div>
      <div class="title">You Win</div>
      <div class="time">
        <!-- Используем computed свойство для времени -->
        {{ winData.timestamp ? formatTime(winData.timestamp) : '' }}
      </div>
    </div>
      
      <div class="notification-body">
        <div class="win-amount-label">{{ t('winning_amount') }}:</div>
        <div class="win-amount">{{ winData.amount }} ₽</div>
      </div>
      
      <div v-if="expandedWinDetails" class="bets-details">
        <div class="bet-item" v-for="(bet, index) in winData.bets" :key="index">
          <div class="bet-meta">
            <div class="bug-colors">
              <div 
                v-for="(color, i) in bet.bugColors" 
                :key="i"
                class="color-dot"
                :style="{ backgroundColor: color }"
              ></div>
            </div>
            <div class="bet-time">{{ bet.timestamp ? formatTime(bet.timestamp) : '' }}</div>
          </div>
          <div class="bet-amount">+{{ bet.winAmount }} ₽</div>
        </div>
      </div>
      
      <div v-if="!expandedWinDetails" class="hint">
        {{ t('press_for_more') }}
      </div>
      
      <button 
        v-if="expandedWinDetails" 
        class="close-button"
        @click.stop="winNotificationVisible = false; expandedWinDetails = false"
      >
        {{ t('close') }}
      </button>
    </div>
  </transition>
  <!-- В секции template после win-notification -->
<transition name="slide-fade">
  <div 
    v-if="loseNotificationVisible && loseData" 
    class="lose-notification"
    :class="{ expanded: expandedLoseDetails }"
    @click="!expandedLoseDetails ? expandedLoseDetails = true : null"
  >
    <div class="notification-header">
      <div class="lose-icon"></div>
      <div class="title">You Lose</div>
      <div class="time">
        {{ loseData.timestamp ? formatTime(loseData.timestamp) : '' }}
      </div>
    </div>
      
    <div class="notification-body">
      <div class="lose-amount-label">{{ t('losing_amount') }}:</div>
      <div class="lose-amount">-{{ loseData.amount }} ₽</div>
    </div>
      
    <div v-if="expandedLoseDetails" class="bets-details">
      <div class="bet-item" v-for="(bet, index) in loseData.bets" :key="index">
        <div class="bet-meta">
          <div class="bug-colors">
            <div 
              v-for="(color, i) in bet.bugColors" 
              :key="i"
              class="color-dot"
              :style="{ backgroundColor: color }"
            ></div>
          </div>
          <div class="bet-time">{{ bet.timestamp ? formatTime(bet.timestamp) : '' }}</div>
        </div>
        <div class="bet-amount">-{{ bet.loseAmount }} ₽</div>
      </div>
    </div>
      
    <div v-if="!expandedLoseDetails" class="hint">
      {{ t('press_for_more') }}
    </div>
      
    <button 
      v-if="expandedLoseDetails" 
      class="close-button"
      @click.stop="loseNotificationVisible = false; expandedLoseDetails = false"
    >
      {{ t('close') }}
    </button>
  </div>
</transition>
<transition name="slide-fade">
    <div 
      v-if="infoNotificationVisible" 
      class="info-notification"
    >
      <div class="notification-header">
        <div class="info-icon"></div>
        <div class="title">{{ t('information') }}</div>
      </div>
      
      <div class="notification-body">
        <div class="info-message">{{ infoMessage }}</div>
      </div>
    </div>
  </transition>
  <audio ref="balanceIncomeSound" src="/sounds/balanceIncome.mp3"></audio>
  <audio ref="raceStartSound" src="/sounds/raceIsStarted.mp3"></audio>
  <audio ref="countdownSound" src="/sounds/countdown.mp3"></audio>
 <audio ref="clearPendingBetsSound" src="/sounds/clearPendingBets.mp3"></audio>
<audio ref="cancelPendingBetSound" src="/sounds/cancelPendingBet.mp3"></audio>
 <audio ref="stakeActionClickSound" src="/sounds/stakeActionClick.mp3"></audio>
 <audio ref="betClickSound" src="/sounds/betClick.mp3"></audio>
 <audio ref="balanceOutcomeSound" src="/sounds/balanceOutcome.mp3"></audio>
</template>


<script setup>

import { ref, computed, onMounted, onUnmounted } from 'vue';
import HistoryBets from './HistoryBets.vue';
import io from 'socket.io-client';
import StavkiMenu from './StavkiMenu.vue';
import LastGameMenu from './LastGameMenu.vue'; // Добавьте эту строку
import PodiumResults from './PodiumResults.vue';
import i18n from './plugins/i18n.js' // Добавить эту строку
import { useI18n } from 'vue-i18n'
// Исправляем работу со звуком
const { t, locale } = useI18n();
const dizzySoundElement = ref(null);
const soundVolume = ref(0.5);
const balanceIncomeSound = ref(null);
const balanceOutcomeSound = ref(null); // Добавлено здесь
// Добавляем новое состояние для управления анимацией
const initialAnimationActive = ref(true);
// Добавьте новый импорт i18n
// Добавьте новый ref
const countdownSound = ref(null);

const lockedBugsArray = ref([]);

// Добавьте переменную для отслеживания состояния звука
const countdownPlayed = ref(false);

const currentLanguage = computed(() => locale.value.toUpperCase());
// В секции refs
const clearPendingBetsSound = ref(null);
const cancelPendingBetSound = ref(null);
const stakeActionClickSound = ref(null);



// Добавляем состояние для выбранных тараканов
const selectedBugIds = ref([]);
const screenWidth = ref(window.innerWidth);
// Функция определения размера экрана
const updateScreenSize = () => {
  screenWidth.value = window.innerWidth;
};

const switchLanguage = () => {
  const newLang = locale.value === 'en' ? 'ru' : 'en';
  
  // Обновляем локаль через Vue I18n
  locale.value = newLang;
  
  // Обновляем UI
  currentLanguage.value = newLang.toUpperCase();
  document.documentElement.lang = newLang;
  
  // Сохраняем выбор в URL
  const url = new URL(window.location);
  url.searchParams.set('lang', newLang);
  window.history.replaceState(null, '', url);
  
  // Принудительно обновляем компоненты
  key.value++; // Добавляем реактивность
}

// Добавляем реактивный ключ
const key = ref(0);
const userInteracted = ref(false); // Добавьте эту строку
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
const selectedTrap = ref(null);
const selectedBugs = ref([]);
const bugSelections = ref({}); // { trapId: [bugIds] }
const loseNotificationVisible = ref(false);
const expandedLoseDetails = ref(false);
const loseData = ref(null); // { amount: сумма проигрыша, bets: [массив проигрышных ставок] }
// Обработчик выбора ловушки
const selectTrap = (trapId) => {
  selectedTrap.value = trapId;
  selectedBugs.value = bugSelections.value[trapId] || [];
};
const betClickSound = ref(null);
// Добавьте эти состояния
const infoNotificationVisible = ref(false);
const infoMessage = ref('');
// Добавьте новый ref
const raceStartSound = ref(null);
const nextRaceBets = ref([]);
// Состояния для уведомлений
// Добавляем новый массив для ставок текущей гонки
const currentRaceBets = ref([]);
const notificationVisible = ref(false);
const notificationMessage = ref('');
const notificationClass = ref('');
const button1Ref = ref(null);
const button2Ref = ref(null);
const button3Ref = ref(null);
const lastGameMenuRef = ref(null);
const centerMenuRef = ref(null);
const historyBetsRef = ref(null);
const backgroundMusic = ref(null);
const isMusicPlaying = ref(false);
// Добавляем новое состояние для отслеживания заблокированных тараканов
const lockedBugs = ref(new Set());
// Функция для воспроизведения звука обратного отсчета
const playCountdownSound = () => {
  if (!userInteracted.value || !countdownSound.value) return;
  
  try {
    countdownSound.value.currentTime = 0;
    countdownSound.value.volume = soundVolume.value;
    countdownSound.value.play().catch(e => console.error("Countdown sound error:", e));
    countdownPlayed.value = true;
  } catch (e) {
    console.error("Countdown playback error:", e);
  }
};
// Создайте функцию для воспроизведения звука
const playRaceStartSound = () => {
  if (!userInteracted.value || !raceStartSound.value) return;
  
  try {
    raceStartSound.value.currentTime = 0;
    raceStartSound.value.volume = soundVolume.value;
    raceStartSound.value.play().catch(e => console.error("Race start sound error:", e));
  } catch (e) {
    console.error("Race start playback error:", e);
  }
};
  const playBetClick = () => {
  if (!userInteracted.value || !betClickSound.value) return;
  
  try {
    betClickSound.value.currentTime = 0;
    betClickSound.value.volume = soundVolume.value;
    betClickSound.value.play().catch(e => console.error("Bet click sound error:", e));
  } catch (e) {
    console.error("Bet click playback error:", e);
  }
};
const handleButtonClick = (btn) => {

  // Снимаем выделение со всех кнопок
  winButtons.value.forEach(button => {
    button.selected = false;
  });

  // Если меню уже открыто для этой кнопки - закрываем его
  if (centerWinMenuVisible.value && activeWinMenuId.value === btn.id) {
    closeWinMenu();
    centerWinMenuVisible.value = false;
    activeWinMenuId.value = null;
    selectedTrap.value = null;
    selectedBugs.value = [];
  } 
  // Если открыто другое меню - закрываем его и открываем новое
  else if (centerWinMenuVisible.value) {
    centerWinMenuVisible.value = false;
    setTimeout(() => {
      activeWinMenuId.value = btn.id;
      centerWinMenuVisible.value = true;
      selectTrap(btn.id);
      btn.selected = true; // Выделяем новую кнопку
    }, 50);
  }
  // Если меню закрыто - открываем его
  else {
    btn.selected = true; // Выделяем кнопку
    activeWinMenuId.value = btn.id;
    centerWinMenuVisible.value = true;
    selectTrap(btn.id);
  }
};

const startRaceCycle = () => {
  isPaused.value = false;
  breakInProgress.value = true;
  raceInProgress.value = false;
  phaseStartTime.value = Date.now();
};
// Показать уведомление
const showNotification = (message, isWin) => {
  notificationMessage.value = isWin 
    ? message.replace('{0}', `${amount} ${t('currency')}`)
    : message;
  notificationMessage.value = message;
  notificationClass.value = isWin ? 'win' : 'lose';
  notificationVisible.value = true;
  
  // Автоматическое скрытие через 3 секунды
  setTimeout(() => {
    notificationVisible.value = false;
  }, 3000);
};
// Обновленная функция для включения музыки
const enableMusic = async () => {
  try {
    if (!backgroundMusic.value) return;
    
    backgroundMusic.value.volume = soundVolume.value;
    await backgroundMusic.value.play();
    isMusicPlaying.value = true;
    
    document.removeEventListener('click', firstInteractionHandler);
    document.removeEventListener('touchstart', firstInteractionHandler);
  } catch (error) {
    console.error("Ошибка воспроизведения музыки:", error);
    showMusicEnableHint();
  }
};
// Обработчик первого взаимодействия
// Обновить firstInteractionHandler
const firstInteractionHandler = () => {
  try {
    userInteracted.value = true;
    
    // Убедиться, что все звуковые элементы доступны
    const soundEffects = [
      dizzySoundElement.value,
      balanceIncomeSound.value,
      balanceOutcomeSound.value,
      raceStartSound.value,
      countdownSound.value
    ];
    
    soundEffects.forEach(sound => {
      if (sound) {
        try {
          sound.volume = 0.001;
          sound.play().then(() => sound.pause()).catch(e => console.debug("Sound unlock error:", e));
          sound.currentTime = 0;
        } catch (e) {
          console.debug("Sound effect unlock error:", e);
        }
      }
    });
    
    enableMusic();
  } catch (e) {
    console.error("First interaction error:", e);
  }
};

// Показать подсказку для включения звука
const showMusicEnableHint = () => {
  infoMessage.value = t('enable_sound_hint');
  infoNotificationVisible.value = true;
  setTimeout(() => {
    infoNotificationVisible.value = false;
  }, 5000);
};
const playIncomeSound = () => {
  if (!userInteracted.value || !balanceIncomeSound.value) return;
  try {
    balanceIncomeSound.value.currentTime = 0;
    balanceIncomeSound.value.volume = soundVolume.value;
    balanceIncomeSound.value.play().catch(e => console.error("Income sound error:", e));
  } catch (e) {
    console.error("Income playback error:", e);
  }
};

// ДОБАВЬТЕ ЭТУ ФУНКЦИЮ
const playOutcomeSound = () => {
  if (!userInteracted.value || !balanceOutcomeSound.value) return;
  try {
    balanceOutcomeSound.value.currentTime = 0;
    balanceOutcomeSound.value.volume = soundVolume.value;
    balanceOutcomeSound.value.play();
  } catch (e) {
    console.error("Outcome sound error:", e);
  }
};
// Обработчик ставки на секцию
const placeSectionBet = () => {
  playBetClick(); // Добавляем звук
   // Блокируем выбранных тараканов
  const newLocked = [...lockedBugsArray.value, ...selectedBugs.value];
  lockedBugsArray.value = newLocked;

  if (!selectedTrap.value || selectedBugs.value.length === 0) {
    alert('Выберите тараканов для ставки');
    return;
  }

 // Проверка минимальной ставки
  const minBet = 25;
  if (currentBet.value < minBet) {
    alert(`Минимальная ставка: ${minBet}₽`);
    return;
  }

  // Проверка достаточности средств
  if (currentBet.value > balance.value) {
    alert('Недостаточно средств на балансе');
    return;
  }

  // Списание средств
  balance.value -= currentBet.value;
  showBetPlacedNotification();
  playOutcomeSound(); // Add this line

 // Создаем объект ставки
  const bet = {
    type: 'section',
    trapId: selectedTrap.value,
    bugs: [...selectedBugs.value],
    amount: currentBet.value,
    timestamp: new Date().toISOString(),
    result: 'pending'
  };

  // Всегда добавляем ставку в nextRaceBets
  nextRaceBets.value.push(bet);
  
  // Всегда показываем уведомление о ставке на следующую гонку
  showNotification(t('next_round_bet'), false);
  
  // Сбрасываем текущий выбор
  resetBugSelections();
  
  // Закрываем меню
  resetWinButtonSelection();
  centerWinMenuVisible.value = false;
  // Сохраняем информацию о ставке для отображения
  const button = winButtons.value.find(b => b.id === selectedTrap.value);
  if (button) {
    button.betAmount = currentBet.value;
  }
};
// Добавляем новую функцию для сброса выделения
const resetWinButtonSelection = () => {
  winButtons.value.forEach(button => {
    button.selected = false;
  });
  activeWinMenuId.value = null;
};

const checkBetsResults = () => {
  const winningBets = [];
  const losingBets = [];
  resetWinMenu(); // Добавлен вызов сброса меню
  let totalWin = 0;
  let totalLose = 0;

  const finishedBugs = bugs.value
    .filter(bug => bug.finished)
    .sort((a, b) => a.finishTime - b.finishTime)
    .map((bug, index) => ({
      id: bug.id,
      position: index + 1
    }));

  currentRaceBets.value.forEach(bet => {
    if (bet.result !== 'pending') return;
    
    // 1. Обработка ставки на ПОЗИЦИЮ
    if (bet.type === 'position') {
      const bugResult = finishedBugs.find(b => b.id === bet.bugId);
      
      if (bugResult && bugResult.position === bet.position) {
        const winAmount = bet.amount * 8; // Коэффициент 8:1
        totalWin += winAmount;
        balance.value += winAmount;
        playIncomeSound();
        winningBets.push({
          ...bet,
          winAmount,
          bugColors: [bugColors.value[bet.bugId-1]]
        });
        bet.result = 'win';
      } else {
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: [bugColors.value[bet.bugId-1]]
        });
        totalLose += bet.amount;
        bet.result = 'lose';
      }
    } 
    // 2. Обработка ставки на ОБГОН
    else if (bet.type === 'overtaking') {
      const overtakerResult = finishedBugs.find(b => b.id === bet.overtaker);
      const overtakenResult = finishedBugs.find(b => b.id === bet.overtaken);
      
      // Условие выигрыша: обгоняющий финишировал раньше
      const isWin = (overtakerResult && !overtakenResult) || 
                   (overtakerResult && overtakenResult && 
                    overtakerResult.position < overtakenResult.position);
      
      if (isWin) {
        const winAmount = bet.amount * 3; // Коэффициент 3:1
        totalWin += winAmount;
        balance.value += winAmount;
        playIncomeSound();
        winningBets.push({
          ...bet,
          winAmount,
          bugColors: [
            bugColors.value[bet.overtaker-1], 
            bugColors.value[bet.overtaken-1]
          ]
        });
        bet.result = 'win';
      } else {
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: [
            bugColors.value[bet.overtaker-1], 
            bugColors.value[bet.overtaken-1]
          ]
        });
        totalLose += bet.amount;
        bet.result = 'lose';
      }
    }
    // 3. Обработка ставки на СЕКЦИЮ (НОВЫЙ ТИП)
    else if (bet.type === 'section') {
      const button = winButtons.value.find(b => b.id === bet.trapId);
      
      if (!button) {
        // Кнопка не найдена - проигрыш
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: bet.bugs.map(id => bugColors.value[id-1])
        });
        totalLose += bet.amount;
        bet.result = 'lose';
        return;
      }

      // Проверяем, есть ли хотя бы один выбранный таракан в списке пришедших на кнопку
      const isWin = bet.bugs.some(bugId => button.bugs.includes(bugId));
      
      if (isWin) {
        const winAmount = bet.amount * 2; // Коэффициент 2:1
        totalWin += winAmount;
        balance.value += winAmount;
        playIncomeSound();
        winningBets.push({
          ...bet,
          winAmount,
          bugColors: bet.bugs.map(id => bugColors.value[id-1])
        });
        bet.result = 'win';
      } else {
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: bet.bugs.map(id => bugColors.value[id-1])
        });
        totalLose += bet.amount;
        bet.result = 'lose';
      }
    }
  });

  // Показ уведомления о выигрыше
  if (winningBets.length > 0) {
    winData.value = {
      amount: Math.floor(totalWin),
      bets: winningBets.map(bet => ({
        ...bet,
        winAmount: Math.floor(bet.winAmount)
      })),
      timestamp: new Date()
    };
    winNotificationVisible.value = true;
    
    setTimeout(() => {
      if (!expandedWinDetails.value) {
        winNotificationVisible.value = false;
      }
    }, 3000);
  }
  
  // Показ уведомления о проигрыше
  if (losingBets.length > 0) {
    loseData.value = {
      amount: Math.floor(totalLose),
      bets: losingBets.map(bet => ({
        ...bet,
        loseAmount: Math.floor(bet.loseAmount)
      })),
      timestamp: new Date()
    };
    loseNotificationVisible.value = true;
    
    setTimeout(() => {
      if (!expandedLoseDetails.value) {
        loseNotificationVisible.value = false;
      }
    }, 3000);
  }
  
  betHistory.value.push(...currentRaceBets.value);
  currentRaceBets.value = [];
};

// Обновленная функция resetBugSelections
const resetBugSelections = () => {
  selectedTrap.value = null;
  selectedBugs.value = [];
  bugSelections.value = {};
};


// Новое состояние для центрального меню
const centerWinMenuVisible = ref(false);
const activeWinMenuId = ref(null);
const historyBetsInsideCenter = ref(false);
const isGroup164Clicked = ref(false);
// Добавляем состояние для выбранного таракана-победителя
const selectedWinnerBugIds = ref([]);
// Состояния для управления ставками
const currentBet = ref(25);
const balance = ref(10000); // Начальный баланс
const betHistory = ref([]); // История ставок
const undoStack = ref([]); // Стек для отмены операций
const betHistoryStack = ref([]); // Добавляем новую переменную
const stavkiButtons = ref([
  { id: '25', amount: 25, src: '/images/buttons/25.png', alt: '25' },
  { id: '50', amount: 50, src: '/images/buttons/50.png', alt: '50' },
  { id: '100', amount: 100, src: '/images/buttons/100.png', alt: '100' },
  { id: '500', amount: 500, src: '/images/buttons/500.png', alt: '500' },
]);

// Добавление ставки
// Обновленная функция добавления ставки
const addToBet = (amount) => {
  playStakeActionClick();
  const maxAllowed = Math.min(balance.value, 10000);
  const newBet = Math.min(currentBet.value + amount, maxAllowed);
  
  if (newBet > currentBet.value) {
    undoStack.value.push({
      type: 'add',
      amount: newBet - currentBet.value,
      prevBet: currentBet.value
    });
    currentBet.value = newBet;
  }
};
const winMenuCenterRef = ref(null);
const buttonWinContainerRefs = ref({});

const setButtonWinContainerRef = (id, el) => {
  if (el) {
    buttonWinContainerRefs.value[id] = el;
  }
};
const playStakeActionClick = () => {
  if (!userInteracted.value || !stakeActionClickSound.value) return;
  
  try {
    stakeActionClickSound.value.currentTime = 0;
    stakeActionClickSound.value.volume = soundVolume.value;
    stakeActionClickSound.value.play().catch(e => console.error("Stake action sound error:", e));
  } catch (e) {
    console.error("Stake action playback error:", e);
  }
};
const handleDocumentClick = (event) => {
  if (centerWinMenuVisible.value && activeWinMenuId.value !== null) {
    const menuEl = winMenuCenterRef.value;
    const buttonContainerEl = buttonWinContainerRefs.value[activeWinMenuId.value];
    
    if (menuEl && !menuEl.contains(event.target) && 
        (!buttonContainerEl || !buttonContainerEl.contains(event.target))) {
      closeWinMenu();
    }
  }
// Закрытие LastGameMenu (Button-1)
  if (lastGameMenuVisible.value) {
    const menuEl = lastGameMenuRef.value?.$el || lastGameMenuRef.value;
    const buttonEl = button1Ref.value;
    
    if (menuEl && 
        !menuEl.contains(event.target) && 
        !buttonEl.contains(event.target)) {
      lastGameMenuVisible.value = false;
    }
  }

  // Закрытие CenterMenu (Button-2)
  if (centerMenuVisible.value) {
    const menuEl = centerMenuRef.value;
    const buttonEl = button2Ref.value;
    
    if (menuEl && 
        !menuEl.contains(event.target) && 
        !buttonEl.contains(event.target)) {
      centerMenuVisible.value = false;
    }
  }

  // Закрытие HistoryBets (Button-3)
  if (historyBetsVisible.value && !historyBetsInsideCenter.value) {
    const menuEl = historyBetsRef.value?.$el || historyBetsRef.value;
    const buttonEl = button3Ref.value;
    
    if (menuEl && 
        !menuEl.contains(event.target) && 
        !buttonEl.contains(event.target)) {
      historyBetsVisible.value = false;
    }
  }
};

const closeWinMenu = () => {
  centerWinMenuVisible.value = false;
  resetWinButtonSelection();
  selectedTrap.value = null;
  
  
};
// Отмена последней ставки
const undoLastBet = () => {
  if (cancelPendingBetSound.value && userInteracted.value) {
    cancelPendingBetSound.value.currentTime = 0;
    cancelPendingBetSound.value.volume = soundVolume.value;
    cancelPendingBetSound.value.play().catch(e => console.error("Cancel sound error:", e));
  }
  if (undoStack.value.length > 0) {
    const lastAction = undoStack.value.pop();
    currentBet.value = lastAction.prevBet;
  }
};
// Добавляем новое состояние для анимации
const isIconClicked = ref(false);

// Обновите эту функцию
const openPersonalAccount = () => {
  // Установите текст уведомления
  infoMessage.value = t('personal_account_coming_soon');
  infoNotificationVisible.value = true;
  
  // Автоматическое скрытие через 3 секунды
  setTimeout(() => {
    infoNotificationVisible.value = false;
  }, 3000);

  // Анимация нажатия
  isIconClicked.value = true;
  setTimeout(() => {
    isIconClicked.value = false;
  }, 300);
};
// Сброс всех ставок
const resetAllBets = () => {
  if (clearPendingBetsSound.value && userInteracted.value) {
    clearPendingBetsSound.value.currentTime = 0;
    clearPendingBetsSound.value.volume = soundVolume.value;
    clearPendingBetsSound.value.play().catch(e => console.error("Clear sound error:", e));
  }
  selectedWinnerBugIds.value = [];
  undoStack.value.push({
    type: 'reset',
    prevBet: currentBet.value
  });
  currentBet.value = 25;
};

// Удвоение ставки

const multiplyBet = () => {
  playStakeActionClick(); // Добавьте этот вызов
  if (currentBet.value > 0) {
    const maxAllowed = Math.min(balance.value, 10000);
    const newBet = Math.min(currentBet.value * 2, maxAllowed);
    
    if (newBet > currentBet.value) {
      undoStack.value.push({
        type: 'multiply',
        prevBet: currentBet.value
      });
      currentBet.value = newBet;
    }
  }
};

// Добавьте вычисляемое свойство
const formattedBalance = computed(() => {
  return `${Math.round(balance.value)} ${t('currency')}`;
});
// Функция для показа уведомления
const showBetPlacedNotification = () => {
  infoMessage.value = t('bet_placed');
  infoNotificationVisible.value = true;
  
  setTimeout(() => {
    infoNotificationVisible.value = false;
  }, 2000); // Автоматическое скрытие через 2 секунды
};
// Добавляем сброс при начале гонки
// Обновленный сброс при начале гонки
watch(raceInProgress, (newVal) => {
  if (newVal) {
    // Очищаем ТОЛЬКО ставки в матрице
    menuButtons.value.forEach(b => {
      b.selected = false;
      b.betAmount = 0;
    });
    
    // Сбрасываем выбор для overtaking
    overtakingSelection.value = { overtaker: null, overtaken: null };
    
    // НЕ сбрасываем текущую ставку!
  }
  
  // Сбрасываем ставки на ловушки
  winButtons.value.forEach(btn => {
    btn.betAmount = 0;
    btn.selected = false;
  });
});
const placeBet = () => {
  playBetClick(); // Добавляем звук
  // Блокируем выбранных тараканов
 if (activeTab.value === 'result') {
    const newLocked = [...lockedBugsArray.value];
    selectedButtons.forEach(button => {
      const row = Math.floor(button.id / 7);
      if (!newLocked.includes(row)) newLocked.push(row);
    });
    lockedBugsArray.value = newLocked;
  }
  // Блокировка для ставок на обгон
  else if (activeTab.value === 'overtaking') {
    const { overtaker, overtaken } = overtakingSelection.value;
    if (overtaker !== null) lockedBugs.value.add(overtaker);
    if (overtaken !== null) lockedBugs.value.add(overtaken);
  }

  if (currentBet.value <= 0) {
    infoMessage.value = t('enter_bet_amount');
    infoNotificationVisible.value = true;
    setTimeout(() => infoNotificationVisible.value = false, 3000);
    return;
  }

  let betsPlaced = false;
  const totalBetAmount = currentBet.value;
  
  // Ставки на конкретные места (вкладка Result)
  if (activeTab.value === 'result') {
    const selectedButtons = menuButtons.value.filter(btn => btn.selected);
    
    if (selectedButtons.length > 0) {
      // Проверка баланса
      if (totalBetAmount > balance.value) {
        infoMessage.value = t('insufficient_funds');
        infoNotificationVisible.value = true;
        setTimeout(() => infoNotificationVisible.value = false, 3000);
        return;
      }

      // Списание средств
      balance.value -= totalBetAmount;
      showBetPlacedNotification();
      playOutcomeSound(); // Add this line
      
      // Создаем ставки
      selectedButtons.forEach(button => {
        const row = Math.floor(button.id / 7); // 0-6 = bugId
        const col = button.id % 7;             // 0-6 = место (col + 1)
        const position = col + 1;
        
        const bet = {
          type: 'position',
          bugId: row,
          position: position,
          amount: totalBetAmount / selectedButtons.length,
          timestamp: new Date().toISOString(),
          result: 'pending'
        };
        
        betHistory.value.push(bet);
        nextRaceBets.value.push(bet);
      });
      
      betsPlaced = true;
      selectedButtons.forEach(button => {
      button.betAmount = totalBetAmount / selectedButtons.length;
    });
    }
     
  }
   // Ставки на обгон (вкладка Overtaking)
  else if (activeTab.value === 'overtaking') {
    const { overtaker, overtaken } = overtakingSelection.value;
    
    if (!overtaker || !overtaken) {
      infoMessage.value = t('select_two_bugs');
      infoNotificationVisible.value = true;
      setTimeout(() => infoNotificationVisible.value = false, 3000);
      return;
    }

    if (totalBetAmount > balance.value) {
      infoMessage.value = t('insufficient_funds');
      infoNotificationVisible.value = true;
      setTimeout(() => infoNotificationVisible.value = false, 3000);
      return;
    }

    balance.value -= totalBetAmount;
    
    const bet = {
      type: 'overtaking',
      overtaker: overtaker, // id обгоняющего таракана
      overtaken: overtaken, // id обгоняемого таракана
      amount: totalBetAmount,
      timestamp: new Date().toISOString(),
      result: 'pending'
    };

    betHistory.value.push(bet);
    nextRaceBets.value.push(bet);
    
    // Сброс выбора
    menuButtons.value.forEach(b => b.selected = false);
    overtakingSelection.value = { overtaker: null, overtaken: null };
    
    infoMessage.value = t('bet_placed_successfully');
    infoNotificationVisible.value = true;
    setTimeout(() => infoNotificationVisible.value = false, 3000);
    betsPlaced = true;
  }
  
  if (betsPlaced) {
    resetAllBets();
  } else {
    // Уведомление если не выбрана ставка
    infoMessage.value = t('select_bet_option');
    infoNotificationVisible.value = true;
    setTimeout(() => infoNotificationVisible.value = false, 3000);
  }
};
// Автоматическое обновление меню после гонки
const resetWinMenu = () => {
  centerWinMenuVisible.value = false;
  activeWinMenuId.value = null;
  selectedTrap.value = null;
  selectedBugs.value = [];
};
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
// Выносим из метода explodeAllBugs в область setup
// Обновленная функция для получения изображения цвета
const minBet = 25;
const maxBet = 10000;
const betStep = 1; // Шаг изменения ставки

const winNotificationVisible = ref(false);
const expandedWinDetails = ref(false);
const winData = ref(null); // { amount: сумма выигрыша, bets: [массив выигрышных ставок] }
const currentTime = ref('');
const getColorImage = (color, count) => {
  const basePath = '/images/colors';
  const colorMap = {
    '#FFFF00': 'yellow',
    '#FFA500': 'orange',
    '#FF0000': 'red',
    '#0000FF': 'blue',
    '#8B0000': 'dark-orange',
    '#800080': 'purple',
    '#00FF00': 'green'
  };
  
  const colorName = colorMap[color] || 'blue';
  
  switch(count) {
    case 1:
      return `${basePath}/Rectangle-${colorName}.png`;
    case 2:
      return `${basePath}/1к2/1к2-Rectangle-${colorName}.png`;
    case 3:
      return `${basePath}/1к3/1к3-Rectangle-${colorName}.png`;
    case 4:
      return `${basePath}/1к4/1к4-Rectangle-${colorName}.png`;
    default:
      return `${basePath}/Rectangle-${colorName}.png`;
  }
};
// Добавляем состояние для меню последней игры
const lastGameMenuVisible = ref(false);
const lastGames = ref([
  { id: 1, results: [{ position: 1, color: '#FF0000' }, { position: null, color: '#00FF00' }] },
  { id: 2, results: [{ position: 3, color: '#0000FF' }, { position: 2, color: '#FFFF00' }] }
]);


const setActiveTab = (tab) => {
  playStakeActionClick();
  // Сбрасываем выделение со всех кнопок
  menuButtons.value.forEach(btn => {
    btn.selected = false;
  });
  
  // Сбрасываем выбранных тараканов-победителей (только для вкладки Result)
  selectedWinnerBugIds.value = [];
  
  // Устанавливаем новую вкладку
  activeTab.value = tab;

  // Сброс выбора для ставок на обгон
  if (tab === 'overtaking') {
    overtakingSelection.value = { overtaker: null, overtaken: null };
  }
};
const explodeAllBugs = (raceEndTime) => {
  if (!isTabActive.value || bugs.value.length === 0) return;

  bugs.value.forEach(bug => {
    if (bug.finished) return;

    // Финишируем всех, кто уже начал движение к точке
    if (bug.phase === 'to_blue_point') {
      const button = winButtons.value.find(b => b.id === bug.targetButtonId);
      if (button) {
        bug.position = [...button.bluePoint];
        bug.finished = true;
        bug.finishTime = raceEndTime; // Важно: время окончания гонки
        
        button.occupied = true;
        button.finishedBugId = bug.id;
        
        if (button.bugs.length < 4) {
          button.bugs.push(bug.id);
        }
      }
    } else {
      // Для остальных запускаем анимацию взрыва
      bug.explodeFrame = 0;
      bug.exploded = false;
    }
  });

  startExplosionAnimation();
};
// Add this method after the selectTrap function
// В секции script
const toggleBugSelection = (bugId) => {
  if (lockedBugsArray.value.includes(bugId)) return;
  playStakeActionClick();
   // Проверяем, не заблокирован ли таракан
  if (lockedBugs.value.has(bugId)) return;
  if (!selectedTrap.value) return;
  
  const currentSelections = [...(bugSelections.value[selectedTrap.value] || [])];
  
  if (currentSelections.includes(bugId)) {
    // Remove selection
    bugSelections.value[selectedTrap.value] = currentSelections.filter(id => id !== bugId);
  } else {
    // Add selection (без ограничения количества)
    bugSelections.value[selectedTrap.value] = [...currentSelections, bugId];
  }
  
  // Update current selection
  selectedBugs.value = bugSelections.value[selectedTrap.value] || [];
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

function formatTime(date) {
  if (!date) return '';
  const d = typeof date === 'string' ? new Date(date) : date;
  const hours = d.getHours().toString().padStart(2, '0');
  const minutes = d.getMinutes().toString().padStart(2, '0');
  return `${hours}:${minutes}`;
}
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


// Обновляем текущее время каждую минуту
onMounted(() => {
  currentTime.value = formatTime(new Date());
  setInterval(() => {
    currentTime.value = formatTime(new Date());
  }, 60000);
});
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
const saveGameResults = async () => {
    // Собираем только финишировавших тараканов и сортируем по времени финиша
    const finishedBugs = bugs.value
        .filter(bug => bug.finished)
        .sort((a, b) => a.finishTime - b.finishTime);
    
    // Создаем массив результатов с позициями
    const results = bugs.value.map((bug) => {
        // Находим индекс финишировавшего таракана
        const finishIndex = finishedBugs.findIndex(b => b.id === bug.id);
        return {
            position: finishIndex !== -1 ? finishIndex + 1 : null,
            color: bugColors.value[bug.id]
        };
    });
    
    try {
        const response = await fetch('/api/game-history/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ results })
        });
        if (!response.ok) throw new Error('Save failed');
        loadGameHistory(); // Перезагружаем историю после сохранения
    } catch (error) {
        console.error('Error saving game results:', error);
    }
    

// Проверяем результаты ставок
  setTimeout(() => {
    checkBetsResults();
  }, 1000);
    // Проверяем результаты ставок
  checkBetsResults();
  
  // Сбрасываем выбор тараканов
  resetBugSelections();
};

// Добавляем состояние для анимации взрыва
const animationExplodeFrame = ref(null);
const explosionImages = ref(Array.from({length: 6}, (_, i) => `/images/bax/bax-${i+1}.png`));
// Загрузка истории
// В методе loadGameHistory
const loadGameHistory = async () => {
  try {
    const response = await fetch('/api/game-history/last');
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
    btn.bugs = [];
    btn.occupied = false;
    btn.finishedBugId = null;
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
          bug.phase = 'to_blue_point';
          bug.bluePointProgress = 0;
          bug.finishTime = now;
          
          // Добавляем таракана в кнопку (максимум 2)
          const button = winButtons.value.find(b => b.id === zone.buttonId);
          if (button && button.bugs.length < 4) {
            button.bugs.push(bug.id);
          }
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
    const speed = 200;

    // Рассчитываем шаг на основе времени
    const step = Math.min(distance, speed * (deltaTime / 1000));
    
    if (step > 0) {
      // Обновляем позицию
      bug.position[0] += (dx / distance) * step;
      bug.position[1] += (dy / distance) * step;
      
      // Обновляем угол только если расстояние > 1px
      if (distance > 1) {
        bug.angle = Math.atan2(dy, dx) - Math.PI / 2;
      }
    }

    if (distance <= 1) {
      bug.finished = true;
      button.occupied = true;
      button.finishedBugId = bug.id;
      bug.finishTime = now; // Сохраняем время финиша
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

// Обновленный обработчик клика
const clickBug = (index) => {
  const bugId = menuBugs.value[index].id;
  
  if (selectedBugIds.value.includes(bugId)) {
    // Удаляем если уже выбран
    selectedBugIds.value = selectedBugIds.value.filter(id => id !== bugId);
  } else {
    // Добавляем если не выбран
    selectedBugIds.value = [...selectedBugIds.value, bugId];
  }
  
  // Сбрасываем состояние клика через 300 мс
  setTimeout(() => {
    clickedBugIndex.value = null;
  }, 300);
  
  // Здесь можно добавить логику при клике на таракана
  console.log(`Clicked on bug ${menuBugs.value[index].id}`);
};

const betHistoryFromApi = ref([]);

const fetchBetHistory = async () => {
  try {
    const response = await fetch('/api/v1/gameplay/games/bets/cockroaches-space-maze/latest');
    if (!response.ok) throw new Error('Failed to fetch bet history');
    const data = await response.json();
    betHistoryFromApi.value = data.bets || [];
  } catch (error) {
    console.error('Error fetching bet history:', error);
    betHistoryFromApi.value = [];
  }
};
/*const loadBetHistory = async () => {
  try {
    const response = await fetch('/api/v1/gameplay/games/bets/latest', {
      headers: { Authorization: `Bearer ${authToken}` }
    });
    
    if (!response.ok) throw new Error('Failed to load bet history');
    
    bets.value = await response.json();
  } catch (error) {
    console.error('Error loading bet history:', error);
  }
};*/

// Вызывать при открытии окна
watch(historyBetsVisible, (visible) => {
  if (visible) {
    
  }
});

// Обработчик для кнопки истории ставок
// Обновите toggleHistoryBets:
const toggleHistoryBets = () => {
  if (centerMenuVisible.value) {
    if (lastGameMenuVisible.value) lastGameMenuVisible.value = false;
    historyBetsVisible.value = !historyBetsVisible.value;
    historyBetsInsideCenter.value = true;
    
    // Загружаем только при открытии
    if (historyBetsVisible.value) {
      fetchBetHistory();
    }
  } else {
    if (lastGameMenuVisible.value) lastGameMenuVisible.value = false;
    historyBetsVisible.value = !historyBetsVisible.value;
    
    // Загружаем только при открытии
    if (historyBetsVisible.value) {
      fetchBetHistory();
    }
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
  if (clearPendingBetsSound.value && userInteracted.value) {
    clearPendingBetsSound.value.currentTime = 0;
    clearPendingBetsSound.value.volume = soundVolume.value;
    clearPendingBetsSound.value.play().catch(e => console.error("Clear sound error:", e));
  }
  stopAction(); // Останавливаем любые активные интервалы
  restorePreviousBet();
  
  // Анимация
  const resetBtn = document.querySelector('.reset-button');
  if (resetBtn) {
    resetBtn.classList.add('reset-clicked');
    setTimeout(() => resetBtn.classList.remove('reset-clicked'), 300);
  }
  undoLastBet(); 
};

// Функция для удвоения ставки
// Обновим метод handleX2ButtonClick
const handleX2ButtonClick = () => {
  playStakeActionClick();
  if (currentBet.value > 0) {
    // Используем undoStack вместо betHistoryStack
    undoStack.value.push({
      type: 'multiply',
      prevBet: currentBet.value
    });
    
    // Удваиваем ставку
    currentBet.value = Math.min(currentBet.value * 2, 10000);
    
    // Анимация кнопки
    isX2Clicked.value = true;
    setTimeout(() => isX2Clicked.value = false, 300);
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


// Обновленные функции изменения ставки с сохранением состояния
const incrementBet = () => {
  playStakeActionClick();
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  const newBet = currentBet.value + betStep;
  currentBet.value = Math.min(newBet, maxBet, balance.value);
}

const decrementBet = () => {
  playStakeActionClick();
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  const newBet = currentBet.value - betStep;
  currentBet.value = Math.max(newBet, minBet);
}

// Функция для сброса ставки к нулю
const handleOtmenaButtonClick = () => {
  if (cancelPendingBetSound.value && userInteracted.value) {
    cancelPendingBetSound.value.currentTime = 0;
    cancelPendingBetSound.value.volume = soundVolume.value;
    cancelPendingBetSound.value.play().catch(e => console.error("Cancel sound error:", e));
  }
  stopAction(); // Останавливаем любые активные интервалы
  saveCurrentBet(); // Сохраняем текущее значение
 const currentBet = ref(25); // Было 0
  
  // Анимация
  const otmenaBtn = document.querySelector('.otmena-button');
  if (otmenaBtn) {
    otmenaBtn.classList.add('otmena-clicked');
    setTimeout(() => otmenaBtn.classList.remove('otmena-clicked'), 300);
  }
  resetAllBets(); 
};
// Новое состояние для выбранной пары тараканов (обгоняющий, обгоняемый)
const overtakingSelection = ref({ 
  overtaker: null, // id таракана, который обгоняет
  overtaken: null  // id таракана, которого обгоняют
});
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


const menuButtons = ref(
  Array.from({ length: 49 }, (_, index) => ({
    id: index,
    selected: false
  }))
);

// Обновленные кнопки победы
const winButtons = ref([
  { id: 7, bugs: [], hovered: false, top: 687, right: 4, bluePoint: [360, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 6, bugs: [], hovered: false, top: 687, right: 59, bluePoint: [305, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 5, bugs: [], hovered: false, top: 687, right: 114, bluePoint: [250, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 4, bugs: [], hovered: false, top: 687, right: 169, bluePoint: [195, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 3, bugs: [], hovered: false, top: 687, right: 224, bluePoint: [140, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 2, bugs: [], hovered: false, top: 687, right: 279, bluePoint: [85, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 1, bugs: [], hovered: false, top: 687, right: 334, bluePoint: [30, 687], menuVisible: false, menuTimer: null, selected: false },
]);
// ==============================
// ВЫЧИСЛЯЕМЫЕ СВОЙСТВА
// ==============================
const toggleMusic = () => {
  if (!backgroundMusic.value) return;
  
  if (isMusicPlaying.value) {
    backgroundMusic.value.pause();
  } else {
    enableMusic().catch(showMusicEnableHint);
  }
  
  isMusicPlaying.value = !isMusicPlaying.value;
};


// Регулировка громкости
const setMusicVolume = (volume) => {
  if (backgroundMusic.value) {
    backgroundMusic.value.volume = volume;
  }
};

const diagonalButtons = computed(() => {
  const diagonals = [];
  for (let i = 0; i < 7; i++) {
    diagonals.push(i * 8); // 0, 8, 16, 24, 32, 40, 48
  }
  return diagonals;
});
const toggleMenuButton = (btn) => {
  playStakeActionClick();
  if (activeTab.value === 'result') {
    const row = Math.floor(btn.id / 7); // ID таракана (0-6)
    const col = btn.id % 7;             // Место (0-6)

    // Проверяем, не заблокирован ли таракан
    if (lockedBugs.value.has(bugId)) return;
    
    // Проверка: если кнопка уже выбрана - снимаем выделение
    if (btn.selected) {
      btn.selected = false;
      return;
    }
    
    // Проверяем, не выбран ли уже другой таракан на это место
    const alreadySelected = menuButtons.value.some(otherBtn => 
      otherBtn.id % 7 === col && 
      otherBtn.selected && 
      otherBtn.id !== btn.id
    );
    
    if (alreadySelected) return; // Место уже занято другим тараканом
    
    // Снимаем предыдущий выбор для этого таракана (если был)
    menuButtons.value.forEach(otherBtn => {
      if (Math.floor(otherBtn.id / 7) === row && otherBtn.selected) {
        otherBtn.selected = false;
      }
    });
    
    // Выбираем текущую кнопку
    btn.selected = true;
  } else if (activeTab.value === 'overtaking') {
    const bugId = Math.floor(btn.id / 7); // Определяем ID таракана

    // Проверяем, не заблокирован ли таракан
    if (lockedBugs.value.has(bugId)) return;
    
    // Если кнопка уже выбрана - снимаем выделение
    if (btn.selected) {
      btn.selected = false;
      
      // Удаляем из выбора
      if (overtakingSelection.value.overtaker === bugId) {
        overtakingSelection.value.overtaker = null;
      } else if (overtakingSelection.value.overtaken === bugId) {
        overtakingSelection.value.overtaken = null;
      }
      return;
    }
    
    // Логика выбора
    if (overtakingSelection.value.overtaker === null) {
      // Выбор обгоняющего (первый таракан)
      overtakingSelection.value.overtaker = bugId;
      btn.selected = true;
    } else if (overtakingSelection.value.overtaken === null && bugId !== overtakingSelection.value.overtaker) {
      // Выбор обгоняемого (второй таракан, не совпадает с первым)
      overtakingSelection.value.overtaken = bugId;
      btn.selected = true;
    }
  }
};

// ==============================
// МЕТОДЫ УПРАВЛЕНИЯ ИНТЕРФЕЙСОМ
// ==============================
// Переключение кнопки меню


// Измененный метод для переключения центрального меню
// Разрешаем показ меню победы во время гонки
const toggleCenterMenu = () => {
  const wasOpen = centerMenuVisible.value;
  centerMenuVisible.value = !wasOpen;
  
  // Разрешаем показ меню победы при открытом нижнем меню
  if (!wasOpen && centerWinMenuVisible.value) {
    centerWinMenuVisible.value = false;
  }
};


// Убедимся что все методы определены
watch(centerMenuVisible, (newVal) => {
  if (newVal) {
    centerWinMenuVisible.value = false;
  }
});

// Добавьте вотчер для автоматического закрытия меню победы
watch(centerMenuVisible, (newVal) => {
  if (newVal) {
    centerWinMenuVisible.value = false;
  }
});

// Добавляем обработчик для кнопки Group 164
const handleGroup164Click = () => {
  playBetClick(); // Добавляем звук
  placeBet();
  console.log('Group 164 clicked');
  // Здесь будет логика подтверждения ставки
};

// Добавляем отсутствующую переменную для анимации x2
const isX2Clicked = ref(false);






// ==============================
// ЛОГИКА ИГРЫ
// ==============================
// Генерация путей для тараканов
const generatePaths = async () => {
  try {
    const response = await fetch('/api/generate-paths', {
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
// Сбрасываем блокировку тараканов
  lockedBugs.value = new Set();
countdownPlayed.value = false; // Сбрасываем флаг
playRaceStartSound(); // <-- ДОБАВИТЬ ЗДЕСЬ

// СОХРАНЯЕМ состояние выбора если меню открыто
  if (!centerWinMenuVisible.value) {
    resetBugSelections();
  }
  // ПЕРЕМЕЩАЕМ ставки на следующую игру в currentRaceBets
  if (nextRaceBets.value.length > 0) {
    currentRaceBets.value = [...nextRaceBets.value];
    nextRaceBets.value = [];
  }
  
  if (animationExplodeFrame.value) {
    cancelAnimationFrame(animationExplodeFrame.value);
    animationExplodeFrame.value = null;
  }
  // ПЕРЕМЕЩАЕМ ставки на следующую игру в активные ПЕРЕД началом новой гонки
  if (nextRaceBets.value.length > 0) {
    betHistory.value = [...betHistory.value, ...nextRaceBets.value];
    nextRaceBets.value = [];
  }
  explosionActive.value = false;
  bugs.value = [];
  try {
    
    isRaceStarted.value = false;
    isLoading.value = true;
    bugs.value = []; // Важно: очищаем предыдущих тараканов
    
   // ВМЕСТО ЭТОГО добавим очистку только в начале новой гонки
  

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
    const breakRemaining = breakInterval.value - breakElapsed;
   // Проверяем, осталась ли 1 секунда и звук еще не проигран
    if (breakRemaining <= 1750 && !countdownPlayed.value) {
      playCountdownSound();
    }
    progress.value = Math.min(100, (breakElapsed / breakInterval.value) * 100);
    isBettingPhase.value = true;

    if (breakElapsed >= breakInterval.value) {
      // Переход к фазе гонки
      breakInProgress.value = false;
      raceInProgress.value = true;
      phaseStartTime.value = now;
      handleGenerateClick(); // Добавленный вызов
    }
  } else if (raceInProgress.value && raceActualStartTime.value) {
    const raceElapsed = now - raceActualStartTime.value;
    const raceRemaining = raceInterval.value - raceElapsed;
    
    // Проверяем, осталась ли 1 секунда и звук еще не проигран
    if (raceRemaining <= 1750 && !countdownPlayed.value) {
      playCountdownSound();
    }
    // Прогресс гонки с учетом реального времени начала
    if (raceActualStartTime.value) {
      const raceElapsed = now - raceActualStartTime.value;
      progress.value = Math.min(100, (raceElapsed / raceInterval.value) * 100);
      
      

      if (raceElapsed >= raceInterval.value) {
        // Завершение гонки
        raceInProgress.value = false;
        const raceEndTime = raceActualStartTime.value + raceInterval.value;
        breakInProgress.value = true;
        phaseStartTime.value = now;
        raceActualStartTime.value = null;
        explodeAllBugs(raceEndTime);
        saveGameResults();
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
   // Сбрасываем флаг при смене фазы
  if (!breakInProgress.value && !raceInProgress.value) {
    countdownPlayed.value = false;
  }
  animationFrameId.value = requestAnimationFrame(updateProgress);
  
};

// Обновленная функция анимации взрыва
const startExplosionAnimation = () => {
  if (!isTabActive.value) return;
  if (explosionActive.value) return;
  
  explosionActive.value = true;
  const explosionStartTime = performance.now();
  
  const animateExplosion = (timestamp) => {
    const elapsed = timestamp - explosionStartTime;
    
    bugs.value.forEach(bug => {
      if (bug.explodeFrame !== undefined && !bug.finished && bug.phase !== 'to_blue_point') {
        const frame = Math.min(5, Math.floor(elapsed / 100));
        bug.explodeFrame = frame;
      }
    });
    
    if (elapsed < 600) {
      animationExplodeFrame.value = requestAnimationFrame(animateExplosion);
    } else {
      // Обработка после завершения анимации взрыва
      bugs.value.forEach(bug => {
        // Сбрасываем кадр анимации
        bug.explodeFrame = undefined;

        // Если таракан был взорван - помечаем его как взорванного и удаляем
        if (!bug.finished && bug.phase !== 'to_blue_point') {
          bug.exploded = true;
        } 
        // Если таракан успел начать движение к финишу до взрыва - перемещаем его
        else if (!bug.finished && bug.targetButtonId) {
          const button = winButtons.value.find(b => b.id === bug.targetButtonId);
          if (button) {
            bug.position = [...button.bluePoint];
            bug.finished = true;
            button.occupied = true;
            button.finishedBugId = bug.id;
            
            if (button.bugs.length < 2) {
              button.bugs.push(bug.id);
            }
          }
        }
      });
      
      
      
      explosionActive.value = false;
      animationExplodeFrame.value = null;
    }
  };
  
  animationExplodeFrame.value = requestAnimationFrame(animateExplosion);
};
const updateScale = () => {
  const baseWidth = 390;
  const baseHeight = 844;
  const widthRatio = window.innerWidth / baseWidth;
  const heightRatio = window.innerHeight / baseHeight;
  
  // Учитываем портретную и ландшафтную ориентацию
  const isPortrait = window.innerHeight > window.innerWidth;
  
  if (isPortrait) {
    // Для портретной ориентации
    scaleFactor.value = Math.min(widthRatio, heightRatio, 1);
  } else {
    // Для ландшафтной ориентации
    scaleFactor.value = Math.min(heightRatio, widthRatio * 0.8, 0.8);
  }
};
  

onMounted(() => {
  
  document.addEventListener('click', handleDocumentClick);
  document.addEventListener('click', handleDocumentClick);
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
// Отключаем анимацию через 4.5 секунды (3 цикла по 1.5 сек)
  setTimeout(() => {
    initialAnimationActive.value = false;
  }, 4500);
 if (backgroundMusic.value) {
    backgroundMusic.value.volume = 0.2;
    backgroundMusic.value.preload = "auto";
    
    // Добавляем обработчики для первого взаимодействия
    document.addEventListener('click', firstInteractionHandler);
    document.addEventListener('touchstart', firstInteractionHandler);
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleDocumentClick);
  document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  // Очищаем таймеры при уничтожении компонента
  clearInterval(cycleTimer);
  clearTimeout(raceTimer);
  window.removeEventListener('resize', updateScale);
 if (animationFrameId.value) {
    cancelAnimationFrame(animationFrameId.value);
  }
document.removeEventListener('click', firstInteractionHandler);
  document.removeEventListener('touchstart', firstInteractionHandler);
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

.icon-button-clicked {
  animation: icon-click 0.3s ease;
}

@keyframes icon-click {
  0% { transform: scale(1); }
  50% { transform: scale(0.9); }
  100% { transform: scale(1); }
}

/* Эффекты при наведении */
.icon-button:hover {
  background-color: #222 !important;
  box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
}

/* Существующие стили с небольшими улучшениями */
.icon-button {
  position: absolute;
  top: 7px;
  right: 10px;
  z-index: 3;
  width: 30px; 
  height: 30px;
  background-color: #000000;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.5);
  cursor: pointer;
  transition: all 0.3s ease;
  
}

.icon-1 {
  background-image: url('/images/icons/Group.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  width: 24px;
  height: 17px;
}
/* Обновленная анимация для серебристых линий */
@keyframes lineAnimationTop {
  0% {
    transform: translate(-100%, -100%) rotate(-26.1deg);
    opacity: 0;
  }
  15% {
    opacity: 1;
  }
  85% {
    opacity: 1;
  }
  100% {
    transform: translate(100%, 100%) rotate(-26.1deg);
    opacity: 0;
  }
}

@keyframes lineAnimationBottom {
  0% {
    transform: translate(100%, 100%) rotate(-26.1deg);
    opacity: 0;
  }
  15% {
    opacity: 1;
  }
  85% {
    opacity: 1;
  }
  100% {
    transform: translate(-100%, -100%) rotate(-26.1deg);
    opacity: 0;
  }
}

/* Класс для анимации */
.initial-animation {
  position: relative;
  overflow: hidden;
  border-radius: inherit;
}

.initial-animation::before,
.initial-animation::after {
  content: '';
  position: absolute;
  width: 200%;
  height: 4px;
  background: rgba(255, 255, 255, 0.7);
  transform: rotate(-26.1deg);
  transform-origin: center;
  z-index: 10;
  pointer-events: none;
  opacity: 0;
  top: 50%;
  left: 50%;
}

.initial-animation::before {
  animation: lineAnimationTop 0.6s ease-in-out 0s 3;
}

.initial-animation::after {
  animation: lineAnimationBottom 0.6s ease-in-out 0.6s 3; /* Задержка = длительность первой анимации */
}

/* Для кнопок победы */
.button-win-container.initial-animation::before,
.button-win-container.initial-animation::after {
  height: 6px;
}

/* Для маленьких кнопок */
.button-1.initial-animation::before,
.button-1.initial-animation::after,
.button-2.initial-animation::before,
.button-2.initial-animation::after,
.button-3.initial-animation::before,
.button-3.initial-animation::after {
  height: 3px;
}

/* Обновите стили для кнопок победы */
.button-win-container .button-win {
  cursor: pointer;
  pointer-events: auto;
  opacity: 1; /* Убедитесь что прозрачность нормальная */
}
.x2-button {
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
}
.x2-button:hover {
  transform: scale(1.05);
  filter: brightness(1.1) drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}
.x2-button:active {
  transform: scale(0.95);
}
.x2-clicked {
  animation: button-click 0.3s ease;
}

@keyframes button-click {
  0% { transform: scale(1.05); }
  50% { transform: scale(0.95); }
  100% { transform: scale(1.05); }
}
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

.notification {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 15px 30px;
  border-radius: 10px;
  background-color: rgba(0, 0, 0, 0.85);
  color: white;
  font-size: 18px;
  font-weight: bold;
  z-index: 1000;
  text-align: center;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
  animation: fadeInOut 3s ease-in-out;
}

.info-notification {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 372px;
  background: rgba(255, 255, 255, 0.9);
  
  border-radius: 10px;
  z-index: 10000;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  cursor: pointer;
}
/* Добавляем стили для заблокированных элементов */
.menu-button.locked {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.bug-button.locked {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}
.info-notification .notification-header {
  background: rgba(76, 175, 80, 0.2);
  padding: 10px 15px;
  display: flex;
  align-items: center;
  border-radius: 10px 10px 0 0;
}

.info-notification .info-icon {
  width: 20px;
  height: 20px;
  background: linear-gradient(180deg, #4CAF50 0%, #2E7D32 100%);
  border-radius: 4px;
  margin-right: 10px;
}

.info-notification .title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 15px;
  background: linear-gradient(180deg, #4CAF50 0%, #2E7D32 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-transform: uppercase;
  flex-grow: 1;
}

.info-notification .notification-body {
  padding: 15px;
}

.info-notification .info-message {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 500;
  font-size: 15px;
  color: #2E7D32;
  text-align: center;
}
.notification.win {
  
  background-color: rgba(76, 175, 80, 0.15);
}

.notification.lose {
  
  background-color: rgba(244, 67, 54, 0.15);
}

@keyframes fadeInOut {
  0%, 100% { opacity: 0; transform: translate(-50%, -40%); }
  10%, 90% { opacity: 1; transform: translate(-50%, -50%); }
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
    top: 15px;
    height: 20px;
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

.x2-button-container {
  position: absolute;
  width: 47px;
  height: 28px;
  left: 12%;
  top: 439%; /* Позиция под кнопками ставок */
  z-index: 9;
 
   
}
/* Стиль для активной вкладки */
.tab-button.active {
  box-shadow: 0 0 8px rgba(255, 255, 0, 0.8);
  filter: brightness(1.2);
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


/* Контейнер для меню с относительным позиционированием */


/* Контейнер для кнопок тараканов */
.bug-buttons-container {
  position: absolute;
  top: 36%;
  left: -17%;
  width: 130%;
  height: 60%;
  z-index: 10; /* Поверх изображения меню */
   
   
}
.win-menu-title {
  position: absolute;
  top: 32.5%; /* Позиционирование сверху */
  left: 0;
  right: 0;
  text-align: center;
  font-family: 'Hero', 'Bahnschrift', sans-serif; /* Основной шрифт + fallback */
  font-weight: 700; /* Bold */
  font-size: 15px;
  line-height: 1; /* 100% */
  color: #FFFFFF; /* Белый цвет текста */
  text-transform: uppercase; /* Все заглавные */
  z-index: 12; /* Поверх других элементов */
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.8); /* Тень для лучшей читаемости */
  padding: 0 20px; /* Защита от краев */
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
   
}

/* Эффекты при наведении */
.bug-button-hovered {
  transform: scale(1.1);
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




/* Добавляем новые стили для позиционирования */
/* Позиция внутри центрального меню history-bets (в главном меню)*/
.history-bets.inside-center {
  position: absolute;
  margin-top: 40%;
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
.lose-notification {
  position: fixed;
  top: 20px; /* Позиция сверху */
  left: 50%;
  transform: translateX(-50%); /* Центрирование по горизонтали */
  width: 372px;
  background: rgba(255, 220, 220, 0.9);
  
  border-radius: 10px;
  z-index: 10000;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.lose-notification.expanded {
  height: auto;
  max-height: 80vh;
  top: 50%;
  transform: translate(-50%, -50%);
}

.notification-header {
  background: rgba(255, 200, 200, 0.7);
}

.lose-icon {
  background: linear-gradient(180deg, #FF0000 0%, #8B0000 100%);
}

.title {
  background: linear-gradient(180deg, #FF0000 0%, #8B0000 100%);
}

.lose-amount {
  color: #FF0000;
}

.bet-amount {
  color: #FF0000;
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

.stavki-buttons-container {
  position: absolute;
  display: flex;
  flex-direction: row;
  justify-content: center;
   gap: var(--stavki-gap, 10px); /* Используем CSS-переменную для управления расстоянием */
  width: 60%;
  height: 30%;
  left: 3%;
  z-index: 9;
  padding: 0px;
  
  top: 404%;
}

.stavki-button {
  width: 50px;
  height: 26px;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  flex: none;
  order: 1;
  flex-grow: 0;
  margin-top: 0%;
}

.stavki-button:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.stavki-button:active {
  transform: scale(0.95);
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
  margin-left: 7.5%;
  
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




/* Стиль для скрытия диагональных кнопок */
 .menu-button.diagonal {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
    
    box-shadow: 0 0 8px rgba(255, 165, 0, 0.6) !important;
    display: block !important; /* Принудительно показываем */
    visibility: visible !important;
    pointer-events: auto !important;
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


.next-race-notice {
  position: absolute;
  top: -75px;
  left: 0;
  right: 0;
  text-align: center;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 20px;
  color: #FFD700;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
  z-index: 15;
  animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
  0% { opacity: 0.7; transform: scale(1); }
  100% { opacity: 1; transform: scale(1.05); }
}
.reset-button:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
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
.trap-bet-amount {
  position: absolute;
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 10px;
  font-weight: bold;
  color: yellow;
  text-shadow: 0 0 2px black;
}
@keyframes explosion-pulse {
  0% { transform: translate(-50%, -50%) scale(1); }
  100% { transform: translate(-50%, -50%) scale(1.1); }
}
/* Стили для новой кнопки Group 164 */
.group-164-button {
  position: absolute;
  margin-top: 84%;
  margin-left: 32%;
  z-index: 8; /* Выше других элементов меню */
  cursor: pointer;
  width: 112px; /* Размер по вашему изображению */
  height: 68px;
  
  /* Позиционируем кнопку поверх изображения stavki.png */
  top: 46%; /* Регулируйте по необходимости */
  left: 48.5%;
  transform: translateX(-50%);
  
  /* Анимация при наведении */
  transition: transform 0.3s ease, filter 0.3s ease;
}

.group-164-button:hover {
  transform: translateX(-50%) scale(1.0);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.group-164-button:active {
  transform: translateX(-50%) scale(0.95);
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
  /**/
  
}

.menu-button.selected {
  /* Изменяем цвет на желтый с помощью фильтра */
  filter: 
    brightness(1.2) 
    sepia(1) 
    saturate(5) 
    hue-rotate(5deg);
}

.group-164-button {
  transition: transform 0.3s ease, filter 0.3s ease;
}

.group-164-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}




@keyframes button-pulse {
  0% { transform: translateX(-50%) scale(1); }
  50% { transform: translateX(-50%) scale(0.95); }
  100% { transform: translateX(-50%) scale(1); }
}




.group-image {
  position: absolute;
  top: -165%;
  left: 0;
  width: 119px;
  height: 151px;
  z-index: 3;
}



.main-bg-container {
  transform-origin: top center;
  width: 100%;
  height: 100%;
  margin: 0 auto;
  position: relative;
}
.labirint-bg {
  aspect-ratio: unset;
  position: absolute;
  top: 8.5%; /* Точная подстройка позиции сверху */
  left: 0;
  width: 100%; /* Занимает всю ширину родителя */
  
  height: 689px; /* Автоматическая высота */
   /* Сохраняем соотношение сторон 390x689 */
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
   /* Для отладки */
}

/* Контейнер для элементов баланса */
.balance-container {
  position: relative;
  width: 100%;
  height: 100%; 
  gap: 10px; /* Расстояние между элементами */
  
}
.bet-amount-display {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 12px;
  font-weight: bold;
  color: white;
  text-shadow: 0 0 2px black;
}
/* Стиль для выбранного таракана */
.bug-button.selected {
  
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
  transform: scale(1.1);
  z-index: 15;
}

/* Усиленная анимация для выбранного состояния */
.bug-button.selected .bug-image {
  animation: selected-pulse 1s infinite alternate;
}

@keyframes selected-pulse {
  0% { transform: translate(-50%, -50%) scale(1); }
  100% { transform: translate(-50%, -50%) scale(1.15); }
}
.main-color {
  background-color: #1E031E;
  min-height: 100vh;
  position: relative; /* Для позиционирования */
}

.tarakan {
  position: absolute; /* Вместо relative */
  width: 30px;
  height: 38px;
  transform: translate(-50%, -50%); /* Центрирование */
  z-index: 3;
  transition: left 0.1s linear, top 0.1s linear, filter 0.3s ease; /* Плавное движение */ /* Полупрозрачный фон */
    
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
  
  box-shadow: 0 0 8px rgba(255, 165, 0, 0.6);
}
.main-bg {
  background-image: url('/images/background/Frame 560.png');
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
  /* Фиксированные размеры контейнера */
  width: 390px;
  min-height: 844px;
  height: 100%;
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
  
}
.menu-button {
  visibility: hidden;
  pointer-events: none;
}
.button-text {
  font-family: 'Hero', 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 14px;
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
  font-size: 20px; /* Чуть больше для центральной кнопки */
  padding: 0px 15px;
}

.button-3 .button-text {
  padding: 0 10px;
  text-align: center;
}
.menu-button.visible {
  visibility: visible;
  pointer-events: auto;
}
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(-50%) translateY(-100px); /* Движение сверху вниз */
  opacity: 0;
}

/* Базовые стили уведомления */
.win-notification {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 372px;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 10px;
  z-index: 10000;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.win-notification.expanded {
  height: auto;
  max-height: 80vh;
  top: 50%;
  transform: translate(-50%, -50%);
}

.notification-header {
  position: relative;
  background: rgba(255, 255, 255, 0.6);
  padding: 10px 15px;
  display: flex;
  align-items: center;
  border-radius: 10px 10px 0 0;
}

.win-icon {
  width: 20px;
  height: 20px;
  background: linear-gradient(180deg, #3C42E3 0%, #FF4300 100%);
  border-radius: 4px;
  margin-right: 10px;
}
/* Добавляем стили для подсказки */
.music-hint {
  position: absolute;
  top: -5px;
  right: -5px;
  width: 15px;
  height: 15px;
  background: #FF0000;
  color: white;
  border-radius: 50%;
  font-size: 12px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.music-control {
  position: absolute;
  top: 7px;
  right: 315px;
  z-index: 10;
  width: 30px;
  height: 30px;
  background-color: #000000;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.music-icon {
  width: 20px;
  height: 20px;
  background-image: url('/images/icons/music-on.png');
  background-size: contain;
  background-repeat: no-repeat;
}

.music-icon.music-off {
  background-image: url('/images/icons/music-off.png');
}

.music-control:hover {
  transform: scale(1.05);
  filter: brightness(1.2);
}
.title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 15px;
  background: linear-gradient(180deg, #3C42E3 0%, #FF4300 100%);
  
  /* Кросс-браузерная поддержка */
  background-clip: text; /* Стандартное свойство */
  -webkit-background-clip: text; /* Для WebKit/Blink браузеров */
  color: transparent; /* Для Firefox и других браузеров */
  -webkit-text-fill-color: transparent; /* Для WebKit/Blink */
  
  text-transform: uppercase;
  flex-grow: 1;
}

.time {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 300;
  font-size: 15px;
  color: #000;
}

.notification-body {
  display: flex;
  padding: 15px;
  align-items: center;
}

.win-amount-label {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 500;
  font-size: 15px;
  color: #000;
  text-transform: uppercase;
  margin-right: 10px;
}

.win-amount {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 18px;
  color: #000;
}

.bets-details {
  padding: 10px 15px;
  max-height: 300px;
  overflow-y: auto;
}

.bet-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.bet-meta {
  display: flex;
  align-items: center;
}

.bug-colors {
  display: flex;
  margin-right: 15px;
}

.color-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-right: 4px;
}

.bet-time {
  font-family: 'Bahnschrift', sans-serif;
  font-size: 12px;
  color: #666;
}

.bet-amount {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 16px;
  color: #28a745;
}

.hint {
  padding: 10px 15px;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 500;
  font-size: 13px;
  color: #000;
  opacity: 0.7;
  text-align: center;
}

.close-button {
  display: block;
  width: 100%;
  padding: 12px;
  background: linear-gradient(180deg, #7F00FE 0%, #66008F 98.9%);
  color: white;
  
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  text-transform: uppercase;
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
  
  
}

.menu-image {
  width: 100%;
  max-width: 390px; /* Максимальная ширина основного изображения */
  height: auto;
  object-fit: contain;
  
}
#Button-1 .button-text {
  white-space: pre-line;
  line-height: 1.2;
  display: flex;
  flex-direction: column;
  justify-content: center;
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

.menu-image {
  max-width: 80vw;
  max-height: 80vh;
  object-fit: contain;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
  transition: opacity 0.2s ease;
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

.color-indicators {
  position: absolute;
  width: 100%;
  height: 100%;
  z-index: 4;
  display: flex; /* Убираем flex-контейнер */
  pointer-events: none; /* Разрешает клики сквозь индикатор */

}

.color-indicator {
  position: absolute;
  top: 0;
  height: 100%;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  transition: all 0.3s ease;
}


/* Специальные классы для позиционирования */
.color-indicator.first-half {
  left: 0;
}

.color-indicator.second-half {
  left: 50%;
}
/* Стиль для вкладки Result */
.result-tab {
  background: rgba(0, 0, 0, 0.4);
  margin-right: 1px;
}
.button-1,.button-2,.button-3 {
 margin-top: 4%;
 
  
}
.button-1 {
  margin-left: 1%;
}
/* Стиль для вкладки Overtaking */
.overtaking-tab {
  background: linear-gradient(180deg, rgba(127, 0, 254, 0.7) 0%, rgba(102, 0, 143, 0.7) 98.9%);
  
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
/* ////////////////////////////////////////медиа@media (max-width: 768px)////////////////////////////////////////*/

@media (max-width: 768px) {

  .bug-buttons-container {
    top: 40%;
  }
.button-1,.button-2,.button-3 {
  margin-top: 4%;
  
}
    .bet-counter-container {
    top: 29%;
    width: 24%;
    height: 30%;
     margin-top: 98%; /* Позиционирование по вертикали */
    left: 37%;
  }
  
  
  .x2-button-container {
  position: absolute;
  width: 47px;
  height: 28px;
  left: 12%;
  top: 442%; /* Позиция под кнопками ставок */
  z-index: 9;
 
   
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
  .group-164-button {
    width: 113px;
    top: 43%;
    left: 48.5%;
    
    
  }

  .reset-button {
    width: 48px;
    height: 24px;
    top: 488%;
    left: 0%;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
     
  }

  .otmena-button {
    width: 25px;
    height: 25px;
    left: 0%;
  }

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
  .bug-image {
    width: 70%;
  }


  .win-menu-center {
    width: 300px;
    height: 180px;
  }

  .win-menu-center .menu-stavki,
  .bottom-menu {
    transform: translateX(-50%) scale(0.9);
    margin-bottom: 15px;
    left: 62.5%;
  }
  
}
/* ////////////////////////////////////////медиа@media (max-width: 768px)////////////////////////////////////////*/
  
/* ////////////////////////////////////////медиа@media (max-width: 480px)////////////////////////////////////////*/
@media (max-width: 480px) {
  .win-menu-center {
    width: 95%;
    max-width: 320px;
  
}

.podium-container, .group-imagener {
  
  width: 100%;
  height: 200%;
  margin-left: 10%;
  top: 10%;
  border-radius: 8px;
}

  
  
  .center-menu {
    bottom: calc(350%); /* Дополнительная корректировка */
    margin-top: 0%;
    left: 50%;
    
    transform: translateX(-50%) scale(0.9); /* Еще меньше */
  }
  /* Стили для диагональных кнопок в режиме Result */
.menu-button.diagonal {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
  
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
   .bet-counter-container {
    top: 27%;
    width: 20%;
    height: 32%;
     margin-top: 98%; /* Позиционирование по вертикали */
    left: 37%;
  }
  .group-image {
    margin-top: 9%;
    left: -32%;
    max-width: 240px;
    transform: translateX(-50%) scale(0.8);
  }
  .bet-display{
    height: 75%;
    width: 300%;
    margin-top: -3%;
  }
  .bet-button {
    /* Адаптация кнопок +/- */
    width: 100%;
    height: 85%;
    transform: scale(1.1);
    margin-top: 0%;
  }
  .main-bg {
    height: 843px;
    width: 100%;
    max-width: 390px;
    min-height: 100%;
    background-size: cover;
  }
  
  .labirint-bg {
    z-index: 4;
    
  }
  
 
  
  .button-win {
   z-index: 1;
  }
  
  
  .overtaking-tab {
    border-radius: 40px 0 0 40px; /* Уменьшаем скругление */
  }

  .result-tab {
    border-radius: 0 40px 40px 0; /* Уменьшаем скругление */
  }
 
  
  .menu-image, .stavki-image {
    max-width: 100%;
    transform: scale(0.9);
    margin-left: 0;
  }
  
  
  
  .menu-tabs {
    width: 90%;
    height: 20px; /* Уменьшаем высоту контейнера */
    top: 25px; /* Сдвигаем вверх для компенсации */
    left: 5%;
    
  }

  .tab-button {
    font-size: 10px; /* Уменьшаем размер шрифта */
     /* Корректируем межстрочный интервал */
    padding: 2px 0; /* Уменьшаем внутренние отступы */
  }
  
    /* Обновленные стили контейнера с учетом диагональных кнопок */
  .menu-buttons-container {
    top: 90%;
    left: 54.75%;
    transform: translateX(-50%);
    width: auto;
    height: auto;
    --button-width: 36px;
    --button-height: 30px;
    --column-gap: 5.5px;
    --row-gap: 6.75px;
    display: grid;
    grid-template-columns: repeat(7, var(--button-width));
    grid-template-rows: repeat(7, var(--button-height));
    column-gap: var(--column-gap);
    row-gap: var(--row-gap);
    padding: 0 5px;
    box-sizing: border-box;
  }
   .group-image {
    top: -170%;
    left: -35%;
    transform: scale(0.7);
  }
  /* Уменьшаем размер кнопок */
  .menu-button {
    background-size: contain;
    width: var(--button-width);
    height: var(--button-height);
  }
  
   .x2-button-container {
    left: 14%;
    top: 440%;
    height: 24%;
   /* */
  }
  .bet-button {
    /* Адаптация кнопок +/- */
    width: 100%;
    height: 85%;
    
  }
  
  .stavki-button {
    width: 45px;
    height: 28px;
  }
  .otmena-button{
    top: 487%;
    left: 2%;
  }
  .reset-button {
   top: 488%;
   width: 11%;
   height: 25%;
   left: -2%;
  }
  
  .x2-button {
    left: 20%;
    width: 100% ;
    margin-top: 0%;
    height: 100%;
  }
  
  .group-164-button {
    width: 26%;
    height: 64%;
    top: 48%;
    left: 43%;
  }
  .bet-button.minus {
  margin-right: 0px; /* Отступ справа для минуса */
}

.bet-button.plus {
  margin-left: 0px; /* Отступ слева для плюса */
}
  
  
  
  
  .history-bets {
    margin-top: 15%;
    margin-left: -2%;
  }
  .history-bets.inside-center {
  position: absolute;
  top: -225%;
  left:-3%;
  width: 240px;
  height: 150px;
  z-index: 10;
  transform: none !important;
}

  .stavki-buttons-container {
    top: 405%;
    width: 59%;
    left: 3%;
    gap: 8px;
    
  }
  
   .menu-buttons-container {
    top: 90%;
    left: 53%;
    --column-gap: 6px;
    --row-gap: 7px;
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
  .menuWin-image-center {
    transform: scale(0.9);
  }
  .bug-buttons-container {
    transform: scale(0.9) translateY(20%) translateX(0%);
    
  }
  
}
/* ////////////////////////////////////////медиа@media (max-width: 480px)////////////////////////////////////////*/

/* ////////////////////////////////////////медиа@media (max-width: 390px)////////////////////////////////////////*/
/* Добавляем общие адаптивные стили */
@media (max-width: 390px) {
  .main-bg {
    width: 100vw;
    max-width: 100%;
    min-height: 100vh;
    background-size: cover;
    overflow-x: hidden;
  }
  
  .panel-layer {
    height: 11%;
    top: 89%;
    
    
  }
  .panel-up {
    top: -163%;
  }
  .button-1, .button-2, .button-3 {
    position: relative;
    transform: scale(0.95);
    margin: 0;
    
  }
  
  .button-1 {
    left: -1%;
    top: 21%;
    width: 26%;
  }
  
  .button-2 {
    top: -35%;
    left: 26%;
    width: 48%;
  }
  
  .button-3 {
    
    top: -87%;
    left: 75%;
    width: 25%;
  }
  .labirint-bg{
    display: none;
    top: 8.6%;
  }
  .button-win-container {
    transform: scale(0.95);
    margin-top: -0.1%;
  }
  
  .button-win-1 { right: -4px; }
  .button-win-2 { right: 50px; }
  .button-win-3 { right: 105px; }
  .button-win-4 { right: 160px; }
  .button-win-5 { right: 215px; }
  .button-win-6 { right: 270px; }
  .button-win-7 { right: 325px; }
  
  .center-menu {
    transform: translateX(-50%) scale(0.8);
    bottom: 420%;
  }
  
  .menu-buttons-container {
    transform: scale(0.85);
    top: 210%;
    left: 52%;
  }
  
  .bet-counter-container {
    left: 35%;
    top: 480%;
  }
  
  .stavki-buttons-container {
    top: 390%;
    width: 95%;
  }
  
  .x2-button-container {
    top: 430%;
  }
  
  .group-164-button {
    width: 100px;
    top: 45%;
  }
  
  .tarakan {
    transform: translate(-50%, -50%) scale(0.8);
  }
  
  .history-bets.inside-center {
    top: -180%;
    transform: scale(0.85);
  }
}
/* .......................375 ......................................*//* .......................375 ......................................*/
@media (max-width: 375px) {
  .button-1, .button-2, .button-3 {
    transform: scale(0.85);
  }
  
  .button-2 {
    transform: translateX(-0%) scale(1);
    left: 25.5%;
  }
  
  .button-text {
    font-size: 12px;
  }
  
  .menu-buttons-container {
    --button-width: 36px;
    --button-height: 30px;
    --column-gap: 5.5px;
    --row-gap: 6.75px;
    top: 90%;
    left: 54.75%;
  }
  
  .bet-counter-container {
    left: 35%;
    margin-top: 105%;
  }
  
  .bet-display {
    height: 75%;
    width: 300%;
    margin-top: -3%;
  }
  
  .bet-button {
    width: 100%;
    height: 85%;
  }
  
  .stavki-button {
    width: 40px;
    height: 24px;
  }
  
  .otmena-button {
    top: 487%;
    left: 2%;
  }
  
  .reset-button {
    top: 488%;
    width: 12%;
    height: 25%;
    left: -3%;
  }
  
  .x2-button {
    left: 20%;
    width: 100%;
    height: 100%;
  }
  .button-win-container {
    margin-right: 13%;
  }
}

/* Оптимизация для русского языка */
html[lang="ru"] .bth-1-text,
html[lang="ru"] .bth-2-text {
  font-size: 1px;
  white-space: nowrap;
}
/* .......................375 ......................................*//* .......................375 ......................................*/

/* ...................../..360 ......................................*//* ...................../..360 ......................................*/
@media (max-width: 360px) {
  /* Основные изменения для фона */
  .main-bg {
    
    background-size: 100% 100%; /* Гарантируем полное покрытие по ширине */
    background-position: top center; /* Фиксируем позицию сверху */
    min-height: 100vh; /* Занимаем всю высоту экрана */
    max-height: none; /* Снимаем ограничение по высоте */
    transform: none; /* Убираем трансформацию */
    margin: 0 auto; /* Центрируем */
    padding-top: 20px; /* Добавляем отступ сверху */
    overflow: hidden; /* Скрываем выходящие за границы элементы */
  }
  .menuWin-image-center {
    transform: scale(0.8) ;
  }
 
 
  
  .bug-button-hovered {
  transform: scale(1.15);
  position: relative;

  filter: 
    drop-shadow(0 0 8px rgba(255, 255, 0, 0.8))
    drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
  z-index: 12; /* Поднимаем над остальными при наведении */
}
  /* Уменьшаем горизонтальные отступы между тараканами */
  .bug-button:nth-child(1) { left: 3% !important; }
  .bug-button:nth-child(2) { left: 14% !important; }
  .bug-button:nth-child(3) { left: 25% !important; }
  .bug-button:nth-child(4) { left: 36% !important; }
  .bug-button:nth-child(5) { left: 47% !important; }
  .bug-button:nth-child(6) { left: 58% !important; }
  .bug-button:nth-child(7) { left: 68% !important; }
  /* Уменьшаем общий масштаб интерфейса */
  .main-bg-container {
    transform: scale(0.92);
    transform-origin: top center;
  }
  
  /* Корректируем позицию лабиринта */
  .labirint-bg {
    top: 9%;
    height: 660px;
  }
 
  /* Адаптируем панели */
  .panel-up {
    top: -165%;
    width: 100%;
    left: 0%;
  }
  
  .panel-layer {
    top: 89%;
    height: 12%;
  }
  
  /* Корректируем кнопки управления */
  .button-1 {
    
    left: 0%;
    top: 20%;
    width: 27%;
  }
  
  .button-2 {
    
    left: 28%;
    top: -25%;
    width: 44%;
  }




/* Специальные настройки для 360px */
@media (max-width: 360px) {
  
  /* Уменьшение размера текста для лучшей читаемости */
  .button-text {
    font-size: 11px;
    transition: font-size 0.1s ease;
  }
  
  .button-2 .button-text {
    font-size: 13px;
  }
  

/* Для кнопки 2 оставляем только hover-эффект без изменения размера */
.button-2:hover {
  filter: 
    brightness(1.1) 
    drop-shadow(6 6 8px rgba(178, 56, 58, 0.8));
}


  .button-2:active .button-text {
    font-size: 12.5px;
  }
}
  
  .button-3 {
    
    left: 72%;
    top: -75%;
    width: 28%;
    height: 50%;
  }
 
  /* Кнопки победы */
  .button-win-container {
    transform: scale(0.9);
    margin-top: -1%;
    margin-right: -11%;
    height: 8.2%;
    width: 14.5%;
    
  }
 
  
  .button-win-1 { right: 39px; }
  .button-win-2 { right: 86px; }
  .button-win-3 { right: 134px; }
  .button-win-4 { right: 183px; }
  .button-win-5 { right: 231px; }
  .button-win-6 { right: 280px; }
  .button-win-7 { right: 330px; }
  
  /* Центральное меню */
  .center-menu {
    transform: translateX(-50%) scale(0.8);
    bottom: 470%;
  }
  
  /* Уменьшаем элементы управления */
  .bet-counter-container {
    margin-top: 105%;
    
  }
   /*.main-bg {
    z-index: 90;
  background-image: url('/images/test/maze-359.png');
   }*/
  .bet-display {
    font-size: 15px;
  
    
  }
  .win-menu-center {
    top: 71%;
    
  }
 
  .menu-container {
    top: 10%;
    
  }
  .bug-button {
    transform: scale(0.8);
    margin-top: 1%;
    margin-left: 2%;
    
  }
   .bug-buttons-container {
   margin-top: 4%;
    margin-left: 10%;
    height: 57%;
  }
  .button-win-container {
    z-index: 10 !important; /* Повышаем z-index */
    pointer-events: auto !important; /* Гарантируем кликабельность */
  }
  
  .button-win {
    z-index: 11 !important; /* Дополнительное повышение */
    pointer-events: auto !important; /* Разрешаем взаимодействие */
  }
  
  /* Дополнительно: убедимся что другие элементы не перекрывают кнопки */
  .panel-layer {
    z-index: 5; /* Ниже кнопок */
  }
  
  .tarakan, .explosion {
    z-index: 3; /* Ниже кнопок */
  }
  .stavki-buttons-container {
    gap: 6px;
  }
  
  .stavki-button {
    width: 40px;
  }
  .language-switcher {
  left: 1%;
}
.menu-buttons-container{
left: 12%;
top: 55%;
width: 85%;
--column-gap: 5.5px; /* Горизонтальное расстояние */
--row-gap: 7.5px;   /* Вертикальное расстояние (можно увеличивать отдельно) */
   
}
.group-164-button {
  top: 37%;
  width: 26%;
  left: 43.5%;
  height: 54%;
}
.stavki-buttons-container {
  top: 323%;
  left: 5.5%;
  gap: 5px;
  width: 55%;
  height: 25%;
}
.stavki-button {
  width: 22%;
  height: 80%;
}
.x2-button-container {
  
  top: 347%;
  transform: scale(0.8);
  left: 13%;
}
.otmena-button {
  top: 386%;
  left: 2%;
  width: 6%;
  height: 19%;
  
}
}

/* Для русского языка делаем шрифты немного меньше */
html[lang="ru"] .bth-1-text,
html[lang="ru"] .bth-2-text {
  font-size: 11px;
}
/* //////////////////////////// 390-400 ///////////////////////// */
/* Адаптация лабиринта для 390-400px */
@media (min-width: 390px) and (max-width: 399px) {
  .labirint-bg {
    height: 689px;
    top: 8.8%; /* Точная подстройка позиции */
    width: 100%; /* Занимает всю доступную ширину */
    z-index: 4;
    max-width: 100%; /* Не превышает ширину экрана */
    left: 0; /* Выравнивание по левому краю */
    transform: none; /* Сбрасываем трансформации */
    background-position: center top; /* Центрируем фон */
  }
  
  .main-bg {
    z-index: 1;
    
    margin-top: -20%;
    height: 800px;
    width: 100%; /* Ширина на весь экран */
    max-width: 100%; /* Не больше ширины экрана */
    background-size: cover; /* Фон покрывает всю область */
    overflow-x: hidden; /* Скрываем горизонтальный скролл */
  }
  .button-win {
    z-index: 20;
    
    pointer-events: none; /* Клики проходят сквозь эти элементы */
  }
 
  /* Корректировка позиций кнопок победы */
  .button-win-1 { right: 5px; }
  .button-win-2 { right: 59px; }
  .button-win-3 { right: 113px; }
  .button-win-4 { right: 167px; }
  .button-win-5 { right: 221px; }
  .button-win-6 { right: 275px; }
  .button-win-7 { right: 330px; }
  .button-win-1,.button-win-2 ,.button-win-3 ,.button-win-4 ,.button-win-5 ,.button-win-6 ,.button-win-7 {
    margin-top: 6%;
    transform: scaleX(1);
    cursor: pointer;
    pointer-events: none;
    
    
  }
  .button-win-container {
    
    
    cursor: pointer;
    pointer-events: none;
    top: 81%;
    width: 13%;
    
    
  }
 .button-win {
  cursor: default !important;
  pointer-events: none;
  
  z-index: 7;
 }
  /* Дополнительные корректировки */
  .panel-layer,
  .panel-up {
    z-index: 5;
    
    width: 100%; /* Ширина под экран */
    box-sizing: border-box; /* Учитываем padding в ширине */
  }
  .panel-up {
    top: 46%;
    left: 0.1%;
    
  }
  .panel-layer {
    top: 92%;
    height: 8%;
    
  }
  .button-3 {
    top: 16%;
    
    left: 74%;
  }
    .button-2 {
      top: 17%;
      left: 28%;
      width: 44%;
    
    }
  .button-1 {
    top: 17%;
  }
  .button-1,.button-2,.button-3 {
    top: 35%;
  }
  .history-bets {
    margin-left: -1%;
    transform: scaleX(0.9);
    
  }
  .last-game-menu {
    transform: scaleX(0.85);
  }
}

/* //////////////////////////// 400-420 ///////////////////////// */

@media (min-width: 400px) and (max-width: 405px) {
  /* Центральное меню */
  .button-1, .button-2, .button-3 {
    top: 17%;
    
  }
  .reset-button {
    transform: scale(1);
    top: 485%;
    left: -2%;
  }
  .button-2 {
    left: 28%;
  }
  .button-3 {
    left: 74%;
  }
  .labirint-bg {
    top: 7.5%;
    max-width: 100%;
    margin: 0 auto;
    z-index: 1;
  }
  .button-win-container {
    width: 14%;
    top: 82%;
  }
  .main-bg {
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
  width: 100%;
  min-height: 844px;
  height: 100%;
  max-height: 844px;
  position: relative;
  overflow: hidden;
  
  margin: 0 auto;
  z-index: 1;
}

.button-win-1,.button-win-2,.button-win-3,.button-win-4,.button-win-5,.button-win-6,.button-win-7 {
   
    margin-top: -2.9%;
    transform: translateX(8%) scaleX(95%)
    
  }
  .button-win {
    z-index: 10;
  }
}

/*  стили для языка */
/* Добавим адаптивные стили для русского языка */
html[lang="ru"] .bth-1-text,
html[lang="ru"] .bth-3-text {
  font-size: 14px;
  letter-spacing: -0.3px;
  white-space: nowrap;
  padding: 0 5px;
  
}

html[lang="ru"] .bth-2-text {
  font-size: 15px;
  letter-spacing: -0.5px;
  padding: 0 8px;
}
/* стили для языка*/
@media (min-width: 400px) and (max-width: 403px) {
  .button-1, 
  .button-2, 
  .button-3 
  {
    margin-top: -1%;
  }
}

@media (min-width: 389px) and (max-width: 391px) {
  .main-bg {
    z-index: 1;
  }
  .labirint-bg {
    z-index: 1;
    top: 8%;
  }
.button-win-container {
  top: 79.7%;
  height: 8%;
  width: 13.1%;
  
}
.group-164-button {
  transform: scale(1);
  top: 64%;
  height: 100%;
  left: 30%;
}
.otmena-button{
  top: 692%;
}
.x2-button-container {
  height: 35%;
  width: 11%;
  top: 630%;
}
.bet-counter-container {
  top: 40%;
  height: 42%;
}
.reset-button {
  top: 692%;
  height: 35%;
}
.stavki-buttons-container {
top: 575%;
height: 50%;
left: 6%;
gap: 3%;
width:55%;
}
.menu-buttons-container{
  width: 85%;
  
  transform: scale(0.9);
  left: 12%;
  top: 105%;
   --column-gap: 8.5px; /* Горизонтальное расстояние */
  --row-gap: 9px;
}
.button-win-1 { right: 5px; }
.button-win-2 { right: 57px; }
.button-win-3 { right: 111px; }
.button-win-4 { right: 164px; }
.button-win-5 { right: 217px; }
.button-win-6 { right: 270px; }
.button-win-7 { right: 322px; }
}
.button-1,.button-2,.button-3 {
  top: 6%;
  
}
.button-1 {
  left: 1.2%;
}
.language-switcher {
  left: 1%;
}
.history-bets.inside-center{
top: -330%;
}
@media (min-width: 359px) and (max-width: 361px) {
.button-1 {
top: 23%;
left: 2%;
width: 23%;

}
.button-1,.button-2,.button-3 {
  transform: scale(1) scaleY(1.1);
  
}
.reset-button {
  top: 385%;
  transform: scale(0.85);
  width: 13%;
  height: 21%;
}

.button-2 {
top: -26%;
left: 28%;
width: 43%;


}
.button-3 {
  top: -73%;
left: 74%;
width: 23%;

}
.history-bets.inside-center {
  top: -284%;
  
}
.bet-counter-container {
  top: -3%;
  transform: scale(0.85);
  width: 25%;
  left: 24%;
  height: 28%;
}
.bug-buttons-container {
  top: 32.5%;
  transform: scale(1);
}



}
@media (min-width: 392px) and (max-width: 394px) {
  .main-color {
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
    
  }
  
  .history-bets.inside-center {
    top: -480%;
  }
  .main-bg {
   background-position: center top;
   left: 2%;
  }
  .button-win-container {
  top: 80.3%;
  height: 8%;
  width: 13.1%;
  z-index: 4;
  
  .button-win {
        pointer-events: auto; /* Разрешаем события */
    }
}
.button-win {
  z-index: 10;
}
.bug-buttons-container {
  z-index: 1;
}

.button-win-1 { right: 5px; }
.button-win-2 { right: 58px; }
.button-win-3 { right: 111px; }
.button-win-4 { right: 164px; }
.button-win-5 { right: 217px; }
.button-win-6 { right: 270px; }
.button-win-7 { right: 324px; }
.button-1, .button-2,.button-3 {
  top: -5%;
}
.button-1 {
  left: 1%;
  width: 23%;
  top: -6%;
}
.win-menu-title {
  top: 38%;
}
.button-3 {
  width: 23%;
  left: 74.2%;
}
.group-164-button {
  
  top: 64%;
  height: 100%;
  left: 43%;
}

.labirint-bg {
  top: 8.2%;
  z-index: 5;
  pointer-events: none;
}

.otmena-button{
  top: 680%;
}
.menu-buttons-container{
  width: 85%;
  
  transform: scale(0.9);
  left: 12%;
  top: 109%;
   --column-gap: 8.9px; /* Горизонтальное расстояние */
  --row-gap: 9px;
}
.x2-button-container {
  height: 35%;
  width: 11%;
  top: 620%;
}
.reset-button {
  top: 681%;
  height: 35%;
}
.bet-counter-container {
  top: 40%;
  height: 42%;
}
.stavki-buttons-container {
top: 567%;

left: 5.5%;
gap: 3%;
width:55%;
}

.bet-counter-container {
margin-top: 97.5%;
left: 36.5%;
height: 44%;
width: 20%;
}
.bet-display {
margin-top: -5%;
font-size: 13px;
height: 70%;
}
.bet-button {
  margin-top: -4%;
}
}

</style>