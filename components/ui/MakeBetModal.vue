<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal">
      <h2>Сделать ставку</h2>
      
      <div class="bet-amounts">
        <button 
          v-for="amount in [25, 50, 100, 150]" 
          :key="amount" 
          @click="addBet(amount)"
          class="bet-btn"
        >
          {{ amount }}
        </button>
      </div>
      
      <div class="bet-controls">
        <button @click="multiplyBet(2)">x2</button>
        <button @click="undoBet">Отменить</button>
        <button @click="cancelAll">Сбросить всё</button>
      </div>
      
      <div class="current-bet">
        Текущая ставка: {{ currentBetAmount }} {{ currency }}
      </div>
      
      <button class="confirm-btn" @click="confirmBet">Подтвердить ставку</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const currentBetAmount = ref(0);
const currency = ref('USD');

function addBet(amount) {
  currentBetAmount.value += amount;
}

function multiplyBet(factor) {
  currentBetAmount.value *= factor;
}

function undoBet() {
  // Логика отмены последнего действия
}

function cancelAll() {
  currentBetAmount.value = 0;
}

function confirmBet() {
  // Отправка ставки на сервер
  console.log(`Ставка ${currentBetAmount.value} подтверждена`);
  emit('close');
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: #1e1b3a;
  padding: 20px;
  border-radius: 15px;
  width: 90%;
  max-width: 500px;
  border: 2px solid #5e35b1;
}

.bet-amounts {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
  margin: 20px 0;
}

.bet-btn {
  padding: 15px;
  background: #5e35b1;
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.bet-btn:hover {
  background: #7e57c2;
  transform: scale(1.05);
}

.bet-controls {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.bet-controls button {
  padding: 10px 15px;
  background: #3949ab;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
}

.current-bet {
  text-align: center;
  font-size: 20px;
  margin: 20px 0;
  padding: 10px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 5px;
}

.confirm-btn {
  width: 100%;
  padding: 15px;
  background: linear-gradient(90deg, #00c853, #64dd17);
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.confirm-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 200, 83, 0.4);
}
</style>