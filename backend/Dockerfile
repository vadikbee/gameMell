FROM php:8.1-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Установка PHP расширений
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создаем рабочую директорию
WORKDIR /var/www/html

# Копируем ТОЛЬКО файлы зависимостей (для кэширования слоя)
COPY composer.json composer.lock ./

# Устанавливаем зависимости (без скриптов)
RUN composer install --no-scripts --no-autoloader

# Копируем ВЕСЬ остальной код
COPY . .

# Завершаем установку зависимостей
RUN composer dump-autoload --optimize

# Права доступа
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Запуск сервера
CMD php artisan serve --host=0.0.0.0 --port=8000