FROM docker.io/wordpress:php8.0-apache

RUN chmod 777 /var/log/apache2

RUN apt-get -y update && apt-get -y --no-install-recommends install less \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar --output /usr/bin/wp \
	&& chmod +X /usr/bin/wp \
	&& chmod 766 /usr/bin/wp

#RUN pecl install xdebug-3.1.1

#ADD ./xdebug.ini         /usr/local/etc/php/conf.d/xdebug.ini
ADD ./quickstart.sh      /usr/bin/quickstart
ADD ./multisite-htaccess /opt/wpss/htaccess

RUN chmod +x /usr/bin/quickstart

#EXPOSE 9003
