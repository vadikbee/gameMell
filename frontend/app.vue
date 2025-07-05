<template>
  <div class="main-color">
   
 <!-- История ставок в основном интерфейсе -->
  <HistoryBets 
    v-if="historyBetsVisible && !historyBetsInsideCenter" 
    :isCenterMenuOpen="centerMenuVisible" 
    class="outside-center"
  />
    <!-- Контейнер для масштабирования фона -->
    <div class="main-bg-container" :style="{ transform: `scale(${scaleFactor})` }"></div>
    
    <!-- Кнопка генерации тараканов...б.б. -->
    <div class="tarakan-test"
         id="generateButton"
         @click="handleGenerateClick" 
         :style="{
           opacity: isLoading ? 0.7 : 1,
           cursor: isLoading ? 'wait' : 'pointer'
         }">
    </div>
    
    <!-- Основной игровой контейнер -->
    <div class="main-bg">
       <!-- Центральное фиксированное меню -->
  <div v-if="centerWinMenuVisible" class="win-menu-center">
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
      <!-- Добавляем изображение таракана -->
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
      <div v-for="(btn, index) in winButtons" 
          :key="'btn-'+btn.id"
          class="button-win-container"
          :class="{
            'disabled': areWinButtonsDisabled,
            [`button-win-${index + 1}`]: true
          }">
          
        
        
        <!-- Основная кнопка победы -->
        <div class="button-win"
             @click="handleButtonClick(btn)"
             :style="{ 
               backgroundImage: btn.selected 
                 ? `url('/images/buttons/knopka-win.png')` 
                 : `url('/images/buttons/Property 1=Default.png')`,
               cursor: isRaceStarted ? 'default' : 'pointer'
             }">
        </div>
       
        <!-- Индикатор победы -->
        <div v-if="btn.occupied" 
             class="win-indicator"
             :style="{ backgroundImage: `url('/images/buttons/Property 1=Variant2.png')` }">
        </div>
      </div>
      
      <!-- Тараканы -->
      <div v-for="bug in bugs" 
           :key="'bug-'+bug.id"
           class="tarakan"
           :style="{
             backgroundImage: `url('/images/tarakani/Property 1=Default (${bug.id + 1}).png')`,
             left: `${bug.position[0]}px`,
             top: `${bug.position[1]}px`
           }">
      </div>
      
      <!-- Нижняя панель управления -->
      <div class="panel-layer">
        <!-- Кнопки управления -->
        <div class="button-1" id="Button-1" @click="handleButtonClick(1)"></div>
        <div class="button-2" id="Button-2" @click="toggleCenterMenu"></div>
        <div class="button-3" id="Button-3" @click="toggleHistoryBets"></div>
       
        <!-- Центральное меню -->
        <div v-if="centerMenuVisible" class="center-menu">
          <img src="/images/menus/center-buttom.png" alt="Center Menu" class="menu-image">
          <img src="/images/menus/group.png" class="menu-image group-image">
                      <HistoryBets 
              v-if="historyBetsVisible && historyBetsInsideCenter" 
              :isCenterMenuOpen="centerMenuVisible" 
              :insideCenter="true"
              class="inside-center"
            />
          <!-- Меню ставок -->
          <div class="menu-stavki"> 
          <img src="/images/menus/stavki.png" alt="Stavki" class="menu-image stavki-image">
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
            <div 
              v-for="btn in menuButtons" 
              :key="btn.id"
              class="menu-button"
              :class="{ 
                selected: btn.selected,
                diagonal: diagonalButtons.includes(btn.id) 
              }"
              @click="toggleMenuButton(btn)"
            ></div>
          </div>
        </div>
        
        <!-- Верхняя панель баланса -->
        <div class="panel-up">
          <div class="balance-container">
            <div class="balance-text">Balance</div>
            <div class="button-balans">228</div>
          </div>
          <div class="icon-button">
            <div class="icon-1"></div>
          </div>
        </div>
      </div> 
    </div>
     
  </div>
  
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import HistoryBets from './HistoryBets.vue';
import io from 'socket.io-client';

const socket = io('https://your-websocket-server.com');
// Добавьте состояние для истории ставок
const historyBetsVisible = ref(false);
// ==============================
// СОСТОЯНИЯ ПРИЛОЖЕНИЯ
// ==============================
const isLoading = ref(false);
const scaleFactor = ref(1);
const isRaceStarted = ref(false);
const areWinButtonsDisabled = ref(false);
const centerMenuVisible = ref(false);
// Добавьте эти переменные
// Новые состояния для взаимодействия с тараканами в меню
const hoveredBugIndex = ref(null);
const clickedBugIndex = ref(null)
// Новое состояние для центрального меню
const centerWinMenuVisible = ref(false);
const activeWinMenuId = ref(null);

