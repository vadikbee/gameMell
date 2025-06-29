<template>
  <div class="main-color">
    <div class="tarakan-test"
         id="generateButton"
         @click="handleGenerateClick" 
         :style="{
           opacity: isLoading ? 0.7 : 1,
           cursor: isLoading ? 'wait' : 'pointer'
         }">
    </div>
    
    <div class="main-bg">
      <!-- Контейнеры для кнопок с индикаторами -->
      <div v-for="(btn, index) in winButtons" 
           :key="btn.id"
           class="button-win-container"
           :class="`button-win-${index + 1}`">
        
        <!-- Основная кнопка -->
        <div class="button-win"
             @click="handleWinClick(btn.id)"
             :style="{ backgroundImage: `url('/images/buttons/Property 1=Default.png')` }">
        </div>
        
        <!-- Индикатор победы -->
        <div v-if="btn.occupied" 
             class="win-indicator"
             :style="{ backgroundImage: `url('/images/buttons/Property 1=Variant2.png')` }">
        </div>
      </div>
      
      <div class="finish-line"></div>

      <!-- Тараканы -->
      <div v-for="bug in bugs" 
           :key="bug.id"
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
        <div class="button-2" id="Button-2" @click="handleButtonClick(2)"></div>
        <div class="button-3" id="Button-3" @click="handleButtonClick(3)"></div>

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
import { ref, onMounted, onUnmounted, reactive } from 'vue';
// Добавляем переменные для управления скоростью
const bugProgress = ref([]); // Прогресс каждого таракана (0-1)
const bugSpeeds = ref([]); // Текущая скорость каждого таракана
const lastSpeedChange = ref([]); // Время последнего изменения скорости
const speedChangeIntervals = ref([]); // Интервалы между изменениями скорости
// Карта соответствия координат финишных точек и ID кнопок
const finishZones = ref([]);

const isLoading = ref(false);
const winButtons = ref([
  { id: 7, occupied: false, top: 687, right: 4, bluePoint: [360, 687] },
  { id: 6, occupied: false, top: 687, right: 59, bluePoint: [305, 687] },
  { id: 5, occupied: false, top: 687, right: 114, bluePoint: [250, 687] },
  { id: 4, occupied: false, top: 687, right: 169, bluePoint: [195, 687] },
  { id: 3, occupied: false, top: 687, right: 224, bluePoint: [140, 687] },
  { id: 2, occupied: false, top: 687, right: 279, bluePoint: [85, 687] },
  { id: 1, occupied: false, top: 687, right: 334, bluePoint: [30, 687] },
]);

// Состояние для отслеживания занятых финишей
const occupiedFinishes = ref([]);

// Обработчик клика
const handleWinClick = (btnId) => {
  console.log(`Clicked win button ${btnId}`);
  // Ваша логика обработки клика
};
const handleButtonClick = (btnId) => {
  console.log(`Clicked button ${btnId}`);
  // Добавьте вашу логику здесь
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

// Обработчик клика по кнопке генерации
const handleGenerateClick = async () => {
  try {
    isLoading.value = true;
    winButtons.value.forEach(btn => btn.occupied = false);
    occupiedFinishes.value = [];
    bugProgress.value = [];
    bugSpeeds.value = [];
    lastSpeedChange.value = [];
    speedChangeIntervals.value = [];
    
    const data = await generatePaths();
    
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

// Функция анимации
const startAnimation = () => {
  let lastTimestamp = performance.now();
  
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

</script>


<style scoped>

.finish-line{
  /*border: 1px solid red !important;*/
  background-image: url('/images/2sloy/Group 419.png');
  background-size: contain;
  background-repeat: no-repeat;
  position: absolute;
  width: 383px; /* Предположительные размеры */
  height: 15px; 
  top: 634px; /* Пример позиции */
  left: 3.5px; /* Пример позиции */
  z-index: 3;
  
}
/* Стили для черной менюшки */
.panel-up {
  position: absolute;
  width: 390px;
  height: 43px;
  left: calc(50% - 390px/2);
  top: -695px; /* Фиксированная позиция вверху экрана */
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
  transition: left 0.1s linear, top 0.1s linear; /* Плавное движение */ /* Полупрозрачный фон */
}
.button-win {
  /* border: 1px solid red !important; /* Временная рамка */
  /* Остальные стили */
  background-image: url('/images/buttons/Property 1=Default.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: center;
  position: absolute;
  width: 52px;
  height: 64px;
     /* Новые координаты - более логичные значения */
  z-index: 3; 
    cursor: pointer;
  transition: all 0.3s ease;
}
/* Контейнер для кнопки и индикатора */
.button-win-container {
  position: absolute;
  width: 52px;
  height: 64px;
  z-index: 3;
}

/* Позиции контейнеров */
.button-win-1 { top: 687px; right: 4px; }
.button-win-2 { top: 687px; right: 59px; }
.button-win-3 { top: 687px; right: 114px; }
.button-win-4 { top: 687px; right: 169px; }
.button-win-5 { top: 687px; right: 224px; }
.button-win-6 { top: 687px; right: 279px; }
.button-win-7 { top: 687px; right: 334px; }

/* Базовая кнопка */
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
/* ОБНОВЛЕННЫЕ СТИЛИ ДЛЯ ЭЛЕМЕНТОВ БАЛАНСА *//* Текст "Balance" */
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
  /* Фиксированные размеры контейнера */
  width: 390px;
  min-height: 844px;
  height: 100vh;
  max-height: 844px;
  position: relative;
  overflow: hidden;
  margin: 0 auto;
  z-index: 1;
}
.button-1 {
  position: absolute;
  background-image: url('/images/buttons/Group 256.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 90px;
  height: 50px;
  cursor: pointer;
  z-index: 3;
  /* Точное позиционирование без трансформаций */
  top: 22px;
  left: 8px; /* Фиксированный отступ от левого края */
  
}
.button-2 {
   position: absolute;
  background-image: url('/images/buttons/Group 168.png');
  background-size: contain;
  background-repeat: no-repeat;
  width: 170px;
  height: 100px;
  cursor: pointer;
  z-index: 3;
  /* Точное позиционирование без трансформаций */
  top: 22px;
  right: 110px; /* Фиксированный отступ от правого края */
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
  width: 390px; /* Фиксированная ширина */
  height: 93px;
  left: 0; /* Прижимаем к левому краю */
  bottom: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, #000000 100%);
  z-index: 4;
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
    brightness(1.1) /* Увеличение яркости */
    drop-shadow(0 0 8px rgba(178, 56, 58, 0.8));
}

/* Эффект при нажатии */
.button-1:active, .button-2:active,.tarakan-test {
  transform: scale(0.98);
  filter: brightness(0.95);
}
/* Гарантируем, что контент будет поверх панели */
/* Адаптивность для маленьких экранов */
/* Адаптация для мобильных */

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

/* Адаптивность по высоте */
@media (max-height: 844px) {
  .main-bg {
    height: 100vh;
    max-height: none;
  }
}

</style>
