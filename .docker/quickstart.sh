#! /bin/bash
URL=${1:-localhost:8080}

wp core install --url="${URL}" --title="Development" --admin_user="admin" --admin_password="password" --admin_email="code@example.com" --skip-email --allow-root
if [ "$2" == "ms" ]; then
	rm /var/www/html/.htaccess && cp /opt/wpss/htaccess /var/www/html/.htaccess
	wp core multisite-install --title="abc" --admin_email="code@soupbowl.io" --allow-root
fi

if [ "$2" == "beta" ]; then
	wp core update --version=nightly --allow-root
fi

wp plugin install wp-crontrol --allow-root
wp plugin install plugin-check --allow-root

wp plugin activate simple-smtp wp-crontrol plugin-check --allow-root