const historyBetsInsideCenter = ref(false);
const betHistory = ref([]); // ИСТОРИЯ СТАВОК
const currentBet = ref(0);
const minBet = 0;
const maxBet = 10000;
const betStep = 1;
// Добавлено: переменные для управления зажатием кнопок
const actionInterval = ref(null);
const actionTimeout = ref(null);
const speedUpInterval = ref(null); // Добавлено для интервала ускорения
const actionSpeed = ref(300); // начальная скорость в ms

// Прогресс и состояние тараканов
const bugProgress = ref([]);
const bugSpeeds = ref([]);
const lastSpeedChange = ref([]);
const speedChangeIntervals = ref([]);
const finishZones = ref([]);
const bugs = ref([]);
const animationFrame = ref(null);

// ==============================
// МЕТОДЫ ДЛЯ УПРАВЛЕНИЯ СТАВКАМИ
// ==============================
// Конфигурация расстояния
const gap = 0.5; // 1% расстояния между кнопками

const menuBugs = ref([
  { id: 1, left: 5, top: 25, width: 12, height: 50 },
  { id: 2, left: 18 + gap, top: 25, width: 12, height: 50 },
  { id: 3, left: 31 + gap*2, top: 25, width: 12, height: 50 },
  { id: 4, left: 44 + gap*3, top: 25, width: 12, height: 50 },
  { id: 5, left: 57 + gap*4, top: 25, width: 12, height: 50 },
  { id: 6, left: 70 + gap*5, top: 25, width: 12, height: 50 },
  { id: 7, left: 83 + gap*6, top: 25, width: 12, height: 50 }
]);
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
// Функция для удвоения ставки

onMounted(() => {
  socket.on('bet-update', (newBet) => {
    if (historyBetsVisible.value) {
      bets.value.unshift(newBet);
      if (bets.value.length > 10) bets.value.pop();
    }
  });
});

onUnmounted(() => {
  socket.disconnect();
});
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
    historyBetsVisible.value = !historyBetsVisible.value;
    historyBetsInsideCenter.value = true;
  } else {
    // Закрываем историю внутри центра при открытии внешней
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
const diagonalButtons = computed(() => {
  const diagonals = [];
  for (let i = 0; i < 7; i++) {
    diagonals.push(i * 7 + i);
  }
  return diagonals;
});

// ==============================
// МЕТОДЫ УПРАВЛЕНИЯ ИНТЕРФЕЙСОМ
// ==============================
// Переключение кнопки меню
const toggleMenuButton = (btn) => {
  btn.selected = !btn.selected;
};



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
  if (isRaceStarted.value || areWinButtonsDisabled.value) return;
  
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

// Запуск генерации тараканов
const handleGenerateClick = async () => {
  try {
    areWinButtonsDisabled.value = false;
    isRaceStarted.value = false;
    isLoading.value = true;
    
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
      
      return {
        id: index,
        position: [...path[0]],
        path: path,
        finished: false,
        phase: 'racing',
        targetButtonId: null,
        bluePointProgress: 0
      };
    });
    
    startAnimation();
  } catch (error) {
    console.error('Ошибка генерации путей:', error);
  } finally {
    isLoading.value = false;
  }
}

// Запуск анимации движения
const startAnimation = () => {
  console.log('Starting animation...')
  isRaceStarted.value = true;
  let lastTimestamp = performance.now();
  
  winButtons.value.forEach(btn => {
    btn.menuVisible = false;
    btn.selected = false;
  });
  
  const animate = (timestamp) => {
    const deltaTime = timestamp - lastTimestamp;
    lastTimestamp = timestamp;
    const safeDeltaTime = Math.min(deltaTime, 100) * 0.25;
    
    let activeBugs = 0;
    
    bugs.value.forEach((bug, index) => {
      if (bug.finished) return;
      activeBugs++;
      
      // Фаза гонки
      if (bug.phase === 'racing') {
        // Обновление скорости
        if (timestamp - lastSpeedChange.value[index] > speedChangeIntervals.value[index]) {
          bugSpeeds.value[index] = 0.0004 + Math.random() * 0.0004;
          lastSpeedChange.value[index] = timestamp;
          speedChangeIntervals.value[index] = 500 + Math.random() * 1500;
        }
        
        // Обновление позиции
        bugProgress.value[index] += bugSpeeds.value[index] * (safeDeltaTime / 16);
        bugProgress.value[index] = Math.max(0, Math.min(bugProgress.value[index], 1));
        
        const totalSteps = bug.path.length;
        const currentIndex = Math.floor(bugProgress.value[index] * (totalSteps - 1));
        const safeIndex = Math.max(0, Math.min(currentIndex, totalSteps - 1));
        const point = bug.path[safeIndex];
        
        if (Array.isArray(point) && point.length >= 2) {
          bug.position = [point[0], point[1]];
        } else {
          console.error(`Invalid path point`, point);
          bug.finished = true;
        }
        
        // Проверка финиша
        if (bugProgress.value[index] >= 1 && !bug.finished) {
          const bugX = bug.position[0];
          const zone = finishZones.value.find(zone => 
            bugX >= zone.minX && bugX < zone.maxX
          );
          
          if (zone) {
            bug.targetButtonId = zone.buttonId;
            bug.phase = 'to_blue_point';
            bug.bluePointProgress = 0;
          } else {
            bug.finished = true;
          }
        }
      }
      
      // Фаза движения к точке финиша
      else if (bug.phase === 'to_blue_point') {
        bug.bluePointProgress += 0.2 * (safeDeltaTime / 16);
        
        const button = winButtons.value.find(b => b.id === bug.targetButtonId);
        if (button) {
          const [targetX, targetY] = button.bluePoint;
          bug.position[0] = bug.position[0] + (targetX - bug.position[0]) * bug.bluePointProgress;
          bug.position[1] = bug.position[1] + (targetY - bug.position[1]) * bug.bluePointProgress;
          
          if (bug.bluePointProgress >= 1) {
            bug.finished = true;
            button.occupied = true;
          }
        } else {
          bug.finished = true;
        }
      }
    });
    
    if (activeBugs > 0) {
      animationFrame.value = requestAnimationFrame(animate);
    }
  };
  
  animationFrame.value = requestAnimationFrame(animate);
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
  updateScale();
  window.addEventListener('resize', updateScale);
  
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
  window.removeEventListener('resize', updateScale);
  if (animationFrame.value) cancelAnimationFrame(animationFrame.value);
});
</script>


