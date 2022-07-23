#!/bin/bash
if [ ! -z ${GITPOD_HOST+x} ]; then
	WP_SITE_URL="https://8080-${GITPOD_WORKSPACE_ID}.${GITPOD_WORKSPACE_CLUSTER_HOST}"
elif [ ! -z ${CODESPACE_NAME+x} ]; then
	WP_SITE_URL="https://${CODESPACE_NAME}-8080.githubpreview.dev"
else
	WP_SITE_URL="http://localhost:8080"
fi

docker run --rm --tty --volume $PWD:/app --user $(id -u):$(id -g) composer install --ignore-platform-reqs
docker-compose up -d
echo "Pausing for MySQL to complete..." && sleep 30
docker-compose exec www wp core install --url="${WP_SITE_URL}" --title="SMTP Dummy" --admin_user="admin" --admin_password="password" --admin_email="code@soupbowl.io" --allow-root
docker-compose exec www wp plugin activate simple-smtp --allow-root
cp .env.example .env
