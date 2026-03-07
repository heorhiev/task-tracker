#!/usr/bin/env bash
set -euo pipefail

if [ -f app/composer.json ]; then
  echo "Yii2 app already exists in ./app"
  exit 0
fi

mkdir -p app

docker compose run --rm php composer create-project --prefer-dist yiisoft/yii2-app-advanced /var/www/html
docker compose run --rm php sh -lc "cd /var/www/html && composer config audit.block-insecure false && composer install && php init --env=Development --overwrite=All"

echo "Yii2 Advanced app created in ./app"
