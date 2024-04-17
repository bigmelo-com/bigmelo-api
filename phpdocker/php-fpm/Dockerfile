FROM phpdockerio/php:8.2-fpm
WORKDIR "/app"

# setup node js source will be used later to install node js
RUN curl -sL https://deb.nodesource.com/setup_16.x -o nodesource_setup.sh
RUN ["sh",  "./nodesource_setup.sh"]

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        php8.2-bz2 \ 
        php8.2-dba \ 
        php8.2-gd \ 
        php8.2-gmp \ 
        php8.2-imap \ 
        php8.2-imagick \ 
        php8.2-interbase \ 
        php8.2-ldap \ 
        php8.2-memcached \ 
        php8.2-mongodb \ 
        php8.2-mysql \
        php8.2-pgsql \
        php8.2-odbc \
        php8.2-phpdbg \ 
        php8.2-pspell \ 
        php8.2-redis \ 
        php8.2-soap \ 
        php8.2-sqlite3 \ 
        php8.2-xdebug \ 
        php8.2-xsl \ 
        php8.2-yaml; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install npm
RUN apt-get update \
    && apt-get -y install nodejs \
    && apt-get -y install npm \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install laravel
RUN composer global require "laravel/installer"
ENV PATH="/root/.composer/vendor/bin:${PATH}"