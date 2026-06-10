FROM php:8.2-apache

# Update packages and install tools if needed
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PDO MySQL and other commonly used extensions
RUN docker-php-ext-install pdo pdo_mysql gd

# Enable Apache Mod Rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to the 'public/' directory instead of the root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set up clean directories and copy code
WORKDIR /var/www/html
COPY . /var/www/html

# Adjust file permissions for runtime execution
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80
