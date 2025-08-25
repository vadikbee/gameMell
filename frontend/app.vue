<template>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <div class="main-color">
    <div class="scroll-container">
    <!-- История ставок в основном интерфейсе -->
    <audio ref="backgroundMusic" src="/sounds/backgroundMusic.mp3" loop></audio>
    <!-- Контейнер для масштабирования фона -->
    <div class="game-wrapper">
  

    
 <div class="game-container" :style="{ transform: currentHeight >= 690 ? `scale(${scaleFactor})` : 'none' }">
    <!-- Основной игровой контейнер -->
    <div class="main-bg">
        <!-- Верхняя панель баланса -->
        <div class="panel-up">
    <!-- Левая группа: язык и звук -->
    <div class="left-group">
      <div class="language-switcher" @click="switchLanguage">
        {{ currentLanguage }}
      </div>
      <div class="music-control" @click="toggleMusic">
        <div class="music-icon" :class="{ 'music-off': !isMusicPlaying }"></div>
        <div v-if="!isMusicPlaying" class="music-hint">!</div>
      </div>
    </div>

    <!-- Правая группа: баланс и иконка (порядок изменен) -->
    <div class="right-group">
      <div class="balance-container">
        <div class="button-balans">{{ formattedBalance }}</div>
        <div class="balance-text">{{ t('balance') }}</div>
      </div>
      <div class="icon-button" @click="openPersonalAccount"
           :class="{ 'icon-button-clicked': isIconClicked }">
        <div class="icon-1"></div>
      </div>
    </div>
  </div>
<!-- Прогресс-бар выносим за пределы panel-up -->
<div class="progress-container">
  <div class="progress-bar">
    <div class="progress-fill" :style="{ width: progress + '%' }"></div>
  </div>
  <div v-if="breakInProgress" class="make-bets-label">
    {{ makeBetsText }}
  </div>
</div>
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
     <div class="menu-images-container" style="transform: translateY(-13%); width: 100%; height: 100%; position: relative;">
        <StavkiMenu custom-class="center-stavki"
      :currentBet="currentBet"
      :minBet="betConfig.minBet"
      :maxBet="betConfig.maxBet"
      :betStep="betConfig.betStep"
      :currency="betConfig.currency"
      context="win"
       :showNextRaceNotice="raceInProgress"
      :nextRaceNoticeText="t('next_round_bet')"
      @win-bet-click="placeSectionBet"
      @otmena-click="resetCurrentBet"
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
      <div class="menu-image-wrapper">
      <div class="win-menu-title">
        {{ t('who_reaches_section') }}
      </div>
       
      <img 
        :src="`/images/menus/Frame 575-${activeWinMenuId}.png`" 
        alt="Menu" 
        class="menuWin-image-center"
      />
      
      <!-- Обновленный контейнер для тараканов с обработчиком выбора -->
     <!-- В секции template -->
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
      
      
    </div>
  </div>\
</div>
      
      <!-- Фон лабиринта -->
      <div class="labirint-bg"></div>
      <div class="win-buttons-container">
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
        >
        <div class="win-button-text">
    <div class="coefficient-x">x</div>
    <div class="button-coefficient">{{ btn.coefficient }}</div>
  </div>
</div>
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
  <!-- Новый контейнер для кнопок -->
  <div class="buttons-container">
    <div 
      ref="button1Ref"
      class="button-1" 
      id="Button-1" 
      :class="{ 'initial-animation': initialAnimationActive }"
      @click="!centerWinMenuVisible && toggleLastGameMenu()"
    >
      <span class="button-text bth-1-text">{{ t('last_games') }}</span>
    </div>
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
      @click="toggleCenterMenu"
    >
      <span class="button-text bth-2-text">{{ t('make_a_bet') }}</span>
    </div>
    
    <div 
      ref="button3Ref"
      class="button-3" 
      id="Button-3" 
      :class="{ 'initial-animation': initialAnimationActive }"
      @click="toggleHistoryBets"
    >
      <span class="button-text">{{ t('history_bets') }}</span>
    </div>
  </div>
       
        <!-- Центральное меню -->
        <div v-if="centerMenuVisible" class="center-menu" ref="centerMenuRef">
        <!-- Изменение здесь: динамический src -->
        <img 
          :src="activeTab === 'overtaking' ? '/images/menus/center-buttom.png' : '/images/menus/center-buttom-result.png'" 
          alt="Center Menu" 
          
        >
          <div class="menu-tabs">
          <!-- Вкладка Overtaking -->
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
  <!-- Обновленный блок menu-buttons-container -->
<div class="menu-buttons-container">
<div 
  v-for="btn in (activeTab === 'result' ? resultButtons : overtakingButtons)" 
  :key="btn.id"
  class="menu-button"
  :class="{
    'selected': btn.selected,
    'confirmed': btn.confirmed,
    'text-button': activeTab === 'result' || activeTab === 'overtaking',
    'button-visible': isButtonVisible(btn),
    'has-bet': btn.betAmount > 0,
    'diagonal': activeTab === 'overtaking' && diagonalButtons.includes(btn.id),
    'locked': activeTab === 'overtaking' && diagonalButtons.includes(btn.id)
  }"
  @click="toggleMenuButton(btn)"
>
  <!-- Удалено старое отображение -->
  <!-- Новое расположение элементов -->
 <div v-if="btn.confirmed" class="confirmed-content">
  <div class="confirmed-coefficient">2.23</div>
  <div class="confirmed-bet-amount">
    {{ btn.betAmount }}₽
  </div>
</div>
  
  <div v-else>
    
    <div 
      v-if="(activeTab === 'result' || activeTab === 'overtaking') && isButtonVisible(btn) && !btn.betAmount" 
      class="coefficient-container"
    >
      <div class="coefficient-bg"></div>
      <div class="coefficient-text">2.23</div>
    </div>
  </div>
</div>
</div>

         <PodiumResults 
          v-if="lastGames.length > 0" 
          :games="lastGames" 
          class="group-image"
        />
<HistoryBets 
  v-if="historyBetsVisible && centerMenuVisible" 
  ref="historyBetsRef"
  :isCenterMenuOpen="centerMenuVisible" 
  :insideCenter="true"
  :title="t('history_bets')"
  :bets="betHistoryFromApi"
  class="inside-center"
/>

          
          <!-- Меню ставок - ИЗМЕНЕНО: убрали класс center-stavki -->
      <!-- В template app.vue, внутри center-menu -->
<div class="stavki-container-in-center">
  <StavkiMenu
    :currentBet="currentBet"
    :minBet="betConfig.minBet"
    :maxBet="betConfig.maxBet"
    :betStep="betConfig.betStep"
    :currency="betConfig.currency"
    context="default"
    :showNextRaceNotice="raceInProgress"
    @otmena-click="resetCurrentBet"
    @reset-click="undoLastBet"
    @decrement-start="startDecrement"
    @increment-start="startIncrement"
    @stop-action="stopAction"
    @add-bet="addToBet"
    @x2-click="multiplyBet"
    :playBetClick="playBetClick"
    @group164-click="placeBet"
    custom-class="center-stavki"
  />
</div>
          

        </div>
        
      </div>
</div>
</div>
      
    </div>
  </div>
</div>
  <transition-group name="slide-fade" tag="div" class="notifications-container">
  <WinLoseNotification
    v-for="notification in winNotifications"
    :key="notification.id"
    :bets="notification.bets"
    :timestamp="notification.timestamp"
    @close="closeWinNotification(notification.id)"
  />
</transition-group>
  <!-- Уведомления о выигрыше/проигрыше -->
    <transition name="slide-fade">
      <WinLoseNotification
        v-if="winNotification.visible"
        type="win"
        :amount="winNotification.amount"
        :bets="winNotification.bets"
        :timestamp="winNotification.timestamp"
        @close="closeWinNotification"
      />
    </transition>
    
    <transition name="slide-fade">
      <WinLoseNotification
        v-if="loseNotification.visible"
        type="lose"
        :amount="loseNotification.amount"
        :bets="loseNotification.bets"
        :timestamp="loseNotification.timestamp"
        @close="closeLoseNotification"
      />
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
 <audio ref="dizzySound" src="/sounds/star.mp3"></audio>
 <audio ref="explosionSound" src="/sounds/bax.mp3"></audio>
<!-- Конфетти эффект -->
 <Confetti 
        v-if="showConfetti" 
        @complete="showConfetti = false"
        :containerWidth="mainBgWidth"
        :containerHeight="mainBgHeight"
        :containerLeft="mainBgLeft"
        :containerTop="mainBgTop"
      />
</template>


