FROM php:8.3-fpm

COPY php.ini /usr/local/etc/php/

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip

# マルウェア報告された版を依存解決から外す仕組みは Composer 2.10 で入った。
# 古い版のまま動くとこの保護が存在しないので、満たなければビルドを失敗させる。
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --2 \
    && composer --version | grep -qE 'version (2\.(1[0-9]|[2-9][0-9])|[3-9])' \
    || (echo "Composer 2.10 未満のためビルドを中止します" && exit 1)

WORKDIR /var/www