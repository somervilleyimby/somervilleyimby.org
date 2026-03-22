FROM wordpress:6-php8.4-fpm

# install the PHP extensions we need (https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions)
RUN set -ex; \
    # https://pecl.php.net/package/imagick
    pecl install redis; \
    docker-php-ext-enable redis; \
    rm -r /tmp/pear;
