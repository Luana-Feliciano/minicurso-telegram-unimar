FROM php:8.3-apache

# PDO do MySQL não vem na imagem oficial; curl e mbstring já vêm.
RUN docker-php-ext-install pdo_mysql
