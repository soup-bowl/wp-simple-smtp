#! /bin/bash
wp core install --url="localhost" --title="Development" --admin_user="admin" --admin_password="password" --admin_email="code@example.com" --skip-email --allow-root
if [ "$1" == "ms" ]; then
	rm /var/www/html/.htaccess && cp /opt/wpss/htaccess /var/www/html/.htaccess
	wp core multisite-install --title="abc" --admin_email="code@soupbowl.io" --allow-root
fi
