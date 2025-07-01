<template>
  <div class="main-color">
     <div class="main-bg-container" :style="{ transform: `scale(${scaleFactor})` }"></div>
    <div class="tarakan-test"
         id="generateButton"
         @click="handleGenerateClick" 
         :style="{
           opacity: isLoading ? 0.7 : 1,
           cursor: isLoading ? 'wait' : 'pointer'
         }">
    </div>
    
    <div class="main-bg">
      <div class="labirint-bg"></div>
      <!-- Контейнеры для кнопок с индикаторами -->
      <div v-for="(btn, index) in winButtons" 
          :key="'btn-'+btn.id"
          class="button-win-container"
          :class="{
         'disabled': areWinButtonsDisabled,
         [`button-win-${index + 1}`]: true
       }">
        <!-- Меню внутри контейнера кнопки -->
    <div v-if="btn.menuVisible"
         class="win-menu"
         @mouseenter="cancelHideMenu(btn)"
         @mouseleave="hideMenu(btn)">
      <img :src="`/images/menus/Frame 575-${btn.id}.png`" alt="Menu" class="menu-image">
    </div>
        <!-- Основная кнопка -->
         <div class="button-win"
         @click="handleButtonClick(btn)"
         @mouseenter="showMenu(btn)"
         @mouseleave="scheduleHideMenu(btn)"
         :style="{ 
           backgroundImage: (btn.selected || (btn.hovered && !isRaceStarted)) 
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
      
      <!-- Нижняя панель -->
      <div class="panel-layer">
        <div class="button-1" id="Button-1" @click="handleButtonClick(1)"></div>
         <!-- Обновленная кнопка 2 с обработчиками меню -->
       <div class="button-2" 
       id="Button-2" 
       @click="toggleCenterMenu">
        </div>
        <div class="button-3" id="Button-3" @click="handleButtonClick(3)"></div>
       
        <!-- Меню для центральной кнопки -->
         <div v-if="centerMenuVisible"
        class="center-menu">
         
         <img src="/images/menus/center-buttom.png" alt="Center Menu" class="menu-image">
           <img src="/images/menus/group.png"  class="menu-image group-image">
           <img src="/images/menus/stavki-history.png"  class="menu-image stavki-history-image">
           <img src="/images/menus/stavki.png" alt="Stavki" class="menu-image stavki-image">
            <!-- Контейнер для кнопок меню -->
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

import { ref, computed, onMounted, onUnmounted, reactive } from 'vue';
const isLoading = ref(false); // Добавляем объявление isLoading
const bugProgress = ref([]); // Прогресс каждого таракана (0-1)
const bugSpeeds = ref([]); // Текущая скорость каждого таракана
const lastSpeedChange = ref([]); // Время последнего изменения скорости
const speedChangeIntervals = ref([]); // Интервалы между изменениями скорости
const finishZones = ref([]);// Карта соответствия координат финишных точек и ID кнопок
const buttonRefs = ref([]);
const centerMenuVisible = ref(false); // Добавляем состояние для центрального меню
const centerMenuTimer = ref(null); // Добавляем состояние для центрального меню
const areWinButtonsDisabled = ref(false);// Добавляем новое состояние для блокировки кнопок

const visibleMenus = computed(() => {
  return winButtons.value.filter(btn => btn.menuVisible && !isRaceStarted.value);
});
// Добавляем вычисляемое свойство для определения диагональных кнопок
const diagonalButtons = computed(() => {
  const diagonals = [];
  for (let i = 0; i < 7; i++) {
    diagonals.push(i * 7 + i); // Индексы диагональных кнопок: 0, 8, 16, 24, 32, 40, 48
  }
  return diagonals;
});
// Добавляем состояние для кнопок меню
const menuButtons = ref(
  Array.from({ length: 49 }, (_, index) => ({
    id: index,
    selected: false
  }))
);
// Переключение состояния кнопки
const toggleMenuButton = (btn) => {
  btn.selected = !btn.selected;
};
// Переключение центрального меню
// Переключение центрального меню (обновленная функция)
const toggleCenterMenu = () => {
  centerMenuVisible.value = !centerMenuVisible.value;
  areWinButtonsDisabled.value = centerMenuVisible.value;
  
  // Сбрасываем состояние кнопок при закрытии меню
  if (!centerMenuVisible.value) {
    menuButtons.value.forEach(btn => btn.selected = false);
  }
};
const scaleFactor = ref(1);

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
});

