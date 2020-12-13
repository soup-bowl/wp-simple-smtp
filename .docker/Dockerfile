FROM wordpress:php7.4-apache

RUN pecl install xdebug-3.0.1
ADD ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 9003