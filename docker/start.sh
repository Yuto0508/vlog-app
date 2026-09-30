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

# アップロード画像の保存先。永続ディスクを storage/app に載せると、ビルド時に作ったフォルダは隠れるため、起動時に作る
mkdir -p storage/app/public/images

php artisan migrate --force
# タグと(ADMIN_EMAIL / ADMIN_PASSWORD があれば)管理者を作る。何度実行しても重複しない
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

# root で作ったファイルを、Apache(www-data)が書き込めるようにする
chown -R www-data:www-data storage/framework storage/logs bootstrap/cache
# 永続ディスクは root 所有でマウントされ、中身(画像)が増えると -R は遅くなるため、
# ディスク側は必要なディレクトリと DB ファイルだけを対象にする(SQLite はディレクトリへの書き込みも要る)
chown www-data:www-data storage/app storage/app/public storage/app/public/images "$(dirname "$DB_FILE")" "$DB_FILE"

exec apache2-foreground
