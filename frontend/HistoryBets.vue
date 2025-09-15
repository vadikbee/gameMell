<template>
  <div class="history-bets" :class="positionClass">
    <div class="history-header">
      <div class="history-title">{{ title }}</div>
    </div>
    <div class="bets-list">
      <!-- Group iteration -->
      <div v-for="(group, groupIndex) in groupedBets" :key="groupIndex" class="bet-group">
        <!-- Main item for the group -->
        <div
          class="bet-item"
          :class="{'clickable': group.isGroup}"
          @click="group.isGroup ? toggleGroup(group.groupKey) : null"
        >
          <div class="bet-colors">
            <div
              v-for="(color, i) in getBetColors(group.displayBet)"
              :key="i"
              class="color-indicator"
              :style="{ background: color }"
            ></div>
          </div>
          <div class="bet-info">
            <div class="bet-description">
              <span v-if="group.isGroup" class="group-count">{{ group.bets.length }}x&nbsp;</span>
              {{ getBetType(group.displayBet) }}
            </div>
            <div class="bet-amount">
              {{ group.totalAmount }} x {{ getMultiplier(group.displayBet) }} = {{ calculateTotal(group) }}
            </div>
          </div>
          <div class="bet-time">{{ formatTime(group.time) }}</div>
          <div v-if="group.isGroup" :class="['arrow-icon', { 'expanded': group.isExpanded }]"></div>
        </div>

        <!-- Expandable details -->
        <div v-if="group.isGroup && group.isExpanded" class="expanded-details">
          <div v-for="bet in group.bets" :key="bet.id || Math.random()" class="bet-item bet-item-detail">
            <div class="bet-colors">
              <div v-for="(color, i) in getBetColors(bet)" :key="i" class="color-indicator" :style="{
              background: color }"></div>
            </div>
            <div class="bet-info">
              <div class="bet-description">{{ getBetType(bet) }}</div>
              <div class="bet-amount">{{ bet.amount }}</div>
            </div>
            <div class="bet-time">{{ formatTime(bet.timestamp) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  bets: Array,
  isCenterMenuOpen: Boolean,
  insideCenter: Boolean,
  title: String,
});

const expandedGroups = ref(new Set());

const toggleGroup = (groupKey) => {
  if (expandedGroups.value.has(groupKey)) {
    expandedGroups.value.delete(groupKey);
  } else {
    expandedGroups.value.add(groupKey);
  }
};

// --- Helper Functions ---
const bugColorMap = { 1: '#FFFF00', 2: '#FFA500', 3: '#8B0000', 4: '#0000FF', 5: '#FF0000', 6: '#800080', 7: '#00FF00' };

const positionClass = computed(() => {
  if (props.insideCenter) return 'inside-center';
  return props.isCenterMenuOpen ? 'top-right' : 'bottom-right';
});

const formatTime = (timeString) => {
  if (!timeString) return '';
  const date = new Date(timeString);
  if (isNaN(date)) return '';
  return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const getBugName = (id) => {
  const numId = parseInt(id, 10);
  const names = { 1: t('yellow'), 2: t('orange'), 3: t('dark_orange'), 4: t('blue'), 5: t('red'), 6: t('purple'), 7: t('green') };
  return names[numId] || t('unknown');
};

const getBetColors = (bet) => {
  if (!bet) return ['#FFFFFF'];
  switch (bet.type) {
    case 'position':
    case 'result':
    case 'win':
      return [bugColorMap[bet.bugId]];
    case 'overtaking':
      return [bugColorMap[bet.overtaker], bugColorMap[bet.overtaken]];
    case 'section':
    case 'trap':
      return bet.selection.map(id => bugColorMap[id]);
    default:
      return ['#FFFFFF'];
  }
};

const getBetType = (bet) => {
  if (!bet) return '';
  switch (bet.type) {
    case 'position':
    case 'result':
      return `${getBugName(bet.bugId)} - ${bet.position} ${t('place')}`;
    case 'overtaking':
      return `${getBugName(bet.overtaker)} ${t('or')} ${getBugName(bet.overtaken)}`;
    case 'trap':
    case 'section':
      return `${t('section')} ${bet.trapId} (${bet.selection.length} ${t('bugs')})`;
    default:
      return 'Неизвестная ставка';
  }
};

const getMultiplier = (bet) => bet?.coefficient || 1;

const calculateTotal = (group) => {
  if (!group || !group.displayBet) return 0;
  const multiplier = getMultiplier(group.displayBet);
  return (group.totalAmount * multiplier).toFixed(2);
};

const formattedBets = computed(() => {
  // Теперь, когда сервер присылает чистые данные, сложная обработка не нужна.
  // Мы просто проверяем, что массив существует, и берем последние 10 записей.
  return (props.bets || []).slice(0, 10);
});
const groupedBets = computed(() => {
  if (!formattedBets.value) return [];
  const groups = {};
  for (const bet of formattedBets.value) {
    const timeMinute = bet.timestamp ? new Date(bet.timestamp).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) : 'no-time';
    const baseKey = `${getBetType(bet)}-${bet.amount}`;
    const groupKey = `${baseKey}-${timeMinute}`;
    if (!groups[groupKey]) {
      groups[groupKey] = { groupKey, bets: [], totalAmount: 0, time: bet.timestamp };
    }
    groups[groupKey].bets.push(bet);
    groups[groupKey].totalAmount += bet.amount;
  }
  return Object.values(groups).map(group => {
    const isGroup = group.bets.length > 1;
    return { ...group, isGroup, groupKey: isGroup ? group.groupKey : null, displayBet: group.bets[0], isExpanded: expandedGroups.value.has(group.groupKey) };
  });
});