onUnmounted(() => {
  window.removeEventListener('resize', updateScale);
});

// Функция для получения позиции меню
const getMenuPosition = (btn) => {
  const buttonRef = buttonRefs.value[btn.id];
  if (!buttonRef) return {};
  const rect = buttonRef.getBoundingClientRect();
  return {
    left: `${rect.left + window.scrollX}px`,
    bottom: `${window.innerHeight - rect.top + window.scrollY + 10}px`,
    transform: 'translateX(-50%)'
  };
};


const winButtons = ref([
  { id: 7, occupied: false, hovered: false, top: 687, right: 4, bluePoint: [360, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 6, occupied: false, hovered: false, top: 687, right: 59, bluePoint: [305, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 5, occupied: false, hovered: false, top: 687, right: 114, bluePoint: [250, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 4, occupied: false, hovered: false, top: 687, right: 169, bluePoint: [195, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 3, occupied: false, hovered: false, top: 687, right: 224, bluePoint: [140, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 2, occupied: false, hovered: false, top: 687, right: 279, bluePoint: [85, 687], menuVisible: false, menuTimer: null, selected: false },
  { id: 1, occupied: false, hovered: false, top: 687, right: 334, bluePoint: [30, 687], menuVisible: false, menuTimer: null, selected: false },
]);


// Состояние для отслеживания занятых финишей
const occupiedFinishes = ref([]);

// Обработчик клика
// Обработчик клика по кнопке
const handleButtonClick = (btn) => {
  if (isRaceStarted.value || areWinButtonsDisabled.value) return;
  
  // Если кнопка уже выбрана - снимаем выделение
  if (btn.selected) {
    btn.selected = false;
    btn.menuVisible = false;
    return;
  }
  
  // Снимаем выделение со всех кнопок
  winButtons.value.forEach(b => {
    b.selected = false;
    b.menuVisible = false;
  });
  
  // Выделяем текущую кнопку
  btn.selected = true;
  btn.menuVisible = true;
};


const paths = ref([]); // Инициализируем ПЕРЕД bugs
const bugs = ref([]);
const animationFrame = ref(null);

// Очистка анимации при уничтожении компонента
onUnmounted(() => {
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
});

// Функция генерации путей
const generatePaths = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/generate-paths', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        bug_count: 7,
        duration: 10000,
        max_moves: 200,
        include_grid: true // Запрашиваем информацию о сетке
      }),
    });

    if (!response.ok) {
      const text = await response.text();
      throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}...`);
    }

      console.log("Status:", response.status);
    const text = await response.text();
    console.log("Response:", text);

    const data = JSON.parse(text);

    
    return {
      paths: data.paths,
      results: data.results,
      grid: data.grid, // Добавляем информацию о сетке
      finishPoints: data.grid.finish // Добавляем финишные точки
    };

    
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
};
// Функции для управления меню
const showMenu = (btn) => {
  if (isRaceStarted.value || btn.selected) return;
  
  btn.hovered = true;
  btn.menuVisible = true;
  if (btn.menuTimer) {
    clearTimeout(btn.menuTimer);
    btn.menuTimer = null;
  }
};

const scheduleHideMenu = (btn) => {
  // Не скрываем если кнопка выбрана
  if (btn.selected) return;
  
  btn.menuTimer = setTimeout(() => {
    hideMenu(btn);
  }, 0.1); // Уменьшена задержка скрытия
};

const hideMenu = (btn) => {
  // Не скрываем если кнопка выбрана
  if (btn.selected) return;
  
  btn.menuVisible = false;
  btn.hovered = false;
  if (btn.menuTimer) {
    clearTimeout(btn.menuTimer);
    btn.menuTimer = null;
  }
};

const cancelHideMenu = (btn) => {
  if (btn.menuTimer) {
    clearTimeout(btn.menuTimer);
    btn.menuTimer = null;
  }
};

// Обработчик клика по кнопке генерации
const handleGenerateClick = async () => {
  try {
    areWinButtonsDisabled.value = false; // Снимаем блокировку при новой генерации
    isRaceStarted.value = false; // Добавьте эту строку
    isLoading.value = true;
     // Сбрасываем все кнопки
    winButtons.value.forEach(btn => {
      btn.occupied = false;
      btn.menuVisible = false;
      btn.hovered = false;
      btn.selected = false;
      if (btn.menuTimer) {
        clearTimeout(btn.menuTimer);
        btn.menuTimer = null;
      }
    });

    winButtons.value.forEach(btn => btn.occupied = false);
    occupiedFinishes.value = [];
    bugProgress.value = [];
    bugSpeeds.value = [];
    lastSpeedChange.value = [];
    speedChangeIntervals.value = [];
    
    const data = await generatePaths();

    winButtons.value.forEach(btn => {
      btn.occupied = false;
      btn.menuVisible = false;
      if (btn.menuTimer) {
        clearTimeout(btn.menuTimer);
        btn.menuTimer = null;
      }
    });
    // Убедимся, что grid существует в ответе
    if (!data.grid) {
      throw new Error('Grid configuration is missing in response');
    }
    
    const cellSize = data.grid.cell_size;
    const offsetX = data.grid.offset_x;
    const totalWidth = data.grid.cols * cellSize;
    const zoneWidth = totalWidth / 7;

    finishZones.value = Array.from({ length: 7 }, (_, i) => ({
      minX: i * zoneWidth + offsetX,
      maxX: (i + 1) * zoneWidth + offsetX,
      buttonId: i + 1
    }));
    
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
        // Добавляем фазы движения
        phase: 'racing', // 'racing', 'to_blue_point', 'finished'
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

const isRaceStarted = ref(false);

// Функция анимации
const startAnimation = () => {
  isRaceStarted.value = true; // Добавьте эту строку
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
      
      if (bug.phase === 'racing') {
        if (timestamp - lastSpeedChange.value[index] > speedChangeIntervals.value[index]) {
          bugSpeeds.value[index] = 0.0004 + Math.random() * 0.0004;
          lastSpeedChange.value[index] = timestamp;
          speedChangeIntervals.value[index] = 500 + Math.random() * 1500;
        }
        
        bugProgress.value[index] += bugSpeeds.value[index] * (safeDeltaTime / 16);
        bugProgress.value[index] = Math.max(0, Math.min(bugProgress.value[index], 1));
        
        // Обновление позиции
        const totalSteps = bug.path.length;
        const currentIndex = Math.floor(bugProgress.value[index] * (totalSteps - 1));
        const safeIndex = Math.max(0, Math.min(currentIndex, totalSteps - 1));
        const point = bug.path[safeIndex];
        
        if (Array.isArray(point) && point.length >= 2) {
          bug.position = [point[0], point[1]];
        } else {
          console.error(`Invalid path point for bug ${index}`, point);
          bug.finished = true;
        }
        
        // Проверка достижения финиша
        if (bugProgress.value[index] >= 1 && !bug.finished) {
          const bugX = bug.position[0];
          const zone = finishZones.value.find(zone => 
            bugX >= zone.minX && bugX < zone.maxX
          );
          
          if (zone) {
            bug.targetButtonId = zone.buttonId;
            bug.phase = 'to_blue_point'; // Переходим ко второй фазе
            bug.bluePointProgress = 0;
          } else {
            bug.finished = true;
          }
        }
      }
      // Фаза движения к синей точке
      else if (bug.phase === 'to_blue_point') {
        // Увеличиваем прогресс движения к синей точке
        bug.bluePointProgress += 0.2 * (safeDeltaTime / 16);
        
        // Находим целевую синюю точку
        const button = winButtons.value.find(b => b.id === bug.targetButtonId);
        if (button) {
          const [targetX, targetY] = button.bluePoint;
          
          // Интерполируем позицию
          bug.position[0] = bug.position[0] + (targetX - bug.position[0]) * bug.bluePointProgress;
          bug.position[1] = bug.position[1] + (targetY - bug.position[1]) * bug.bluePointProgress;
          
          // Проверка достижения синей точки
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
onMounted(() => {
  let lastDevicePixelRatio = window.devicePixelRatio;
  
  const updateZoomFactor = () => {
    const currentDevicePixelRatio = window.devicePixelRatio;
    if (currentDevicePixelRatio !== lastDevicePixelRatio) {
      lastDevicePixelRatio = currentDevicePixelRatio;
      document.documentElement.style.setProperty(
        '--zoom-factor', 
        currentDevicePixelRatio
      );
    }
  };
  
  // Инициализация
  updateZoomFactor();
  
  // Следим за изменениями масштаба
  window.addEventListener('resize', updateZoomFactor);
  
  // Для браузеров, которые поддерживают событие zoom
  window.addEventListener('wheel', (e) => {
    if (e.ctrlKey) updateZoomFactor();
  });
  
  onUnmounted(() => {
    window.removeEventListener('resize', updateZoomFactor);
    window.removeEventListener('wheel', updateZoomFactor);
  });
});
</script>


<style scoped>
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
.menu-buttons-container {
  
  position: absolute;
  margin-top: -62.5%; /* Регулируйте по необходимости */
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
  border: 2px solid rgb(255, 255, 255) !important;
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
    top: 43%; /* Корректировка позиции */
  }
}

@media (max-width: 480px) {
  .menu-buttons-container {
    width: 170px;
    height: 150px;
    top: 41%; /* Дополнительная корректировка */
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

.button-win-container.disabled .win-menu {
  display: none !important;
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
  position: absolute;
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
  position: absolute;
  width: 32px;
  height: 38px;
  transform: translate(-50%, -50%); /* Центрирование */
  z-index: 2;
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
@media (max-width: 768px) {
  .button-win-1,.button-win-2,.button-win-3,.button-win-4,.button-win-5.button-win-6.button-win-7 {
    transform: scale(0.9); /* Уменьшаем размер контейнера */
    
  }
}
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
  margin-top: -1%; /* Поднимаем изображение ближе к основному */
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
/* История ставок справа */
.stavki-history-image {
  background-image: url('/images/menus/stavki-history.png');
  position: absolute;
  width: 240px; /* Размеры по вашему макету */
  height: 150px;  /* Размеры по вашему макету */
  top: -163%;       /* Позиционирование сверху */
  right: 0px;  /* Позиционирование справа */
  z-index: 1;
  
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
  .stavki-history-image{
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
  .stavki-history-image{
    left: 26%;
    margin-top: 8.5%;
    max-width: 240px;
    transform: translateX(-60%) scale(0.8);
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
.win-menu {
  position: absolute;
  bottom: calc(100% + 0px); /* 10px над кнопкой */
  left: 50%;
   transform: translateX(-50%);
  z-index: 4;
  height: 225px;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  cursor: default;
  transition: opacity 0.2s ease;
  pointer-events: auto;
}

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

/* Обновленные медиа-запросы */
@media (max-width: 768px) {
  .win-menu {
    transform: translate(-50%, -50%) scale(0.9);
  }
}


/* Адаптация для маленьких экранов */
@media (max-width: 768px) {
  .win-menu {
    width: 300px;
    height: 180px;
  }
}

/* Для вертикальной ориентации */
@media (max-height: 700px) {
  .win-menu {
    bottom: 28vh; /* Корректируем позицию для маленьких экранов */
    transform: translateX(-50%) scale(0.9);
  }
}
@media (max-width: 480px) {
  .win-menu {
    transform: translate(-50%, -50%) scale(0.8);
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
