FROM php:8.1-apache
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev
RUN docker-php-ext-install pdo pdo_mysql gd
COPY . /var/www/html/
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
EXPOSE 80
