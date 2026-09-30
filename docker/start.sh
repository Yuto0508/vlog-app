#!/bin/sh
# コンテナ起動時に実行される。環境変数はここで初めて確定するため、設定のキャッシュもここで作る。
set -e

cd /var/www/html

# Render が渡す PORT で待ち受ける(未指定なら 10000)
PORT="${PORT:-10000}"
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# SQLite のファイルを用意する(DB_DATABASE で場所を変えられる)
DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
touch "$DB_FILE"

php artisan migrate --force
# タグと(ADMIN_EMAIL / ADMIN_PASSWORD があれば)管理者を作る。何度実行しても重複しない
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

# root で作ったファイルを、Apache(www-data)が書き込めるようにする
chown -R www-data:www-data storage bootstrap/cache "$(dirname "$DB_FILE")"

exec apache2-foreground
