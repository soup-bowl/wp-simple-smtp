FROM wordpress:php7.4-apache

RUN apt-get update && apt-get install less

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar --output /usr/bin/wp \
	&& chmod +X /usr/bin/wp \
	&& chmod 766 /usr/bin/wp

RUN pecl install xdebug-3.1.1

ADD ./xdebug.ini         /usr/local/etc/php/conf.d/xdebug.ini
ADD ./quickstart.sh      /usr/bin/quickstart
ADD ./multisite-htaccess /opt/wpss/htaccess

RUN chmod +x /usr/bin/quickstart

EXPOSE 9003
