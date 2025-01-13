# ベースイメージ
FROM php:8.1-fpm

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl

# Composerをインストール
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# PHP-FPMの listen アドレスを全ての接続元から受け付けるように修正
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

# 作業ディレクトリを設定
WORKDIR /var/www/html
