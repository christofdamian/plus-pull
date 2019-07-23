FROM php:7.3-cli
COPY . /usr/src/plus-pull/
WORKDIR /usr/src/plus-pull

RUN apt-get update && apt-get -y --no-install-recommends install git unzip
RUN php -r "readfile('https://raw.githubusercontent.com/composer/getcomposer.org/3c21a2c/web/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN /usr/bin/composer install

CMD [ "php", "/usr/src/plus-pull/bin/pluspull.php" ]