<script setup>
import Confetti from './Confetti.vue';
import { ref, computed, onMounted, onUnmounted, reactive  } from 'vue';
import HistoryBets from './HistoryBets.vue';
import io from 'socket.io-client';
import StavkiMenu from './StavkiMenu.vue';
import LastGameMenu from './LastGameMenu.vue'; // Добавьте эту строку
import PodiumResults from './PodiumResults.vue';
import i18n from './plugins/i18n.js' // Добавить эту строку
import { useI18n } from 'vue-i18n'
import WinLoseNotification from './WinLoseNotification.vue';

const showConfetti = ref(false);
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
const lastGameMenuVisible = ref(false);
const countdownSound = ref(null);
const explosionSound = ref(null);
const lockedBugsArray = ref([]);

// Добавьте переменную для отслеживания состояния звука
const countdownPlayed = ref(false);

const mainBgWidth = ref(0);
const mainBgHeight = ref(0);
const mainBgLeft = ref(0);
const mainBgTop = ref(0);

const betConfig = ref({
  minBet: 25,
  maxBet: 10000,
  betStep: 1,
  currency: 'RUB'
});
const currentLanguage = computed(() => locale.value.toUpperCase());
// В секции refs
const clearPendingBetsSound = ref(null);
const cancelPendingBetSound = ref(null);
const stakeActionClickSound = ref(null);

// Добавим функцию unlockAllBugs, если её нет
const unlockAllBugs = () => {
  lockedBugs.value = new Set();
  lockedBugsArray.value = []; // Очищаем массив заблокированных
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
const soundEnabled = ref(true); // Добавляем общий флаг для управления звуком
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
// Добавляем состояние для хранения выделений по строкам
const overtakingSelection = ref({}); // { row: Set(columns) }
const overtakingSelections = ref({}); // { row: Set(columns) }
const currentOvertakerRow = ref(null); // Текущая выбранная строка
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
// Добавляем эти функции
const winNotifications = ref([]);
const notificationCounter = ref(0);

const startDecrement = () => startAction(decrementBet);
const startIncrement = () => startAction(incrementBet);

const decrementBet = () => {
  playStakeActionClick();
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  const newBet = currentBet.value - betStep;
  currentBet.value = Math.max(newBet, minBet);
}

const incrementBet = () => {
  playStakeActionClick();
  if (!actionInterval.value && !actionTimeout.value) {
    saveCurrentBet();
  }
  const newBet = currentBet.value + betStep;
  currentBet.value = Math.min(newBet, maxBet, balance.value);
}

const saveCurrentBet = () => {
  undoStack.value.push({
    type: 'change',
    prevBet: currentBet.value
  });
}
watch(lastGameMenuVisible, (visible) => {
  if (visible) loadGameHistory();
});
// Добавляем новое состояние для отслеживания заблокированных тараканов
const lockedBugs = ref(new Set());
// Функция для воспроизведения звука обратного отсчета
const playCountdownSound = () => {
  if (!userInteracted.value || !betClickSound.value || !soundEnabled.value) return;
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
  if (!userInteracted.value || !betClickSound.value || !soundEnabled.value) return;
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
    if (!userInteracted.value || !betClickSound.value || !soundEnabled.value) return;
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
  if (!soundEnabled.value) return; // Проверяем общий флаг звука
  
  try {
    if (!backgroundMusic.value) return;
    backgroundMusic.value.volume = soundVolume.value;
    await backgroundMusic.value.play();
    isMusicPlaying.value = true;
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
  if (hasShownSoundHint.value) return;
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
  if (!userInteracted.value || !betClickSound.value || !soundEnabled.value) return;
  if (!userInteracted.value || !balanceOutcomeSound.value) return;
  try {
    balanceOutcomeSound.value.currentTime = 0;
    balanceOutcomeSound.value.volume = soundVolume.value;
    balanceOutcomeSound.value.play();
  } catch (e) {
    console.error("Outcome sound error:", e);
  }
};

const placeSectionBet = async () => {
  try {
    playBetClick();
    
    // Преобразуем ID тараканов из формата интерфейса (1-7) в формат гонки (0-6)
    const raceBugIds = selectedBugs.value.map(id => id - 1);
    
    console.log(`Создаем ставку на секцию ${selectedTrap.value} с тараканами: ${selectedBugs.value.join(', ')} (в формате гонки: ${raceBugIds.join(', ')})`);
    
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
    if (currentBet.value * selectedBugs.value.length > balance.value) {
      alert('Недостаточно средств на балансе');
      return;
    }

    // Создаем ОДНУ ставку для всех выбранных тараканов
    const totalAmount = currentBet.value * selectedBugs.value.length;
    
    // Списание общей суммы
    balance.value -= totalAmount;
    showBetPlacedNotification();
    playOutcomeSound();

    // Добавляем одну ставку в массив (используем raceBugIds для формата гонки)
    nextRaceBets.value.push({
      type: 'section',
      trapId: selectedTrap.value,
      bugIds: raceBugIds, // Используем преобразованные ID
      amount: totalAmount
    });

    // Обновляем отображение суммы ставки на кнопке
    const button = winButtons.value.find(b => b.id === selectedTrap.value);
    if (button) {
      button.betAmount = (button.betAmount || 0) + totalAmount;
    }

    // Отправка на бэкенд
    const response = await fetch('/api/save-bet', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: 1,
        amount: totalAmount,
        type: 'trap',
        selection: selectedBugs.value, // Оригинальные ID для бэкенда
        trapId: selectedTrap.value,
        color: 'linear-gradient(180deg, #FF170F 0%, #FF005E 100%)',
        time: new Date().toTimeString().split(' ')[0]
      })
    });
    
    if (!response.ok) throw new Error('Failed to save section bet');
    
    fetchBetHistory();
  } catch (error) {
    console.error('Error saving section bet:', error);
  } finally {
    resetBugSelections();
    resetWinButtonSelection();
    centerWinMenuVisible.value = false;
  }
};
// Добавляем новую функцию для сброса выделения
const resetWinButtonSelection = () => {
  winButtons.value.forEach(button => {
    button.selected = false;
  });
  activeWinMenuId.value = null;
};
let winNotificationTimer = null;
let loseNotificationTimer = null;
const checkBetsResults = () => {
  // Собираем только финишировавших тараканов и сортируем по времени финиша
  const finishedBugs = bugs.value
    .filter(bug => bug.finished)
    .sort((a, b) => a.finishTime - b.finishTime)
    .map((bug, index) => ({
      ...bug,
      position: index + 1 // Присваиваем позицию по порядку финиша
    }));

  // Логируем порядок финиша тараканов
  console.log("Порядок финиша тараканов:");
  finishedBugs.forEach(bug => {
    console.log(`${bug.position} место: Таракан ${bug.id + 1} (${getBugName(bug.id)})`);
  });

  // Создаем объект для быстрого доступа к позициям тараканов
  const bugPositions = {};
  finishedBugs.forEach(bug => {
    bugPositions[bug.id] = bug.position;
  });

  const winningBets = [];
  const losingBets = [];
  let totalWin = 0;
  let totalLose = 0;
  
   currentRaceBets.value.forEach(bet => {
    if (bet.type === 'overtaking') {
        const overtakerResult = finishedBugs.find(b => b.id === bet.overtaker);
        const overtakenResult = finishedBugs.find(b => b.id === bet.overtaken);
        
        // Исправленное условие выигрыша
        const isWin = overtakerResult && 
               (!overtakenResult || overtakerResult.position < overtakenResult.position);

        if (isWin) {
            // Выигрыш
            const winAmount = bet.amount * 2;
            totalWin += winAmount;
            balance.value += winAmount;
            playIncomeSound();
            
            winningBets.push({
  ...bet,
  winAmount,
  bugColors: [
    bugColors.value[bet.overtaker],
    bugColors.value[bet.overtaken]
  ]
});
            
            bet.result = 'win';
        } else {
            // Проигрыш
            losingBets.push({
                ...bet,
                loseAmount: bet.amount,
                bugColors: [
                    bugColors.value[bet.overtaker],
                    bugColors.value[bet.overtaken]
                ]
            });
            
            totalLose += bet.amount;
            bet.result = 'lose';
        }
    }

// Добавляем обработку для ставок типа "position" (result)
    else if (bet.type === 'position') {
      const bugId = bet.bugId - 1; // Преобразуем ID из формата интерфейса (1-7) в формат гонки (0-6)
      const finishedPosition = bugPositions[bugId];

      console.log(`Проверяем ставку: таракан ${bet.bugId} на ${bet.position} место. Фактическая позиция: ${finishedPosition || 'не финишировал'}`);

      // Таракан не финишировал - ставка проиграла
      if (finishedPosition === undefined) {
        totalLose += bet.amount;
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: [bugColors.value[bugId]]
        });
        bet.result = 'lose';
      } 
      // Таракан финишировал на указанном месте - ставка выиграла
      else if (finishedPosition === bet.position) {
        const winAmount = bet.amount * 2.23; // Коэффициент 2.23
        totalWin += winAmount;
        balance.value += winAmount;
        playIncomeSound();
        
        winningBets.push({
          ...bet,
          winAmount: winAmount,
          bugColors: [bugColors.value[bugId]]
        });
        bet.result = 'win';
        console.log(`Ставка ВЫИГРАЛА: ${winAmount}₽`);
      } 
      // Таракан финишировал, но не на указанном месте - ставка проиграла
      else {
        totalLose += bet.amount;
        losingBets.push({
          ...bet,
          loseAmount: bet.amount,
          bugColors: [bugColors.value[bugId]]
        });
        bet.result = 'lose';
        console.log(`Ставка ПРОИГРАЛА: занял ${finishedPosition} место вместо ${bet.position}`);
      }
    }
    

   // Добавляем обработку ставок на секцию
// В функции checkBetsResults добавить обработку для ставок на секцию
// В обработке ставок на секцию
else if (bet.type === 'section') {
  console.log(`Проверяем ставку на секцию ${bet.trapId} с тараканами: ${bet.bugIds.join(', ')}`);
  
  const winningBugs = [];
  
  bet.bugIds.forEach(bugId => {
    const bugResult = bugs.value.find(b => b.id === bugId);
    // Добавляем проверку, что таракан финишировал именно в нужной секции
    if (bugResult && bugResult.finished && bugResult.trapId === bet.trapId) {
      console.log(`Таракан ${bugId} финишировал в секции ${bugResult.trapId} (нужная секция: ${bet.trapId}) - ВЫИГРЫШ`);
      winningBugs.push({
        id: bugId,
        color: bugColors.value[bugId]
      });
    } else {
      if (bugResult) {
        console.log(`Таракан ${bugId} не подходит: finished=${bugResult.finished}, trapId=${bugResult.trapId}, нужна секция=${bet.trapId}`);
      } else {
        console.log(`Таракан ${bugId} не найден среди финишировавших`);
      }
    }
  });

  if (winningBugs.length > 0) {
    const winAmountPerBug = bet.amount / bet.bugIds.length;
    const totalWin = winAmountPerBug * winningBugs.length * 2.23;
    
    balance.value += totalWin;
    playIncomeSound();
    
    winningBets.push({
      ...bet,
      winAmount: totalWin,
      winningBugs: winningBugs,
      bugColors: winningBugs.map(bug => bug.color),
      timestamp: new Date().toISOString()
    });
    
    console.log(`Ставка на секцию ${bet.trapId} выиграла: ${totalWin}₽`);
  } else {
    losingBets.push({
      ...bet,
      loseAmount: bet.amount,
      timestamp: new Date().toISOString()
    });
    totalLose += bet.amount;
    console.log(`Ставка на секцию ${bet.trapId} проиграла`);
  }
}
  });

  
  betHistory.value.push(...currentRaceBets.value);
  currentRaceBets.value = [];

  if (winningBets.length > 0) {
    showWinNotification(totalWin, winningBets);
  }
  
  
};

