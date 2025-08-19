<template>
  <div class="confetti-container">
    <div 
      v-for="(confetto, index) in confetti" 
      :key="index"
      class="confetto"
      :style="confetto.style"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['complete']);
const confetti = ref([]);

onMounted(() => {
  createConfetti();
  setTimeout(() => {
    emit('complete');
  }, 3000);
});

const createConfetti = () => {
  const colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#FFA500'];
  const shapes = ['square', 'circle', 'rectangle', 'triangle'];
  
  for (let i = 0; i < 150; i++) {
    confetti.value.push({
      style: {
        '--x': Math.random() * 100 + 'vw',
        '--y': -10 - Math.random() * 20 + 'vh',
        '--r': Math.random() * 360 + 'deg',
        '--c': colors[Math.floor(Math.random() * colors.length)],
        '--s': shapes[Math.floor(Math.random() * shapes.length)],
        '--d': Math.random() * 2 + 1 + 's',
        '--delay': Math.random() * 0.5 + 's'
      }
    });
  }
};
</script>

<style scoped>
.confetti-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1000;
  overflow: hidden;
}

.confetto {
  position: absolute;
  width: 10px;
  height: 10px;
  animation: fall var(--d) ease-in forwards;
  animation-delay: var(--delay);
  left: var(--x);
  top: var(--y);
  transform: rotate(var(--r));
}

.confetto::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: var(--c);
}

.confetto[style*="square"]::before {
  border-radius: 0;
}

.confetto[style*="circle"]::before {
  border-radius: 50%;
}

.confetto[style*="rectangle"]::before {
  width: 150%;
}

.confetto[style*="triangle"]::before {
  width: 0;
  height: 0;
  background: transparent;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 10px solid var(--c);
}

@keyframes fall {
  0% {
    transform: translateY(0) rotate(var(--r));
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(100vh) rotate(calc(var(--r) * 5));
    opacity: 0;
  }
}
</style>