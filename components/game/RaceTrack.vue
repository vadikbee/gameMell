<template>
  <div class="race-track">
    <!-- Фон лабиринта -->
    <img :src="`/maze/${maze}.png`" alt="Maze" class="maze-bg">
    
    <!-- Контейнер для тараканов -->
    <div class="cockroaches-container">
      <Cockroach 
        v-for="(cockroach, index) in cockroaches" 
        :key="index"
        :color="cockroach.color"
        :position="cockroach.position"
        :isRunning="cockroach.isRunning"
      />
    </div>
    
    <!-- Финишная линия -->
    <div class="finish-line"></div>
  </div>
</template>

<script setup>
import Cockroach from './Cockroach.vue';

defineProps({
  maze: String,
  cockroaches: Array
});
</script>

<style scoped>
.race-track {
  position: relative;
  width: 100%;
  height: 500px;
  overflow: hidden;
  border-radius: 10px;
  border: 2px solid #5e35b1;
}

.maze-bg {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cockroaches-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.finish-line {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 5px;
  background: linear-gradient(90deg, #ff00cc, #333399);
  box-shadow: 0 0 10px #ff00cc;
}
</style>