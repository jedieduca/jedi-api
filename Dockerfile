FROM php:8.3-apache

# Instala as dependências básicas e extensões do seu ecossistema
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    imagemagick \
    libmagickwand-dev \
    ghostscript \
    zip unzip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        soap \
        sockets \
        exif \
        intl \
        opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# 🔹 SOLUÇÃO DO 404: Aponta a raiz pública do Apache direto para a pasta api
ENV APACHE_DOCUMENT_ROOT /var/www/html/api
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Permite que os arquivos .htaccess substituam as regras
RUN echo '<Directory /var/www/html/api/>\n\tOptions Indexes FollowSymLinks MultiViews\n\tAllowOverride All\n\tRequire all granted\n</Directory>' > /etc/apache2/conf-available/override-permissions.conf \
    && a2enconf override-permissions

# Composer (gerenciador de dependências PHP, ex.: PHPMailer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# O volume ./:/var/www/html sobrepõe o que foi gerado no build, por isso
# as dependências são instaladas ao subir o container (rápido se já existirem).
CMD ["sh", "-c", "composer install --no-dev --no-interaction --optimize-autoloader || echo '[jedi-api] AVISO: composer install falhou'; exec apache2-foreground"]