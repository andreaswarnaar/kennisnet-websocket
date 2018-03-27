FROM php:7.0-apache

MAINTAINER Andreas Warnaar

ARG local_user_id=1000
ARG local_user_group_id=1000

RUN groupadd --gid $local_user_group_id host && useradd -u $local_user_id -g $local_user_group_id host -s /bin/bash -m && \
    usermod -a -G www-data host

RUN echo Europe/Amsterdam > /etc/timezone && \
ln -sf /usr/share/zoneinfo/Europe/Amsterdam /etc/localtime && \
dpkg-reconfigure -f noninteractive tzdata

# Install ssl-cert
RUN apt-get update \
        && apt-get install -y \
        ssl-cert \
        libxml2-dev \
        libxslt-dev \
        libicu-dev \
        git \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        zlib1g-dev \
        libssh2-1-dev \
        supervisor \
        && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

# Install apache2 and enable defaults
RUN a2enmod ssl \
        && a2enmod headers \
        && a2enmod rewrite \
        && a2enmod proxy \
        && a2enmod proxy_ajp \
        && a2enmod proxy_http \
        && a2enmod deflate \
        && a2enmod proxy_balancer \
        && a2enmod proxy_connect \
        && a2enmod proxy_html \
        && a2enmod proxy_wstunnel \
        && a2ensite default-ssl \
        && a2enmod lbmethod_byrequests

# Install PHP
RUN docker-php-ext-install \
		pdo pdo_mysql \
		opcache \
		xml\
		xsl\
		curl\
		intl \
		pcntl \
		sysvmsg\
		bcmath \
		soap \
		zip \
		&& a2enmod rewrite && mkdir /var/www/html/web

RUN pecl install -f xdebug

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN echo 'su host' > /root/.bashrc


EXPOSE 80 443

ENV IS_DOCKER 1
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY apache2.conf /etc/apache2/apache2.conf
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
COPY project.ini /usr/local/etc/php/conf.d/project.ini
COPY servers.txt /etc/apache2/servers.txt

CMD ["/usr/bin/supervisord"]