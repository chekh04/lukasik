FROM php:7.4-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Install ionCube Loader (detect architecture)
RUN cd /tmp \
    && ARCH=$(dpkg --print-architecture) \
    && if [ "$ARCH" = "amd64" ]; then \
         wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
         && tar xzf ioncube_loaders_lin_x86-64.tar.gz \
         && rm ioncube_loaders_lin_x86-64.tar.gz; \
       elif [ "$ARCH" = "arm64" ]; then \
         wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_aarch64.tar.gz \
         && tar xzf ioncube_loaders_lin_aarch64.tar.gz \
         && rm ioncube_loaders_lin_aarch64.tar.gz; \
       fi \
    && PHP_EXT_DIR=$(php -i | grep "extension_dir" | head -1 | awk '{print $NF}') \
    && cp ioncube/ioncube_loader_lin_7.4.so $PHP_EXT_DIR \
    && echo "zend_extension=ioncube_loader_lin_7.4.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
    && rm -rf ioncube

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/memory.ini \
    && echo 'upload_max_filesize = 100M' > /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo 'max_execution_time = 300' > /usr/local/etc/php/conf.d/execution.ini

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

