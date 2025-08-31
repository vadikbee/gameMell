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
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
  containerWidth: Number,
  containerHeight: Number,
  containerLeft: Number,
  containerTop: Number,
  scaleFactor: {
    type: Number,
    default: 1
  }
});

const emit = defineEmits(['complete']);
const confetti = ref([]);

const createConfetti = () => {
  const colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#FFA500'];
  const shapes = ['square', 'circle', 'rectangle', 'triangle'];
  
  confetti.value = [];
  
  for (let i = 0; i < 150; i++) {
    confetti.value.push({
      style: {
        '--x': Math.random() * 100 + '%',
        '--y': -10 - Math.random() * 20 + 'px',
        '--r': Math.random() * 360 + 'deg',
        '--c': colors[Math.floor(Math.random() * colors.length)],
        '--s': shapes[Math.floor(Math.random() * shapes.length)],
        '--d': Math.random() * 2 + 1 + 's',
        '--delay': Math.random() * 0.5 + 's',
        '--scale': props.scaleFactor
      }
    });
  }
};

onMounted(async () => {
  await nextTick();
  createConfetti();
  setTimeout(() => {
    emit('complete');
  }, 3000);
});

watch(() => [props.containerWidth, props.containerHeight, props.scaleFactor], () => {
  if (props.containerWidth > 0 && props.containerHeight > 0) {
    createConfetti();
  }
});
</script>

<style scoped>
.confetti-container {
  position: absolute;
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
  transform: rotate(var(--r)) scale(var(--scale));
  transform-origin: center;
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

/* Обновленная анимация - падение до конца контейнера */
@keyframes fall {
  100% {
    transform: translateY(calc(v-bind('containerHeight') * 1px)) rotate(calc(var(--r) * 5)) scale(var(--scale));
    opacity: 0;
  }
}
</style>