FROM wordpress:php7.4-apache

RUN pecl install xdebug-2.9.8
ADD ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 9000