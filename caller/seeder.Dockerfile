FROM php:8.4-cli

# Устанавливаем необходимые зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libmariadb-dev-compat \
    libmariadb-dev \
    zip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем необходимые расширения PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем все файлы
COPY . .

# Команда запуска: просто открываем оболочку
CMD ["sh"]
