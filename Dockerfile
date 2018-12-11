FROM php:7.0-cli
COPY . /usr/src/plus-pull/
WORKDIR /usr/src/plus-pull

RUN apt-get update && apt-get -y --no-install-recommends install git unzip
RUN php -r "readfile('https://raw.githubusercontent.com/composer/getcomposer.org/d3e09029468023aa4e9dcd165e9b6f43df0a9999/web/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN composer install

CMD [ "php", "/usr/src/plus-pull/bin/pluspull.php" ]