</script>

<style scoped>
/* Ваши оригинальные стили, с небольшими дополнениями */
.bet-group { display: flex; flex-direction: column; }
.bet-item.clickable { cursor: pointer; }
.arrow-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 10px; height: 10px; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>'); background-repeat: no-repeat; background-position: center; transition: transform 0.2s ease; }
.arrow-icon.expanded { transform: translateY(-50%) rotate(180deg); }
.expanded-details { padding-left: 15px; margin-top: 2px; }
.bet-item-detail { background: rgba(0, 0, 0, 0.2) !important; border-left: 2px solid rgba(255,255,255,0.3); padding-left: 10px !important; margin-bottom: 2px !important; }
.bet-description { display: flex; align-items: center; }
.group-count { font-weight: bold; }
.bet-info { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; }
.bet-description, .bet-amount { white-space: nowrap; }

/* --- ВАШИ ОРИГИНАЛЬНЫЕ СТИЛИ --- */
.history-bets { position: absolute; width: 236px; height: 150px; background: linear-gradient(180deg, rgba(30, 3, 30, 0.4) 0%, rgba(30, 3, 30, 0.4) 100%); border-radius: 8px; z-index: 10; padding: 5px; box-sizing: border-box; overflow: hidden; }
.bottom-right { left: 36.15%; right: 2.31%; top: 63.03%; bottom: 19.19%; }
.top-right { left: 36.15%; right: 2.31%; top: 0.15%; bottom: 82.07%; }
.inside-center { position: relative; width: 100%; height: auto; margin-top: 20px; }
.history-header { background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%); border-radius: 10px; padding: 4px 18px; margin-bottom: 5px; }
.history-title { font-family: 'Bahnschrift', sans-serif; font-weight: 700; font-size: 10px; color: #FFFFFF; text-transform: uppercase; text-align: center; }
.bets-list { height: calc(100% - 30px); overflow-y: auto; padding-right: 5px; }
.bet-item { display: flex; align-items: center; background: linear-gradient(180deg, rgba(5, 3, 30, 0.7) 0%, rgba(10, 3, 49, 0.7) 100%); border-radius: 30px; padding: 5px 8px; margin-bottom: 4px; font-family: 'Bahnschrift', sans-serif; font-size: 9px; color: white; position: relative; }
.bet-colors { display: flex; flex-wrap: wrap; max-width: 60px; margin-right: 5px; }
.color-indicator { width: 12px; height: 12px; border-radius: 2px; border: 1px solid #FFFFFF; margin-right: 2px; margin-bottom: 2px; }
.bet-amount { font-weight: 300; color: #FFFFFF; font-size: 8px; }
.bet-time { font-weight: 300; color: #000000; background: rgba(255, 255, 255, 0.7); border-radius: 30px; padding: 2px 5px; font-size: 8px; min-width: 30px; text-align: center; margin-left: auto; }
</style>