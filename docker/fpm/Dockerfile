FROM php:7.3-fpm

ARG environment=dev

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        ssh-client \
        git \
        unzip \
        libicu-dev \
        libmcrypt-dev \
        curl

RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install pcntl \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && rm -rf /tmp/pear

RUN if [ $environment = "dev" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi

RUN rm -f /usr/local/etc/www.conf.default && rm -f /usr/local/etc/docker.conf && rm -f /usr/local/etc/zz-docker.conf

COPY docker/fpm/config/php_${environment}.ini /usr/local/etc/php/php.ini
COPY docker/fpm/config/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/fpm/config/www_${environment}.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /usr/src/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /usr/src/app

RUN if [ $environment = "prod" ]; then SYMFONY_ENV=prod composer install --no-dev --optimize-autoloader \
     && echo "Fix permissions" \
     && chown -R www-data:www-data /usr/src/app; fi

CMD ["php-fpm"]
