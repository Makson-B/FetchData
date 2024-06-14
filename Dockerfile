# Используем базовый образ PHP с FPM
FROM php:8.2-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    cron \
    && docker-php-ext-install zip pdo_mysql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы Laravel в контейнер
COPY . /var/www/html

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Устанавливаем зависимости PHP
RUN composer install --no-dev --optimize-autoloader

# Настраиваем права доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Устанавливаем права на запись для директории
RUN chmod -R 777 /tmp

# Настраиваем cron
COPY ./crontab /etc/cron.d/laravel-cron
RUN chmod 0644 /etc/cron.d/laravel-cron \
        && touch /var/log/cron.log

# Запускаем PHP-FPM
CMD ["php-fpm"]