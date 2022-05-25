#!/bin/bash
docker run --rm --tty --volume $PWD:/app --user $(id -u):$(id -g) composer install --ignore-platform-reqs
docker-compose up -d
echo "Pausing for MySQL to complete..."&& sleep 30
docker-compose exec www wp core install --url="https://${CODESPACE_NAME}-8080.githubpreview.dev" --title="SMTP Dummy" --admin_user="admin" --admin_password="password" --admin_email="code@soupbowl.io" --allow-root
docker-compose exec www wp plugin activate simple-smtp --allow-root
cp .env.example .env
