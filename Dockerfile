FROM php:8.2-apache

# Устанавливаем расширения PHP (для MySQL и других)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && a2enmod rewrite

# Копируем код проекта
WORKDIR /var/www/html
COPY . .

# Настраиваем права (если нужно)
RUN chown -R www-data:www-data /var/www/html

# Экспонируем порт
EXPOSE 8000

# Запуск Apache
CMD ["apache2-foreground"]