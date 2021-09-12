FROM wordpress:php7.4-apache

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar --output /usr/bin/wp \
	&& chmod +X /usr/bin/wp \
	&& chmod 766 /usr/bin/wp

RUN pecl install xdebug-3.0.1

ADD ./xdebug.ini              /usr/local/etc/php/conf.d/xdebug.ini
ADD ./convert-to-multisite.sh /usr/bin/convert-to-multisite
ADD ./multisite-htaccess      /opt/wpss/htaccess

RUN chmod +x /usr/bin/convert-to-multisite

EXPOSE 9003