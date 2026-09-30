# Render などの Docker 環境向け。ビルドは 2 段階:
#   1. Node で Vite のアセット(public/build)を作る
#   2. PHP + Apache で本体を動かす

# ---- 1. アセットのビルド ----
FROM node:22-slim AS assets
WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---- 2. アプリ本体 ----
FROM php:8.3-apache

# Composer が依存関係を展開するのに unzip が要る
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: 公開ディレクトリを public/ にし、.htaccess(Laravel のルーティング)を有効にする
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && a2enmod rewrite
COPY docker/apache-laravel.conf /etc/apache2/conf-available/laravel.conf
RUN a2enconf laravel

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/app/public/images storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Render は PORT 環境変数で待ち受けポートを指定する(start.sh で反映)
EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]
