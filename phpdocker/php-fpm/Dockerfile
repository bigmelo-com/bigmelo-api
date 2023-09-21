FROM phpdockerio/php:8.1-fpm
WORKDIR "/app"

# setup node js source will be used later to install node js
RUN curl -sL https://deb.nodesource.com/setup_16.x -o nodesource_setup.sh
RUN ["sh",  "./nodesource_setup.sh"]

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        php8.1-bz2 \ 
        php8.1-dba \ 
        php8.1-gd \ 
        php8.1-gmp \ 
        php8.1-imap \ 
        php8.1-imagick \ 
        php8.1-interbase \ 
        php8.1-ldap \ 
        php8.1-memcached \ 
        php8.1-mongodb \ 
        php8.1-mysql \
        php8.1-pgsql \
        php8.1-odbc \
        php8.1-phpdbg \ 
        php8.1-pspell \ 
        php8.1-redis \ 
        php8.1-soap \ 
        php8.1-sqlite3 \ 
        php8.1-xdebug \ 
        php8.1-xsl \ 
        php8.1-yaml; \
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