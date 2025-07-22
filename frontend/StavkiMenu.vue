<!-- StavkiMenu.vue -->
 <!--эта менюшка (дублирует меню в центральной кнопке для кнопок win)' -->
<template>
  <div class="menu-stavki">
    <img src="/images/menus/stavki.png" alt="Stavki" class="menu-image stavki-image">
    <div class="bet-controls-container">
      <img 
        src="/images/buttons/otmena.png" 
        alt="Otmena"
        class="otmena-button"
        @click="$emit('otmena-click')"
      >
      <img 
        src="/images/buttons/reset.png" 
        alt="Reset" 
        class="reset-button"
        @click="$emit('reset-click')"
      >
      
      <img 
        src="/images/menus/Group 164.png" 
        alt="Group 164" 
        class="group-164-button"
        @click="$emit('group164-click')"
      >
    </div>
    <div class="bet-counter-container">
      <div 
        class="bet-button minus" 
        @mousedown="$emit('decrement-start')"
        @mouseup="$emit('stop-action')"
        @mouseleave="$emit('stop-action')"
      ></div>
      <div class="bet-display">{{ currentBet }}</div>
      <div 
        class="bet-button plus" 
        @mousedown="$emit('increment-start')"
        @mouseup="$emit('stop-action')"
        @mouseleave="$emit('stop-action')"
      ></div>
    </div>
    <div class="stavki-buttons-container">
      <img 
        v-for="button in stavkiButtons"
        :key="button.id"
        :src="button.src"
        :alt="button.alt"
        class="stavki-button"
        @click="$emit('add-bet', button.amount)"
      >
      <img 
        src="/images/buttons/x2.png" 
        alt="x2"
        class="x2-button"
        @click="$emit('x2-click')"
      >
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentBet: Number,
  stavkiButtons: Array
});

defineEmits([
  'otmena-click',
  'reset-click',
  'group164-click',
  'decrement-start',
  'increment-start',
  'stop-action',
  'add-bet',
  'x2-click'
]);
</script>

<style scoped>
.menu-stavki {
  position: relative;
  width: 100%;
  max-width: 360px;
  margin: 0 auto;
  z-index: 10;
}

.bet-controls-container {
  position: relative;
}

.bet-counter-container {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  margin-top: -10.5%;
  left: 33.5%;
  transform: translateX(-50%);
  z-index: 12;
  width: 23%;
}

.stavki-buttons-container {
  position: absolute;
  display: flex;
  flex-direction: row;
  justify-content: center;
  gap: var(--stavki-gap, 10px);
  width: 89%;
  height: 0%;
  left: -22%;
  top: 92px;
  z-index: 9;
  padding: 0;
}

.group-164-button {
  position: absolute;
  margin-top: 85%;
  margin-left: 30%;
  z-index: 8;
  cursor: pointer;
  width: 112px;
  height: 68px;
  margin-top: -20%;
  margin-left: 75%;
  transform: translateX(-50%);
  transition: transform 0.3s ease, filter 0.3s ease;
}

.group-164-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.group-164-button:active {
  transform: translateX(-50%) scale(0.95);
}

/* Стили для изображения stavki */
.stavki-image {
  width: 95%;
  max-width: 370px;
  margin-top: -1%;
  object-fit: contain;
}

/* Стили для кнопок */
.otmena-button {
  position: absolute;
  z-index: 10;
  cursor: pointer;
  width: 25px;
  height: 25px;
  margin-top: -11%;
  transform: translateX(-50%);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  margin-left: 5%;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
}

.otmena-button:hover {
  transform: translateX(-50%) scale(1.05);
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

.reset-button {
  position: absolute;
  z-index: 10;
  cursor: pointer;
  width: 49px;
  height: 25px;
  margin-top: -10.8%;
  margin-left: 46%;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.reset-button:hover {
  filter: drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
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

.x2-button {
  position: relative;
  width: 56px;
  height: 40px;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  flex: none;
  margin-top: -13.5%;
  left: 26.4%;
  z-index: 9;
  flex-grow: 0;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.x2-button:hover {
  transform: scale(1.05);
  filter: brightness(1.1) drop-shadow(0 0 5px rgba(255, 255, 0, 0.8));
}

/* Стили для счетчика ставок */
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
    top: -373.5%;
    margin-top: 98%;
    left: 35%;
  }
  
  .bet-display {
    width: 80px;
    font-size: 17px;
    margin: 0 -18px;
  }
  
  .stavki-buttons-container {
    top: 404%;
    width: 85%;
    padding-left: 15px;
  }
  
  .stavki-button {
    margin-top: 0%;
  }
  
  .x2-button {
    width: 55px;
    height: 32px;
    left: 31%;
    margin-top: 11.1%;
  }
  
  .otmena-button {
    width: 25px;
    height: 25px;
    left: 0%;
  }
  
  .reset-button {
    width: 50px;
    height: 24px;
    top: 392%;
    left: 56%;
  }
  
  .group-164-button {
    width: 113px;
    top: 45%;
    left: 48.5%;
  }
}

/* Адаптация для маленьких экранов */
@media (max-width: 768px) {
  .stavki-buttons-container {
    top: 9%;
    left: -24%;
  }
   .reset-button {
    left: 2%;
    margin-top: -10.9%;
    transform: scale(0.95);
  }
  .otmena-button {
    
    left: 1%;
  }
   .x2-button {
    
    left: 30%;
    
  }
  .group-164-button {
    width: 113px;
    margin-top: -21%;
    left: 3%;
  }
  
}
@media (max-width: 480px) {
  .bet-display {
    width: 70px;
    height: 20px;
    font-size: 12px;
    
    
    
  }

  .bet-counter-container {
    top: 49%;
    border: 1px solid rgb(36, 223, 15) !important;
    margin-top: 0%;
    left: 35%;
  }
  .bet-button {
    width: 20px;
    height: 20px;
    margin-top: 0%;
    
  }
  
  .stavki-buttons-container {
    top: 10%;
    width: 75%;
    height: 30%;
    left: -19%;
    gap: 7px;
    border: 1px solid rgb(36, 223, 15) !important;
  }
  
  .stavki-button {
    width: 42px;
    height: 20px;
    
  }
  
  .x2-button {
    width: 45px;
    height: 32px;
    left: 30%;
    top: 10%;
    border: 1px solid rgb(36, 223, 15) !important;
  }
  
  .otmena-button {
    width: 24px;
    height: 24px;
    left: 1%;
    top: 382%;
    border: 1px solid rgb(36, 223, 15) !important;
  }
  
  .reset-button {
    width: 42px;
    height: 21px;
    margin-top: -11.5%;
    left: 1.5%;
    border: 1px solid rgb(36, 223, 15) !important;
  }
  
  .group-164-button {
    width: 30%;
    top: 41%;
    height: 58px;
  }
}
</style>