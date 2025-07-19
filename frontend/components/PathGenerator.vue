<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['paths-generated']);

// Запрос к сервиу генерации путей
const generate = async () => {
  const response = await fetch('/api/path_service.php', {
    method: 'POST',
    body: JSON.stringify({ bug_count: 7, duration: 10000, max_moves: 200 })
  });
  const data = await response.json();
  emit('paths-generated', data);
};

// Генерировать пути при монтировании
generate();
</script>