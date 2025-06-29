FROM node:20-alpine

WORKDIR /app


# Установка зависимостей в отдельном слое
COPY package.json package-lock.json ./
RUN npm ci --silent --no-progress

# Копирование приложения
COPY . .

# Сборка приложения
RUN npm run build

# Запуск сервера
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]