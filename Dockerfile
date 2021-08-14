FROM php:7.4-cli

RUN mkdir /books
WORKDIR /books
COPY . /books
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo pdo_mysql
RUN composer install

EXPOSE 8000

CMD ["php","-S","0.0.0.0:8000","-t","public"]
