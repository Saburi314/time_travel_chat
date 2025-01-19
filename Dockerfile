# ベースイメージ
FROM php:8.1-apache

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

# カスタム Apache 設定を追加
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# 必要な Apache モジュールを有効化
RUN a2enmod rewrite

# Apache を再起動
RUN service apache2 restart

# 作業ディレクトリを設定
WORKDIR /var/www/html
