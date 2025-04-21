# Базовый образ
FROM php:8.1

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl gd sockets opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/symfony

# 1. Сначала копируем только файлы, необходимые для composer
COPY composer.json composer.lock symfony.lock ./

# 2. Установка зависимостей (без scripts, чтобы избежать ошибок)
RUN composer install --no-dev --no-scripts --no-autoloader

# 3. Копируем остальные файлы (кроме тех, что в .dockerignore)
COPY . .

# 4. Создаем минимальный .env для production
RUN echo "APP_ENV=prod" > .env && \
    echo "APP_SECRET=$(openssl rand -hex 16)" >> .env && \
    echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" >> .env

# 5. Завершаем установку
RUN composer dump-autoload --optimize && \
    php bin/console cache:clear --no-warmup && \
    chown -R www-data:www-data var/

EXPOSE 80
CMD ["symfony serve"]