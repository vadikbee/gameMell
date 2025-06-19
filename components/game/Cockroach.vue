#Компонент таракана
<template>
  <div 
    class="cockroach" 
    :style="{
      left: position.x + 'px',
      top: position.y + 'px',
      backgroundColor: color,
      animation: isRunning ? 'run 0.5s infinite alternate' : 'none'
    }"
  >
    <div class="antenna left"></div>
    <div class="antenna right"></div>
    <div class="leg" v-for="n in 6" :key="n" :class="'leg-' + n"></div>
  </div>
</template>

<script setup>
defineProps({
  color: String,
  position: Object,
  isRunning: Boolean
});
</script>

<style scoped>
.cockroach {
  position: absolute;
  width: 30px;
  height: 15px;
  border-radius: 50% 50% 40% 40%;
  transform: translate(-50%, -50%);
  transition: left 0.5s linear, top 0.5s linear;
  z-index: 10;
}

.antenna {
  position: absolute;
  top: -8px;
  width: 2px;
  height: 8px;
  background-color: inherit;
}

.antenna.left {
  left: 5px;
  transform: rotate(-20deg);
}

.antenna.right {
  right: 5px;
  transform: rotate(20deg);
}

.leg {
  position: absolute;
  width: 6px;
  height: 2px;
  background-color: inherit;
  bottom: 0;
}

.leg-1 { left: 3px; transform: rotate(30deg); }
.leg-2 { left: 10px; transform: rotate(15deg); }
.leg-3 { left: 17px; transform: rotate(-15deg); }
.leg-4 { right: 17px; transform: rotate(15deg); }
.leg-5 { right: 10px; transform: rotate(-15deg); }
.leg-6 { right: 3px; transform: rotate(-30deg); }

@keyframes run {
  0% { transform: translate(-50%, -50%) rotate(-2deg); }
  100% { transform: translate(-50%, -50%) rotate(2deg); }
}
</style>