# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем необходимые расширения PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Копируем файлы проекта в контейнер
COPY . /var/www/html/

# Устанавливаем рабочую директорию
WORKDIR /var/www/html/

# Открываем порт
EXPOSE 80