// Обновленная функция resetBugSelections
const resetBugSelections = () => {
  selectedTrap.value = null;
  selectedBugs.value = [];
  bugSelections.value = {};
};

// Добавим получение конфигурации из API
const { data: gameConfig } = useFetch('/api/v1/gameplay/games/instances/cockroaches-space-maze');
// Новое состояние для центрального меню
const centerWinMenuVisible = ref(false);
const activeWinMenuId = ref(null);
const historyBetsInsideCenter = ref(false);
const isGroup164Clicked = ref(false);
// Добавляем состояние для выбранного таракана-победителя
const selectedWinnerBugIds = ref([]);
const hasShownSoundHint = ref(false);
// Состояния для управления ставками
const currentBet = ref(25);
const balance = ref(10000); // Начальный баланс
const betHistory = ref([]); // История ставок
const undoStack = ref([]); // Стек для отмены операций

// Используем параметры из API
const stavkiButtons = computed(() => {
  if (!gameConfig.value) return [];
  
  return gameConfig.value.bet_buttons.map(amount => ({
    id: amount.toString(),
    amount: amount
  }));
});

// Добавление ставки
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
  if (!userInteracted.value || !betClickSound.value || !soundEnabled.value) return;
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
   // 1. Обработка win меню
  if (centerWinMenuVisible.value) {
    const menuEl = winMenuCenterRef.value;
    const buttonId = activeWinMenuId.value;
    const buttonEl = buttonId !== null ? buttonWinContainerRefs.value[buttonId] : null;

    if (menuEl && 
        !menuEl.contains(event.target) && 
        !(buttonEl && buttonEl.contains(event.target))) {
      closeWinMenu();
    }
  }

  // 2. Обработка LastGameMenu (Button-1)
  if (lastGameMenuVisible.value) {
    const menuEl = lastGameMenuRef.value?.$el || lastGameMenuRef.value;
    const buttonEl = button1Ref.value;
    
    if (menuEl && 
        !menuEl.contains(event.target) && 
        !buttonEl.contains(event.target)) {
      lastGameMenuVisible.value = false;
    }
  }

  // 3. Обработка CenterMenu (Button-2)
  if (centerMenuVisible.value) {
    const menuEl = centerMenuRef.value;
    const buttonEl = button2Ref.value;
    
    if (menuEl && 
        !menuEl.contains(event.target) && 
        !buttonEl.contains(event.target)) {
      centerMenuVisible.value = false;
      
      // При закрытии центрального меню перемещаем историю ставок наружу
      if (historyBetsVisible.value) {
        historyBetsInsideCenter.value = false;
      }
    }
  }

  // 4. Обработка HistoryBets (Button-3)
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
  
  // Сбрасываем только ставки, не трогая выбор кнопок
  selectedWinnerBugIds.value = [];
  undoStack.value.push({
    type: 'reset',
    prevBet: currentBet.value
  });
  currentBet.value = 25;
 // Полный сброс только для confirmed ставок
  resultButtons.value.forEach(b => {
    b.confirmed = false;
    b.betAmount = 0;
  });
  
  overtakingButtons.value.forEach(b => {
    if (b.confirmed) {
      b.confirmed = false;
      b.betAmount = 0;
      // Сбрасываем выделение только для подтвержденных кнопок
      b.selected = false;
    }
    // Неподтвержденные кнопки остаются выделенными
  });
  
  
};
// Восстановление неподтвержденных выделений при открытии меню

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
   if (activeTab.value === 'result') {
    resultButtons.value.forEach(btn => btn.selected = false);
  } else {
    overtakingButtons.value.forEach(btn => btn.selected = false);
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

// Состояния для уведомлений
const winNotification = ref({
  visible: false,
  amount: 0,
  bets: [],
  timestamp: Date.now()
});

const loseNotification = ref({
  visible: false,
  amount: 0,
  bets: [],
  timestamp: Date.now()
});

// Функции для отображения уведомлений
const showWinNotification = (amount, bets) => {
  // Для нескольких выигрышей показываем одно уведомление
  winNotifications.value.push({
    id: ++notificationCounter.value,
    bets: bets.map(bet => ({
      ...bet,
      winAmount: bet.winAmount, // убедитесь что это правильная сумма
      // Генерация описания ставки
      description: getBetDescription(bet),
      // Получаем цвет для ставки
      color: bet.bugColors[0] || '#000'
    })),
    timestamp: Date.now()
  });
  
  // Запускаем конфетти
  showConfetti.value = true;
};
// Функция для обновления размеров и позиции main-bg
const updateMainBgDimensions = () => {
  const mainBgElement = document.querySelector('.main-bg');
  if (mainBgElement) {
    const rect = mainBgElement.getBoundingClientRect();
    mainBgWidth.value = rect.width;
    mainBgHeight.value = rect.height;
    mainBgLeft.value = rect.left;
    mainBgTop.value = rect.top;
  }
};
const showLoseNotification = (amount, bets) => {
  loseNotification.value = {
    visible: true,
    amount,
    bets: bets.map(bet => ({
      description: getBetDescription(bet),
      amount: bet.amount,
      color: bet.bugColors[0] || '#000'
    })),
    timestamp: Date.now()
  };
  
  // Воспроизведение звука проигрыша
  if (userInteracted.value) {
    const loseSound = new Audio('/sounds/lose.mp3');
    loseSound.volume = soundVolume.value;
    loseSound.play();
  }
};

// Закрытие уведомления
const closeWinNotification = (id) => {
  winNotifications.value = winNotifications.value.filter(n => n.id !== id);
};

const closeLoseNotification = () => {
  loseNotification.value.visible = false;
};

// Генерация описания ставки
// В app.vue
const getBetDescription = (bet) => {
  if (bet.type === 'position') {
    return t('position_bet', { bug: bet.bugId, position: bet.position });
  } else if (bet.type === 'overtaking') {
    return t('overtaking_bet', { overtaker: bet.overtaker, overtaken: bet.overtaken });
  } else if (bet.type === 'section') {
    return t('section_bet', { 
      trap: bet.trapId,
      count: bet.bugIds.length,
      bugs: bet.bugIds.join(', ')
    });
  }
  return t('unknown_bet');
};

const getBugName = (id) => {
  const names = {
    0: t('yellow'),
    1: t('orange'),
    2: t('dark_orange'),
    3: t('blue'),
    4: t('red'),
    5: t('purple'),
    6: t('green')
  };
  return names[id] || t('unknown');
};
// В секции <script setup> app.vue
const saveBetToServer = async (bet) => {
  try {
    let serverType = bet.type;
    let payload = {
      user_id: 1,
      amount: bet.amount,
      type: serverType,
      color: 'linear-gradient(180deg, #FF170F 0%, #FF005E 100%)',
      time: new Date().toTimeString().split(' ')[0]
    };

    // Для ставок на позицию используем серверный тип 'place'
    if (bet.type === 'position') {
      serverType = 'place';
      payload = {
        ...payload,
        type: serverType,
        selection: [bet.bugId, bet.position]
      };
    } 
    // Для ставок на обгон используем серверный тип 'trap'
    // Для ставок на обгон
    else if (bet.type === 'overtaking') {
      payload = {
        ...payload,
        type: 'trap',
        selection: [bet.overtaker, bet.overtaken],
        trapId: 0 // Добавляем trapId для прохождения валидации
      };
    }

    const response = await fetch('/api/save-bet', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Failed to save bet: ${errorText}`);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error saving bet:', error);
    throw error;
  }
};
const placeBet = async () => {
    try {
      

        const workingButtons = activeTab.value === 'result' 
            ? resultButtons.value 
            : overtakingButtons.value;
            
        const selectedButtons = workingButtons.filter(btn => btn.selected);
        playBetClick();
        
        // Для ставок на обгон
        if (activeTab.value === 'overtaking') {
            
            const selectedPairs = [];
            
  for (const [row, columns] of Object.entries(overtakingSelections.value)) {
    for (const col of columns) {
      selectedPairs.push({
        overtaker: Number(row),
        overtaken: Number(col)
      });
    }
  }
  
             if (selectedPairs.length === 0) {
    infoMessage.value = t('select_bug_to_overtake');
    infoNotificationVisible.value = true;
    setTimeout(() => infoNotificationVisible.value = false, 3000);
    return;
  }
  
  // Создаем ставки для КАЖДОЙ пары
  selectedPairs.forEach(pair => {
    const bet = {
      type: 'overtaking',
      overtaker: pair.overtaker,
      overtaken: pair.overtaken,
      amount: currentBet.value,
      timestamp: new Date().toISOString(),
      result: 'pending'
    };
    
    betHistory.value.push(bet);
    nextRaceBets.value.push(bet);
    
    // Сохраняем на сервере
    saveBetToServer({
      type: 'overtaking',
      overtaker: pair.overtaker,
      overtaken: pair.overtaken,
      amount: currentBet.value
    });
  });
            
            // Списание средств
  balance.value -= currentBet.value * selectedPairs.length;
  playOutcomeSound();
  showBetPlacedNotification();
            
            // Отмечаем кнопки как подтвержденные
  selectedPairs.forEach(pair => {
    const buttonId = pair.overtaker * 7 + pair.overtaken;
    const button = overtakingButtons.value.find(b => b.id === buttonId);
    if (button) {
      button.confirmed = true;
      button.betAmount = currentBet.value;
      // Сбрасываем выделение после подтверждения
      button.selected = false; // <-- ДОБАВЛЕНА ЭТА СТРОКА
    }
  });

  clearOvertakingSelections(); // ДОБАВИТЬ
}

        // Для ставок на результат (позицию)
        if (activeTab.value === 'result') {
            const selectedButtons = resultButtons.value.filter(btn => btn.selected);
            
            if (selectedButtons.length > 0) {
                // Рассчитываем общую сумму ставки
                const totalBetAmount = currentBet.value * selectedButtons.length;
                
                if (totalBetAmount > balance.value) {
                    infoMessage.value = t('insufficient_funds');
                    infoNotificationVisible.value = true;
                    setTimeout(() => infoNotificationVisible.value = false, 3000);
                    return;
                }

                // Списание средств
                balance.value -= totalBetAmount;
                playOutcomeSound();
                showBetPlacedNotification();
                
                // Для каждой выбранной кнопки создаем отдельную ставку
                selectedButtons.forEach(button => {
                    const row = Math.floor(button.id / 7);
                    const col = button.id % 7;
                    const bugId = row + 1;
                    const position = col + 1;
                    
                    const bet = {
                        type: 'position',
                        bugId: bugId,
                        position: position,
                        amount: currentBet.value,
                        timestamp: new Date().toISOString()
                    };
                    
                    betHistory.value.push(bet);
                    nextRaceBets.value.push(bet);
                    
                    // Сохраняем на сервере
                    saveBetToServer({
                        type: 'position',
                        bugId: bugId,
                        position: position,
                        amount: currentBet.value
                    });
                });
                
                // Обновляем UI
                selectedButtons.forEach(button => {
                    button.confirmed = true;
                    button.betAmount = currentBet.value;
                    button.selected = false;
                });
            } else {
                infoMessage.value = t('select_bet_option');
                infoNotificationVisible.value = true;
                setTimeout(() => infoNotificationVisible.value = false, 3000);
            }
        }
        
        // Обновляем историю ставок
        fetchBetHistory();
        
    } catch (error) {
        console.error('Error saving bet:', error);
    }
};
watch(activeTab, (newTab) => {
  if (newTab === 'overtaking') {
    // Восстанавливаем ВСЕ выделения из overtakingSelections
    overtakingButtons.value.forEach(btn => {
      const row = Math.floor(btn.id / 7);
      const col = btn.id % 7;
      
      // Для подтвержденных кнопок не восстанавливаем выделение
      if (!btn.confirmed) {
        btn.selected = overtakingSelections.value[row]?.has(col) || false;
      }
    });
  }
});

// ... остальной код ...
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

const lastGames = ref([
  { id: 1, results: [{ position: 1, color: '#FF0000' }, { position: null, color: '#00FF00' }] },
  { id: 2, results: [{ position: 3, color: '#0000FF' }, { position: 2, color: '#FFFF00' }] }
]);


const setActiveTab = (tab) => {
  playStakeActionClick();
  
  // Сбрасываем выделение на текущей активной вкладке
  if (activeTab.value === 'result') {
    resultButtons.value.forEach(btn => btn.selected = false);
  } 
  
  // Устанавливаем новую вкладку
  activeTab.value = tab;
  
  // Сбрасываем выбранных тараканов-победителей
  selectedWinnerBugIds.value = [];
  
  // Если переключаемся на Overtaking, сбрасываем выбор обгона
  if (tab === 'overtaking') {
    overtakingSelection.value = [];
  }
};

// 2. Добавляем метод для восстановления выделений
const restoreOvertakingSelections = () => {
  overtakingButtons.value.forEach(btn => {
    const row = Math.floor(btn.id / 7);
    const col = btn.id % 7;
    
    // Восстанавливаем только неподтвержденные выделения
    if (!btn.confirmed) {
      btn.selected = overtakingSelections.value[row]?.has(col) || false;
    }
  });
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

  // Воспроизведение звука для каждого взрыва
  bugs.value.forEach(bug => {
    if (!bug.finished) {
      // Для тараканов, которые не финишировали - воспроизводим звук
      if (userInteracted.value && explosionSound.value && soundEnabled.value) {
        try {
          explosionSound.value.currentTime = 0;
          explosionSound.value.volume = soundVolume.value;
          explosionSound.value.play().catch(e => console.error("Explosion sound error:", e));
        } catch (e) {
          console.error("Explosion playback error:", e);
        }
      }
    }
  });
  
  startExplosionAnimation();
};

// Add this method after the selectTrap function
// В секции script
const toggleBugSelection = (bugId) => {
  console.log("Selected bug ID:", bugId); // Для отладки
  if (lockedBugsArray.value.includes(bugId)) return;
  playStakeActionClick();
   // Логирование с указанием позиции и цвета
  const positions = ["первый", "второй", "третий", "четвёртый", "пятый", "шестой", "седьмой"];
  const colors = [
  "жёлтый",        // ID 1
  "оранжевый",     // ID 2
  "тёмно-оранжевый", // ID 3
  "синий",         // ID 4
  "красный",       // ID 5
  "фиолетовый",    // ID 6
  "зелёный"        // ID 7
];

   console.log(`Выбран ${positions[bugId-1]} таракан (${colors[bugId-1]})`);
  
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
         (activeTab.value === 'overtaking');
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
  if (!userInteracted.value || !soundEnabled.value) return; // Добавьте эту проверку
  // Проверяем что bugs.value существует и index в пределах массива
  if (!bugs.value || index >= bugs.value.length) return;
  
  const bug = bugs.value[index];
  
  // Проверяем наличие необходимых свойств
  if (!bug || bug.finished || bug.exploded || 
     (bug.explodeFrame !== undefined && !bug.finished)) {
    return;
  }
// Получение конфигурации ставок
const fetchBetConfig = async () => {
  try {
    const response = await fetch('/api/v1/gameplay/games/instances/cockroaches-space-maze');
    const data = await response.json();
    betConfig.value = {
      minBet: data.min_bet,
      maxBet: data.max_bet,
      betStep: data.bet_step,
      currency: data.currency
    };
  } catch (error) {
    console.error('Ошибка загрузки конфигурации ставок:', error);
  }
};

// Обновляем текущее время каждую минуту
onMounted(() => {
  fetchBetConfig();
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
      dizzySoundElement.value.currentTime = 0;
      dizzySoundElement.value.volume = soundVolume.value;
      dizzySoundElement.value.play().catch(e => console.error("Dizzy sound error:", e));
    } catch (e) {
      console.error("Dizzy playback error:", e);
    }
  }
};

// ==============================
// МЕТОДЫ ДЛЯ УПРАВЛЕНИЯ СТАВКАМИ
// ==============================
// Конфигурация расстояния
const gap = 0.5; // % расстояния между кнопками

const menuBugs = ref([
  // Желтый (1)
  { id: 1, width: 12, height: 40 },
  // Оранжевый (2) - ID 2
  { id: 2, width: 12, height: 40 },
  // Темно-оранжевый (3) - ID 3
  { id: 3, width: 12, height: 40 },
  // Синий (4)
  { id: 4, width: 12, height: 40 },
  // Красный (5)
  { id: 5, width: 12, height: 40 },
  // Фиолетовый (6)
  { id: 6, width: 12, height: 40 },
  // Зеленый (7)
  { id: 7, width: 12, height: 40 }
]);
// В секции <script setup>
const bugColors = ref([
  '#FFFF00', // 1. Жёлтый
  '#FFA500', // 2. Оранжевый
  '#8B0000', // 3. Тёмно-оранжевый
  '#0000FF', // 4. Синий
  '#FF0000', // 5. Красный
  '#800080', // 6. Фиолетовый
  '#00FF00'  // 7. Зелёный
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
  

   
// Проверяем результаты ставок
  setTimeout(() => {
    checkBetsResults();
  }, 1000);

  // Деактивируем сессию на сервере
await fetch('/api/gameplay/games/sessions/cockroaches-space-maze/deactivate', {
    method: 'POST'
});
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

   // В блоке фазы движения к точке финиша
// В блоке фазы движения к точке финиша
if (distance <= 1) {
  bug.finished = true;
  button.occupied = true;
  button.finishedBugId = bug.id;
  bug.finishTime = now;
  
  // Сохраняем ID ловушки для ставок на секцию
  bug.trapId = bug.targetButtonId;
  
  // Логируем приход таракана с временем финиша
  const finishTimeMs = (now - raceStartTime.value).toFixed(0);
  console.log(`Таракан ${bug.id + 1} (${getBugName(bug.id)}) финишировал за ${finishTimeMs}мс`);
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
  const totalBugs = menuBugs.value.length;
  const containerWidth = 80; // Ширина контейнера в %
  const bugWidth = 14; // Ширина каждого таракана в %
  const gap = 3; // Увеличиваем отступ между тараканами
  
  return {
    left: `${5 + index * (bugWidth + gap)}%`, // 10% отступ слева + позиция
    top: `38%`,
    width: `${bugWidth}%`,
    height: `${menuBugs.value[index].height}%`,
  };
};

// Обработчики событий
const hoverBug = (index) => {
  hoveredBugIndex.value = index;
};

const unhoverBug = () => {
  hoveredBugIndex.value = null;
};



const betHistoryFromApi = ref([]);

const fetchBetHistory = async () => {
  try {
    // Добавляем задержку между запросами
    await new Promise(resolve => setTimeout(resolve, 500));
    
    const response = await fetch('/api/get-bets');
    const data = await response.json();
     // Логирование полученных данных
    console.log("Bet history data:", data);

    // Преобразование данных к нужному формату
    betHistoryFromApi.value = data.map(item => ({
      ...item.data,
      time: item.data.time || item.timestamp.split(' ')[1] // Используем время из данных
    }));
    
  } catch (error) {
    console.error('Error fetching bet history:', error);
    betHistoryFromApi.value = [];
  }
};

// Вызывать при открытии окна
watch(historyBetsVisible, (visible) => {
  if (visible) {
    
  }
});

// Обработчик для кнопки истории ставок
// app.vue
const toggleHistoryBets = () => {
  // Всегда открываем центральное меню при нажатии на кнопку истории
  if (!centerMenuVisible.value) {
    centerMenuVisible.value = true;
  }
  
  // Переключаем видимость истории ставок
  historyBetsVisible.value = !historyBetsVisible.value;
  
  // Загружаем историю при открытии
  if (historyBetsVisible.value) {
    fetchBetHistory();
  }
};

// Закрытие истории при открытии центрального меню
watch(centerMenuVisible, (newVal) => {
  if (newVal) {
    // Восстанавливаем выделения для ставок на обгон
    restoreOvertakingSelections();
  
    
    // Дополнительно: восстанавливаем активную вкладку
    if (activeTab.value === 'overtaking') {
      restoreOvertakingSelections();
    }
  }
});

watch(locale, () => {
  if (centerMenuVisible.value && activeTab.value === 'overtaking') {
    restoreOvertakingSelections();
  }
});


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
  resetCurrentBet(); // Теперь просто сбрасывает ставку без побочных эффектов
  
  
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






// Новая функция для сброса только текущей ставки
const resetCurrentBet = () => {
  if (cancelPendingBetSound.value && userInteracted.value) {
    cancelPendingBetSound.value.currentTime = 0;
    cancelPendingBetSound.value.volume = soundVolume.value;
    cancelPendingBetSound.value.play().catch(e => console.error("Cancel sound error:", e));
  }
  
  // Сбрасываем ставку к минимальному значению 25
  currentBet.value = 25;
  
  // Очищаем стек отмены
  undoStack.value = [];
};
const handleOtmenaButtonClick = () => {
  resetAllBets();
  
  
};

const startAction = (action) => {
  action();
  actionTimeout.value = setTimeout(() => {
    actionInterval.value = setInterval(action, actionSpeed.value);
    speedUpInterval.value = setInterval(() => {
      actionSpeed.value = Math.max(50, actionSpeed.value - 50);
      clearInterval(actionInterval.value);
      actionInterval.value = setInterval(action, actionSpeed.value);
    }, 500);
  }, 500);
};




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



const resultButtons = ref(
  Array.from({ length: 49 }, (_, index) => 
    reactive({
      id: index,
      selected: false,
      confirmed: false,
      betAmount: 0
    })
  )
);

const overtakingButtons = ref(
  Array.from({ length: 49 }, (_, index) => 
    reactive({
      id: index,
      selected: false,
      confirmed: false,
      betAmount: 0
    })
  )
);

// Обновленные кнопки победы
const winButtons = ref([
  { id: 7, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 4, bluePoint: [360, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 6, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 59, bluePoint: [305, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 5, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 114, bluePoint: [250, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 4, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 169, bluePoint: [195, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 3, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 224, bluePoint: [140, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 2, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 279, bluePoint: [85, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 1, bugs: [], coefficient: 1.1,hovered: false, top: 687, right: 334, bluePoint: [30, 687], menuVisible: false, menuTimer: null, selected: false },
]);

// Сброс выбора обгона

const resetOvertakingSelection = () => {
  // Очищаем только подтвержденные ставки
  overtakingButtons.value.forEach(btn => {
    if (btn.confirmed) {
      btn.confirmed = false;
      btn.betAmount = 0;
      btn.selected = false;
    }
  });
  
  // НЕ сбрасываем неподтвержденные выделения (selected)
};
// ==============================
// ВЫЧИСЛЯЕМЫЕ СВОЙСТВА
// ==============================
const toggleMusic = () => {
  soundEnabled.value = !soundEnabled.value;
  
  // Обновляем состояние музыки
  isMusicPlaying.value = soundEnabled.value;
  
  if (soundEnabled.value) {
    enableMusic().catch(showMusicEnableHint);
  } else if (backgroundMusic.value) {
    backgroundMusic.value.pause();
  }
};



const diagonalButtons = computed(() => {
  const diagonals = [];
  for (let i = 0; i < 7; i++) {
    diagonals.push(i * 8); // 0, 8, 16, 24, 32, 40, 48
  }
  return diagonals;
});

// Обработчик выбора кнопки в меню
const toggleMenuButton = (btn) => {
  playStakeActionClick();
    if (activeTab.value === 'overtaking' && diagonalButtons.value.includes(btn.id)) {
    return;
  }
  const row = Math.floor(btn.id / 7);
  const col = btn.id % 7;
 
    // Запрещаем взаимодействие с подтвержденными кнопками
  if (btn.confirmed) return; // <-- Добавленная проверка

  console.log(`Toggling button: id=${btn.id}, row=${row}, col=${col}, bug=${getBugName(row+1)}, position=${col+1}`);
  if (activeTab.value === 'overtaking') {
    // Пропускаем диагональные кнопки
    if (row === col) return;

    // Инициализируем строку если нужно
    if (!overtakingSelections.value[row]) {
      overtakingSelections.value[row] = new Set();
    }

    
    // Проверка блокировки
    if (lockedBugsArray.value.includes(row) || 
        lockedBugsArray.value.includes(col)) {
      return;
    }
  // Переключение выбора столбца
  if (overtakingSelections.value[row].has(col)) {
    overtakingSelections.value[row].delete(col);
    btn.selected = false;
  } else {
    overtakingSelections.value[row].add(col);
    btn.selected = true;
  }

    const { overtaker, overtaken } = overtakingSelection.value;
    
    // Если выбран новый обгоняющий
    if (overtaker !== row) {
      
     
      
      // Устанавливаем нового обгоняющего
      overtakingSelection.value.overtaker = row;
      overtakingSelection.value.overtaken = new Set([col]);
      btn.selected = true;
    } 
    // Тот же обгоняющий - добавляем/удаляем обгоняемого
    else {
      const newSet = new Set(overtaken);
      if (newSet.has(col)) {
        newSet.delete(col);
        btn.selected = false;
      } else {
        newSet.add(col);
        btn.selected = true;
      }
      overtakingSelection.value.overtaken = newSet;
      // Обновляем текущую строку
    currentOvertakerRow.value = row;
    }
  }


  
    if (activeTab.value === 'result') {
    // Проверка блокировки
    if (lockedBugsArray.value.includes(Math.floor(btn.id / 7))) {
      return;
    }
    
    // Если кнопка уже подтверждена, не позволяем выбирать
    if (btn.confirmed) {
      return;
    }
    
    // Инвертируем состояние выбора
    btn.selected = !btn.selected;
    
    // Если кнопка теперь выбрана, сохраняем текущую ставку
    if (btn.selected) {
        btn.pendingBetAmount = currentBet.value;
    }
    
}
console.log(`Toggling button: id=${btn.id}, row=${row}, col=${col}, bug=${getBugName(row+1)}, position=${col+1}`);

};

// ==============================
// МЕТОДЫ УПРАВЛЕНИЯ ИНТЕРФЕЙСОМ
// ==============================
// Переключение кнопки меню


const toggleCenterMenu = () => {
  // Закрываем другие меню
  lastGameMenuVisible.value = false;
  centerWinMenuVisible.value = false;
  
  // Открываем/закрываем центральное меню
  centerMenuVisible.value = !centerMenuVisible.value;
  
  // Автоматически открываем историю ставок при открытии центрального меню
  if (centerMenuVisible.value) {
    historyBetsVisible.value = true;
    historyBetsInsideCenter.value = true;
    fetchBetHistory();
  } else {
    // При закрытии перемещаем историю наружу
    historyBetsInsideCenter.value = false;
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
  unlockAllBugs();
  const confirmedBets = [];
// Сбрасываем блокировку тараканов
  lockedBugs.value = new Set();
countdownPlayed.value = false; // Сбрасываем флаг
playRaceStartSound(); // <-- ДОБАВИТЬ ЗДЕСЬ
// Активируем сессию на сервере
await fetch('/api/gameplay/games/sessions/cockroaches-space-maze/activate', {
    method: 'POST'
});

// СОХРАНЯЕМ состояние выбора если меню открыто
  if (!centerWinMenuVisible.value) {
    resetBugSelections();
  }
  // ПЕРЕМЕЩАЕМ ставки на следующую игру в currentRaceBets
  if (nextRaceBets.value.length > 0) {
    currentRaceBets.value = [...nextRaceBets.value];
    nextRaceBets.value = [];
  }
  // Сбрасываем состояния кнопок после проверки ставок
// Стало: сбрасываем только confirmed кнопки
  resultButtons.value.forEach(b => {
    b.confirmed = false;
    b.betAmount = 0;
  });

  overtakingButtons.value.forEach(btn => {
    if (btn.confirmed) {
      btn.confirmed = false;
      btn.betAmount = 0;
    }
  });
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

  confirmedBets.forEach(bet => {
    const btn = overtakingButtons.value.find(b => b.id === bet.id);
    if (btn) {
      btn.confirmed = true;
      btn.betAmount = bet.amount;
    }
  });
};
const clearOvertakingSelections = () => {
  // Сбрасываем только НЕподтвержденные выделения
  overtakingButtons.value.forEach(btn => {
    if (!btn.confirmed) {
      btn.selected = false;
    }
  });
  
  // Очищаем объект выделений
  overtakingSelections.value = {};
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
  
  // Воспроизведение звука взрыва
  if (userInteracted.value && explosionSound.value && soundEnabled.value) {
    try {
      explosionSound.value.currentTime = 0;
      explosionSound.value.volume = soundVolume.value;
      explosionSound.value.play().catch(e => console.error("Explosion sound error:", e));
    } catch (e) {
      console.error("Explosion playback error:", e);
    }
  }
  
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
// Добавляем вычисление масштаба
const updateScale = () => {
  const baseWidth = 390;
  const baseHeight = 788;
  const currentWidth = window.innerWidth;
  const currentHeight = ref(window.innerHeight);
  
  // Если высота меньше 690px, используем медиазапрос вместо transform
  if (currentHeight < 690) {
    scaleFactor.value = 1; // Позволим CSS медиазапросам handle масштабирование
    return;
  }
  
  // Масштабирование по высоте с приоритетом
  const scaleByHeight = currentHeight / baseHeight;
  
  // Учитываем минимальный масштаб для очень маленьких экранов
  scaleFactor.value = Math.max(scaleByHeight, 0.7);
  
  // Для очень широких экранов ограничиваем масштаб по ширине
  const scaledWidth = baseWidth * scaleFactor.value;
  if (scaledWidth > currentWidth) {
    scaleFactor.value = currentWidth / baseWidth;
  }
};


onMounted(() => {
  setTimeout(updateScale, 100);
  
  document.addEventListener('click', handleDocumentClick);
 
    document.addEventListener('visibilitychange', handleVisibilityChange);
  
  
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
  updateMainBgDimensions();
 window.addEventListener('resize', () => {
    currentHeight.value = window.innerHeight;
    updateScale();
  });
});

onUnmounted(() => {
  
  window.removeEventListener('resize', updateScale);
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
window.removeEventListener('resize', updateMainBgDimensions);

});


</script>
<style scoped>
@media (max-height: 690px) {
  .main-bg {
    transform: scale(0.95);
    transform-origin: top center;
    min-height: 600px;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  
}

@media (max-height: 670px) {
  .main-bg {
    transform: scale(0.93);
    transform-origin: top center;
    min-height: 600px;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  
}

@media (max-height: 645px) {
  .main-bg {
    transform: scale(0.90);
    transform-origin: top center;
    min-height: 600px;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  
}

@media (max-height: 635px) {
  .main-bg {
    transform: scale(0.87);
    transform-origin: top center;
    min-height: 600px;
    margin-top: 20px;
    margin-bottom: 20px;
  }
  
}
/* Для очень маленьких экранов */
@media (max-height: 615px) {
  .main-bg {
    transform: scale(0.84);
    min-height: 500px;
  }
}

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
  width: 30px;
  height: 30px;
  background-color: #000000;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
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
.win-content-wrapper {
  transform: translateY(-13%);
  width: 100%;
  height: 100%;
  position: relative;
}

.win-menu-center {
  /* остальные стили остаются без изменений */
  position: absolute;
  top: 74%;
  left: 40%;
  transform: translate(-50%, -50%);
  z-index: 20;
  width: 80%;
  max-width: 360px;
  max-height: 80%;
  display: flex;
  justify-content: center;
  align-items: center;
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
.win-button-text {
  position: relative;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.coefficient-x {
  position: absolute;
  left: 37.5%;
  right: 37.5%;
  top: 12.5%;
  bottom: 64.21%;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 300;
  font-size: 24px;
  line-height: 29px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
}

/* Стили для меню ставок в центральном меню *//* Стили для меню ставок в центральном меню */



.stavki-container-in-center {
  position: relative;
  width: 100%;
  max-width: 360px;
  left: 4%;
  z-index: 10;
  transform-origin: top center;
  transition: all 0.3s ease;
}


/* Стили для меню ставок в центральном меню *//* Стили для меню ставок в центральном меню */
.button-coefficient {
  position: absolute;
  left: 28.85%;
  right: 28.85%;
  top: 46.73%;
  bottom: 26.71%;
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 300;
  font-size: 24px;
  line-height: 29px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
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


@keyframes button-click {
  0% { transform: scale(1.05); }
  50% { transform: scale(0.95); }
  100% { transform: scale(1.05); }
}
.language-switcher {
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
  cursor: pointer;
}



.info-notification {
   position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 90vw; /* Адаптивная ширина */
  max-width: 372px; /* Максимальная ширина */
  background: rgba(255, 255, 255, 0.9);
  
  border-radius: 10px;
  z-index: 10000;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  cursor: pointer;
}
/* Добавляем стили для заблокированных элементов */
.menu-button.locked {
  opacity: 1;
  cursor: not-allowed;
  pointer-events: none;
}

.win-buttons-container {
  position: absolute;
  top: 73%;
  right: 0;
  width: 100%;
  height: 66px;
  z-index: 4;
  pointer-events: none; /* Пропускает события мыши к дочерним элементам */
}

.win-buttons-container .button-win-container {
  pointer-events: auto; /* Включает события мыши для кнопок */
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
/* Анимации для уведомлений */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
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

/* Контейнер вкладок */
.menu-tabs {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  display: flex;
  height: 24px;
  z-index: 15;
}
  

/* Специфичные стили для вкладки Result */
.result-tab {
  border-radius: 0 50px 50px 0;
}
/* Стиль для вкладки Overtaking (теперь слева) */
.overtaking-tab {
  border-radius: 50px 0 0 50px; /* Закругление слева */
}
/* Стили для НЕактивных вкладок */
.tab-button:not(.active) {
  background: rgba(0, 0, 0, 0.4);
}

/* Стиль для активной вкладки */
.tab-button.active {
  box-shadow: 0 0 8px rgba(255, 255, 0, 0.8);
  filter: brightness(1.2);
}

/* Существующие стили прогресс-бара */
.progress-container {
  position: absolute;
  top: 7%; /* Располагаем сразу под panel-up (высота panel-up) */
  left: 0;
  right: 0;
  width: 100%;
  padding: 0 10px;
  box-sizing: border-box;
  z-index: 6; /* Убедимся что прогресс-бар выше фона */
}

.progress-bar {
  height: 10px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 5px;
  overflow: hidden;
  position: relative;
}
.scroll-container {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  scrollbar-color: #7F00FE #1E031E;
}



.scroll-container::-webkit-scrollbar {
  width: 8px;
}

.scroll-container::-webkit-scrollbar-track {
  background: #1E031E;
  border-radius: 4px;
}

.scroll-container::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #7F00FE 0%, #66008F 98.9%);
  border-radius: 4px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #9B30FF 0%, #8B30DB 98.9%);
}

/* Плавная прокрутка */
.scroll-container {
  scroll-behavior: smooth;
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
  bottom: 115%;
  left: 4%;
  
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



@keyframes bug-click {
  0% { transform: translate(-50%, -50%) scale(1.1); }
  50% { transform: translate(-50%, -50%) scale(0.9); }
  100% { transform: translate(-50%, -50%) scale(1.1); }
}


/* Контейнер для меню с относительным позиционированием */



.win-menu-title {
  position: absolute;
  top: 14%; /* Позиционирование сверху */
  left: 5%;
  right: 0;
  width: 100%;
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
  left: 10%;
  position: absolute;
  background-color: transparent;
  cursor: pointer;
  border-radius: 5px;
  transform-origin: center; /* Фиксируем точку трансформации */
  transition: all 0.3s ease;
  transform-origin: center;
  z-index: 11;
   
}

/* Эффекты при наведении */
.bug-button-hovered {
  transform: scale(1.15); /* Увеличиваем без смещения */
  filter: 
    drop-shadow(0 0 8px rgba(255, 255, 0, 0.8))
    drop-shadow(0 0 15px rgba(255, 215, 0, 0.6));
  z-index: 12;
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
  top: 74%;
  left: 40%;
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




/* Желтая центральная подсветка */
.bug-button-hovered::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  
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
  top: 55%;
  left: 38%;
  z-index: 10;
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


.game-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: flex-start; /* Изменено с center на flex-start */
  overflow: hidden;
}




.menu-button.text-button {
  background-image: none !important;
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
}


.confirmed-bet-amount {
  font-family: 'Hero', 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 13px;
  line-height: 16px;
  color: #FFFFFF; /* Белый цвет вместо прозрачного */
  margin-top: 30%;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8); /* Добавляем тень для лучшей читаемости */
  opacity: 1 !important; /* Убираем любую прозрачность */
  font-style: normal; /* Убираем курсив если был */
}
/* Усиливаем видимость для заблокированных элементов */
.menu-button.locked .confirmed-bet-amount {
  color: #FFFFFF !important;
  opacity: 1 !important;
}

/* Стиль для текста коэффициента */
.coefficient-text {
  position: relative;
  z-index: 2;
  font-family: 'Hero', 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 17px;
  line-height: 20px;
  text-align: center;
  color: #3F3F3F;
  
}


.menu-button.diagonal {
    width: 40px;
    height: 28px;
    position: relative;
    background: rgba(0, 0, 0, 0.3) !important;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Скрываем содержимое диагональных кнопок */
.menu-button.diagonal .coefficient-container,
.menu-button.diagonal .confirmed-content {
    display: none !important;
}






@keyframes pulse {
  0% { opacity: 0.7; transform: scale(1); }
  100% { opacity: 1; transform: scale(1.05); }
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
  top: 232%; /* Регулируйте по необходимости */
  left: 53%;
  transform: translate(-50%, -50%);
  
  display: grid;
  grid-template-columns: repeat(7, 1fr); /* 7 колонок */
  grid-template-rows: repeat(7, 1fr); /* 7 строк */
  z-index: 4; /* Поверх изображений меню */
  --button-width: 42px; /* Ширина кнопок по умолчанию */
  --button-height: 30px; /* Высота кнопок по умолчанию */
   /* Разделяем горизонтальное и вертикальное расстояние */
  --column-gap: 3px; /* Горизонтальное расстояние */
  --row-gap: 9px;   /* Вертикальное расстояние (можно увеличивать отдельно) */
   
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
  margin-top: -13%;
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



.menu-button.diagonal.locked {
  pointer-events: none;
  opacity: 0.5;
  cursor: not-allowed;
  background: rgba(0, 0, 0, 0.3) !important;
}
.game-container {
  transform-origin: top center; /* Масштабирование от верхнего края */
  flex-shrink: 0;
}
/* Для очень маленьких экранов добавим минимальный масштаб */
@media (max-height: 700px) {
  .game-container {
    transform: scale(0.9) !important;
  }
}

@media (max-height: 600px) {
  .game-container {
    transform: scale(0.8) !important;
  }
}

.menu-button.diagonal.locked .coefficient-container,
.menu-button.diagonal.locked .confirmed-content {
  display: none !important;
}
.menu-button {
  width: 40px;
  height: 28px;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  cursor: pointer;
  transition: filter 0.2s ease;
   border-radius: 2px; /* Регулируйте это значение по вкусу */
  overflow: hidden; /* Важно для корректного отображения закругления */
  /**/
  
}
/* Стили для подтвержденной кнопки */
.menu-button.confirmed {
  background: #6B51FF !important;
  border-radius: 3px;
  background-image: none !important;
  position: relative;
  
}


/* Стили для коэффициента в подтвержденной кнопке */
.confirmed-coefficient {
  position: absolute;
  left: 0;
  right: 0;
  top: 4px;
  font-family: 'Hero', 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 8px;
  line-height: 10px;
  text-align: center;
  color: #FFC567;
  z-index: 3;
}

/* Обновленные стили для состояний кнопок */
.menu-button.selected {
  background-color: #000 !important;
  box-shadow: 0 0 8px rgba(255, 255, 255, 0.8) !important;
}



/* Стили для текста внутри выбранной кнопки */
.menu-button.selected .coefficient-text,
.menu-button.selected  {
  color: white !important; /* Белый текст */
  
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
  
  position: absolute;
  top: 1.7%; /* Точная подстройка позиции сверху */
  height: 689px; /* Соответствует соотношению сторон 390x689 */
  width: 100%; /* Занимает всю ширину родителя */
  pointer-events: none;
  
   /* Сохраняем соотношение сторон 390x689 */
  background-image: url('/images/background/labirint.png');
  
  background-size: contain; /* Изображение полностью помещается в элемент */
  background-position: center;
  background-repeat: no-repeat;
  z-index: 5;
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

/* Добавляем стили для заблокированных тараканов */
.bug-button.locked {
  opacity: 0.5;
  filter: grayscale(100%) brightness(0.7);
}

/* Убираем эффекты при наведении на заблокированных */
.bug-button.locked:hover {
  transform: scale(1) !important;
  filter: grayscale(100%) brightness(0.7) !important;
}

/* Отключаем анимацию клика для заблокированных */
.bug-button.locked.bug-button-clicked {
  animation: none !important;
}

/* Затемняем изображение таракана */
.bug-button.locked .bug-image {
  filter: brightness(0.6);
}
/* Иконка замка для заблокированных тараканов */
.bug-button.locked::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  background-image: url('/images/icons/lock.png');
  background-size: contain;
  background-repeat: no-repeat;
  
  z-index: 15;
}
/* Стили для черной менюшки */
.panel-up {
  position: absolute;
  top: 0%;
  left: 0;
  width: 100%;
  height: 43px;
  background: #000000;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10px;
  box-sizing: border-box;
  z-index: 6;
}
.left-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.right-group {
  display: flex;
  align-items: center;
  gap: 0px;
  margin-left: auto; /* Прижимаем всю группу к правому краю */
}

/* Обновленные стили для контейнера баланса */
.balance-container {
  display: flex;
  align-items: center;
  order: 1;
  gap: 5px;
  flex-direction: row-reverse; /* Кнопка справа, текст слева */
}

/* Стиль для выбранного таракана */
.bug-button.selected {
  box-shadow: none !important;
}

.menu-button.confirmed {
  background: linear-gradient(180deg, #3C42E3 0%, #2A2FBF 100%) !important;
}

/* Add central glow effect for selected bugs */
.bug-button.selected::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: radial-gradient(
    circle, 
    rgba(255, 255, 0, 0.8) 0%, 
    rgba(255, 255, 0, 0.4) 50%, 
    rgba(255, 255, 0, 0) 70%
  );
  z-index: 10;
  animation: central-glow 1s infinite alternate;
  pointer-events: none;
}

/* Enhanced glow animation */
@keyframes central-glow {
  0% {
    opacity: 0.7;
    transform: translate(-50%, -50%) scale(0.9);
  }
  100% {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
  }
}

/* Keep the image scaling animation for selected bugs */
.bug-button.selected .bug-image {
  animation: selected-pulse 1s infinite alternate;
}

@keyframes selected-pulse {
  0% { transform: translate(-50%, -50%) scale(1); }
  100% { transform: translate(-50%, -50%) scale(1.1); }
}

/* Ensure the bug image stays above the glow effect */
.bug-image {
  z-index: 13;
  position: relative;
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
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  background-color: #1E031E; /* Добавьте это свойство */
}

.tarakan {
  position: absolute; /* Вместо relative */
  width: 30px;
  height: 38px;
  scale: 90%;
  margin-top: -13%;
  transform: translate(-50%, -50%) ; /* Центрирование */
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
  height: 66px;
  z-index: 4;
  
}

/* Позиции контейнеров */
.button-win-1 { top: 81.2%; right: 4px; }
.button-win-2 { top: 81.2%; right: 59px; }
.button-win-3 { top: 81.2%; right: 114px; }
.button-win-4 { top: 81.2%; right: 169px; }
.button-win-5 { top: 81.2%; right: 224px; }
.button-win-6 { top: 81.2%; right: 279px; }
.button-win-7 { top: 81.2%; right: 334px; }

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
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 350;
  font-size: 10px;
  color: #FFFFFF;
  text-transform: uppercase;
  white-space: nowrap;
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
/* Делаем иконку более заметной в углу */
.icon-button {
  margin-left: 10px;
  order: 2; /* Перемещаем иконку в конец контейнера */
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


.main-bg {
  background-image: url('/images/background/Frame 560.png');
  background-position: center top;
  background-repeat: no-repeat;
  background-size: 100% auto;
  /* Фиксированные размеры контейнера */
  width: 390px;
  min-height: 788px;
  height: 100%;
  max-height: 788px;
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



.menu-button {
  visibility: hidden;
  pointer-events: none;
}


/* Специфичные стили для каждой кнопки */
.button-1 .button-text {
  padding: 0 0px;
  text-align: center;
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
  width: 100%;
  height: 100%;
  justify-content: center;
}
.button-2 .button-text {
  font-size: 20px; /* Чуть больше для центральной кнопки */
  padding: 0px 0px;
}

.button-3 .button-text {
  padding: 0 0px;
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
  position: relative;
  width: 30px;
  height: 30px;
  background-color: #000000;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}
.history-bets.inside-center {
  position: absolute;
  top: 0;
  right: 0;
  margin-top: 31%;
  margin-right: 0%;
  width: 236px;
  height: 150px;
  z-index: 15; /* Поверх центрального меню */
  transform: none;
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
  width: 100%;
  height: 11%;
  bottom: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, #000000 100%);
  z-index: 6;
  display: flex;
  justify-content: center;
  align-items: center;
}
/* Новый контейнер для кнопок */
.buttons-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 390px;
  padding: 0 20px;
  position: relative;
  z-index: 7;
}
/* Стили для центрального меню */
.center-menu {
  position: absolute;
    bottom: calc(464% + 40%); /* Сдвигаем на 13% вверх */
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



#Button-1 .button-text {
  white-space: pre-line;
  line-height: 1.2;
  display: flex;
  flex-direction: column;
  justify-content: center;
}




.menu-button.button-visible {
  visibility: visible;
  pointer-events: auto;
}
.button-balans {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 49px;
  height: 19px;
  padding: 3px 6px;
  background: linear-gradient(180deg, #7F00FE 0%, #66008F 98.9%);
  box-shadow: 0px 1px 0px #191E47;
  border-radius: 49px;
  color: white;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
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
  box-sizing: border-box;
  height: 100%;
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

.button-1 {
  margin-left: 1%;
}
/* Стиль для вкладки Overtaking */
.overtaking-tab {
  background: linear-gradient(180deg, rgba(127, 0, 254, 0.7) 0%, rgba(102, 0, 143, 0.7) 98.9%);
  
}

/* Стили для активной вкладки */
.tab-button.active {
  background: linear-gradient(180deg, rgba(127, 0, 254, 0.7) 0%, rgba(102, 0, 143, 0.7) 98.9%);
  border: 1px solid #6B51FF;
}

/* Эффекты при наведении */
.tab-button:hover {
  filter: brightness(1.1);
}

.tab-button:active {
  transform: scale(0.98);
}

/* Общие стили для кнопок */
.button-1,
.button-2,
.button-3 {
  position: relative;
  background-size: contain;
  background-repeat: no-repeat;
  cursor: pointer;
  flex-shrink: 0;
  margin: 0 5px;
}
/* Специфичные размеры кнопок */
.button-1,
.button-3 {
  width: 90px;
  height: 50px;
  background-image: url('/images/buttons/Group 255.png');
}

.button-2 {
  width: 170px;
  height: 46px;
  background-image: url('/images/buttons/Group 168.png');
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
</style>