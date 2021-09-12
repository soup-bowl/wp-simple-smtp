#! /bin/bash
echo "Adding multisite defines and replacing htaccess..."
# Swap htaccess for a MS one - https://wordpress.org/support/article/htaccess/
rm /var/www/html/.htaccess && cp /opt/wpss/htaccess /var/www/html/.htaccess
# Modify the wp-config.php to contain multisite configurations.
wp core multisite-install --title="abc" --admin_email="code@soupbowl.io" --allow-root
echo "Multisite modifications made. You can re-login now."
