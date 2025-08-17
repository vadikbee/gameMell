<template>
  <div class="history-bets" :class="positionClass">
    <div class="history-header">
      <div class="history-title">{{ title }}</div>
    </div>
    
    <div class="bets-list">
      <div v-for="(bet, index) in formattedBets" :key="index" class="bet-item">
        <!-- Для ставок на секцию и обгон оставляем цветные индикаторы -->
        <div v-if="bet.type === 'trap' || bet.type === 'place' || bet.type === 'overtaking'" class="bet-colors">
          <div 
            v-for="(color, i) in getBetColors(bet)" 
            :key="i"
            class="color-indicator"
            :style="{ background: color }"
          ></div>
        </div>
        
        <div class="bet-info">
          <div class="bet-description">
            {{ getBetType(bet) }}
          </div>
          <div class="bet-amount">{{ bet.amount }}₽ × {{ getMultiplier(bet) }} = {{ calculateTotal(bet) }}₽</div>
        </div>
        <div class="bet-time">{{ formatTime(bet.time) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const props = defineProps({
  bets: Array,
  isCenterMenuOpen: Boolean,
  insideCenter: Boolean,
  title: String,
  bugColors: Object
});

const bugColorMap = {
  1: '#FFFF00',
  2: '#FFA500',
  3: '#8B0000',
  4: '#0000FF',
  5: '#FF0000',
  6: '#800080',
  7: '#00FF00'
};

const positionClass = computed(() => {
  if (props.insideCenter) return 'inside-center';
  return props.isCenterMenuOpen ? 'top-right' : 'bottom-right';
});

const formatTime = (timeString) => {
  if (!timeString) return '';
  return timeString.split(':').slice(0, 2).join(':');
};

const getBetColors = (bet) => {
  // Для ставок на позицию
  if (bet.type === 'position') {
    return [bugColorMap[bet.bugId]];
  }
  // Для ставок на секцию
  if (bet.type === 'section') {
    return bet.selection.map(id => bugColorMap[id]);
  }
  
  // Для ставок на обгон
  if (bet.type === 'overtaking') {
    return [
      bugColorMap[bet.overtaker],
      bugColorMap[bet.overtaken]
    ];
  }

  if (bet.type === 'trap') {
    return bet.selection.map(id => bugColorMap[id] || '#FFFFFF');
  }
  
  if (bet.type === 'place' && bet.selection && bet.selection.length >= 2) {
    return [
      bugColorMap[bet.selection[0]],
      bugColorMap[bet.selection[1]]
    ];
  }
  
  const colors = [];
  if (bet.color) {
    const matches = bet.color.match(/#[0-9A-Fa-f]{6}/g);
    if (matches) colors.push(...matches.slice(0, 2));
  }
  
  return colors.length > 0 ? colors : ['#FFFFFF'];
};

const getMultiplier = (bet) => {
  return bet.type === 'position' ? 2.23 : 1;
}

const calculateTotal = (bet) => {
  if (bet.type === 'position') {
    return bet.amount * 2.23;
  }
  
  return bet.amount;
};


const getBetType = (bet) => {
  // Для ставок на обгон
  if (bet.type === 'overtaking') {
    return `${getBugName(bet.overtaker)} ${t('or')} ${getBugName(bet.overtaken)}`;
  }

  // Для ставок на позицию
  if (bet.type === 'position' || bet.type === 'win') {
    return `${getBugName(bet.bugId)} - ${bet.position} ${t('place')}`;
  }

  if (bet.type === 'place' && bet.selection && bet.selection.length >= 2) {
    return `${getBugName(bet.selection[0])} ${t('or')} ${getBugName(bet.selection[1])}`;
  }
  
  if (bet.type === 'trap') {
    return `${t('section')} ${bet.trapId} ${t('section_bet')} (${bet.selection.length} ${t('bugs')})`;
  }
  
  // Для ставок на секцию
  if (bet.type === 'section') {
    return `${t('section')} ${bet.trapId} ${t('section_bet')} (${bet.selection.length} ${t('bugs')})`;
  }

  if (bet.bugId && bet.position) {
    return `${getBugName(bet.bugId)} - ${t('place')} ${bet.position}`;
  }

  return t('position_bet');
};

const getBugName = (id) => {
  if (!id) return t('unknown');
  
  const numId = parseInt(id);
  if (isNaN(numId)) return t('unknown');
  
  const names = {
    1: t('yellow'),
    2: t('orange'),
    3: t('dark_orange'),
    4: t('blue'),
    5: t('red'),
    6: t('purple'),
    7: t('green')
  };
  
  return names[numId] || t('unknown');
};

const formattedBets = computed(() => {
  return (props.bets || []).slice(0, 10).map(item => {
    const bet = item.data || item;
    
    // Преобразование ставок на обгон
    if (bet.type === 'overtaking' || 
        (bet.type === 'trap' && bet.selection?.length === 2)) {
      return {
        ...bet,
        type: 'overtaking',
        overtaker: bet.overtaker || bet.selection?.[0],
        overtaken: bet.overtaken || bet.selection?.[1]
      };
    }
    
    // Нормализация данных для ставок на позицию
    if (bet.type === 'position' || bet.type === 'win' || bet.type === 'place') {
      let bugId, position;
      
      // Обработка нового формата
      if (bet.bugId !== undefined) {
        bugId = bet.bugId;
        position = bet.position;
      }
      // Обработка старого формата
      else if (bet.selection && bet.selection.length >= 2) {
        bugId = bet.selection[0];
        position = bet.selection[1];
      }
      // Обработка выигрышных ставок
      else if (bet.type === 'win' && bet.selection && bet.selection.length === 1) {
        bugId = bet.selection[0];
        position = 1;
      }
      
      return {
        ...bet,
        type: 'position',
        bugId: bugId,
        position: position
      };
    }
    
    // Конвертация ставок из серверного формата в клиентский
    if (bet.type === 'win' && bet.selection?.length === 1) {
      return {
        ...bet,
        type: 'position',
        bugId: bet.selection[0],
        position: 1
      };
    }
    else if (bet.type === 'place' && bet.selection?.length === 2) {
      return {
        ...bet,
        type: 'position',
        bugId: bet.selection[0],
        position: bet.selection[1]
      };
    }
    else if (bet.type === 'trap' && bet.selection?.length === 2) {
      return {
        ...bet,
        type: 'overtaking',
        overtaker: bet.selection[0],
        overtaken: bet.selection[1]
      };
    }
    
    // Нормализация данных ставки
    return {
      id: bet.id,
      type: bet.type,
      amount: bet.amount,
      time: bet.time,
      bugId: bet.bugId || (bet.selection?.[0]),
      position: bet.position || (bet.selection?.[1]),
      overtaker: bet.overtaker,
      overtaken: bet.overtaken,
      trapId: bet.trapId,
      selection: bet.selection
    };
  });
});
</script>

<style scoped>
/* Стили остаются без изменений */
.history-bets {
  position: absolute;
  width: 236px;
  height: 150px;
  background: linear-gradient(180deg, rgba(30, 3, 30, 0.4) 0%, rgba(30, 3, 30, 0.4) 100%);
  border-radius: 8px;
  z-index: 10;
  padding: 5px;
  box-sizing: border-box;
  overflow: hidden;
}

.bottom-right {
  left: 36.15%;
  right: 2.31%;
  top: 63.03%;
  bottom: 19.19%;
}

.top-right {
  left: 36.15%;
  right: 2.31%;
  top: 0.15%;
  bottom: 82.07%;
}

.inside-center {
  position: relative;
  width: 100%;
  height: auto;
  margin-top: 20px;
}

.history-header {
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 10px;
  padding: 4px 18px;
  margin-bottom: 5px;
}

.history-title {
  font-family: 'Bahnschrift', sans-serif;
  font-weight: 700;
  font-size: 10px;
  color: #FFFFFF;
  text-transform: uppercase;
  text-align: center;
}

.bets-list {
  height: calc(100% - 30px);
  overflow-y: auto;
  padding-right: 5px;
}

.bet-item {
  display: flex;
  align-items: center;
  background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%);
  border-radius: 30px;
  padding: 5px 8px;
  margin-bottom: 4px;
  font-family: 'Bahnschrift', sans-serif;
  font-size: 9px;
}

.bet-colors {
  display: flex;
  flex-wrap: wrap;
  max-width: 60px;
  margin-right: 5px;
}

.color-indicator {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  border: 1px solid #FFFFFF;
  margin-right: 2px;
  margin-bottom: 2px;
}

.bet-info {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.bet-description {
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.2;
  margin-bottom: 2px;
}

.bet-amount {
  font-weight: 300;
  color: #FFFFFF;
  font-size: 8px;
}

.bet-time {
  font-weight: 300;
  color: #000000;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 30px;
  padding: 2px 5px;
  font-size: 8px;
  min-width: 30px;
  text-align: center;
}

@media (max-width: 768px) {
  .history-bets {
    transform: scale(0.85);
    transform-origin: top right;
  }
  
  .bottom-right {
    top: auto;
    bottom: 70px;
    left: auto;
    right: 10px;
  }
  
  .top-right {
    top: 70px;
    left: auto;
    right: 10px;
  }
}
</style>