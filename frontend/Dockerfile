FROM node:20-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --silent --no-progress

COPY . .

ARG BUILD_ENV=production

# Копируем соответствующий .env файл
COPY .env.${BUILD_ENV} .env

# Сборка только для production
RUN if [ "$BUILD_ENV" = "production" ]; then npm run build; fi

CMD if [ "$BUILD_ENV" = "production" ]; then \
      npm run start; \
    else \
      npm run dev -- --host 0.0.0.0; \
    fi