<style scoped>
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
/* Позиция внутри центрального меню */
.history-bets.inside-center {
  position: absolute;
  top: -163%;
  margin-left: -5%;
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
  left: -19.2%;
  z-index: 9;
  padding: 0;
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
  opacity: 0.6;
  cursor: default !important;
  pointer-events: none;
  filter: grayscale(70%);
}
/* Отключаем эффекты при блокировке */
.button-win-container.disabled .button-win {
  cursor: default !important;
  pointer-events: none;
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
  margin-top: -795px; /* Фиксированная позиция вверху экрана */
  z-index: 4;
  background: #000000;
  /*border: 1px solid red !important; /* Для отладки */
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
    
filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.8))
          drop-shadow(0 0 6px rgba(255, 255, 255, 0.5));
 
/* Анимация пульсации свечения */
  animation: glow-pulse 2s infinite alternate;          
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
  transition: all 0.3s ease;
}
/* Адаптация для маленьких экранов */


@media (max-width: 390px) {
  .button-win-1,.button-win-2,.button-win-3,.button-win-4,.button-win-5.button-win-6.button-win-7 {
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
  background-image: url('/images/buttons/Group 256.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  height: 50px;
  cursor: pointer;
  z-index: 3;
  top: 22px;
  left: 8px;
}
/* Корректируем позиционирование кнопки */
.button-2 {
  position: absolute;
  background-image: url('/images/buttons/Group 168.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 170px;
  height: 100px;
  cursor: pointer;
  z-index: 3;
  top: 22px;
  right: -110px; /* Исправленное позиционирование */
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
/* Группа слева - точное позиционирование */
.group-image {
  position: absolute;
  background-image: url('/images/menus/group.png');
  width: 119px;
  height: 151px;
  top: -165%; /* Точное позиционирование сверху */
  left: 0px; /* Точное позиционирование слева */
  z-index: 3;
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
.button-1:hover, .button-2:hover, .button-3:hover,.tarakan-test {
  filter: 
    brightness(1.1) /* Увеличение яркости (первое число - размытие, второе - прозрачность).*/
    drop-shadow(6 6 8px rgba(178, 56, 58, 0.8)); 
}

/* Эффект при нажатии */
.button-1:active, .button-2:active,.tarakan-test {
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
  opacity: 0.6;
  filter: grayscale(70%);
}
/* *****************************************ТЕСТИРОВАНИЕ**********************************/
.tarakan-test {
  background-image: url('/images/tarakani/Property 1=Default (1).png');
  position: fixed; /* Фиксированная позиция относительно окна */
  background-size: contain;
  background-repeat: no-repeat;
  width: 20vw; /* Относительная ширина */
  height: 10vh; /* Относительная высота */
  top: 15vh; /* Позиционирование по вертикали */
  left: 70vw; /* Позиционирование по горизонтали */
  z-index: 3;
  cursor: pointer;
  transform: 
    scale(0.98) 
    rotate(180deg)
    translateZ(0); /* Аппаратное ускорение */
  filter: brightness(0.95);
  transform-origin: center;
  will-change: transform; /* Оптимизация анимации */
}
/* Добавьте этот стиль для индикации загрузки */
.tarakan-test {
  transition: opacity 0.3s ease;
}

/* *****************************************ТЕСТИРОВАНИЕ**********************************/

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
/* Для кнопок добавляем transform для улучшения производительности */
.button-1,
.button-2,
.button-3 {
  transform: translateZ(0);
  will-change: transform;
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
/* Дополнительный стиль для предотвращения мерцания */
.button-2 {
  position: relative;
  z-index: 4;
}
</style>
