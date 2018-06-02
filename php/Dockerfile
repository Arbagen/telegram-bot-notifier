FROM php:7.2-rc-fpm
RUN apt-get update && apt-get install -y git mc gnupg cron vim
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo_mysql
COPY ./config/php.ini /usr/local/etc/php/php.ini
# Install Composer and make it available in the PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN apt install -y nodejs
#xdebug
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    pecl install xdebug-2.6.0beta1 && docker-php-ext-enable xdebug && \
    echo "\n xdebug.remote_host=dockerhost \n xdebug.remote_enable = 1 \n xdebug.remote_port = 9000 \n xdebug.max_nesting_level = 500 \n xdebug.remote_autostart=1 \n xdebug.remote_connect_back=1" >> /usr/local/etc/php/php.ini \
;fi
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn
