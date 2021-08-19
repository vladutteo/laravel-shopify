FROM php:7.4-fpm

ARG USER=www-data

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    curl zip unzip \
    zlib1g-dev libpng-dev libicu-dev libcurl4-openssl-dev libonig-dev libxml2-dev libzip-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists* && \
    docker-php-ext-install gd bcmath iconv intl json mbstring mysqli opcache pdo soap pdo_mysql zip exif

RUN echo "date.timezone=${PHP_TIMEZONE=-Europe/Bucharest}" >> ${PHP_INI_DIR}/conf.d/custom.ini && \
    echo "memory_limit=${PHP_MEMORY_LIMIT:-2G}" >> ${PHP_INI_DIR}/conf.d/custom.ini && \
    echo "file_upoads=${PHP_FILE_UPLOADS:-On}" >> ${PHP_INI_DIR}/conf.d/custom.ini && \
    echo "upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE:-1G}" >> ${PHP_INI_DIR}/conf.d/custom.ini && \
    echo "post_max_size=${PHP_POST_MAX_SIZE:-2G}" >> ${PHP_INI_DIR}/conf.d/custom.ini && \
    echo "max_execution_time=${PHP_MAX_EXECUTION_TIME:-1700}" >> ${PHP_INI_DIR}/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

USER $USER