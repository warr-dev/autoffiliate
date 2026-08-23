# ==============================================================================
# PHP Laravel Devcontainer Dockerfile
# PHP Version: 8.3
# Database Support: mariadb
# ==============================================================================
FROM php:8.3-cli-alpine

ARG USER_ID=1000
ARG GROUP_ID=1000

# 1. Create a non-root user and setup sudo access
RUN apk add --no-cache shadow sudo && \
    (getent group ${GROUP_ID} || addgroup -g ${GROUP_ID} -S user) && \
    (getent passwd ${USER_ID} || adduser -u ${USER_ID} -D -G user -s /bin/bash user) && \
    echo "user ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers.d/user && \
    chmod 0440 /etc/sudoers.d/user

# 2. Install essential build tools, libraries, and utilities
RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    nano \
    htop \
    zip \
    unzip \
    openssh-client \
    rsync \
    sshpass \
    build-base \
    autoconf \
    gcc \
    g++ \
    make \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libxml2-dev \
    openssl-dev \
    linux-headers

# 3. Configure and install core PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo \
    soap \
    zip \
    mysqli \
    pdo_mysql

# 4. Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis


# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 7. Install Node.js & npm for frontend asset compilation (Vite, Tailwind, etc.)
RUN apk add --no-cache nodejs npm

# 8. Configure Cron scheduler
RUN mkdir -p /etc/periodic/1min /var/log/cron && \
    echo "*       *       *       *       *       run-parts /etc/periodic/1min" >> /etc/crontabs/root
COPY ./cron/ /etc/periodic/1min/
RUN chmod -R +x /etc/periodic/1min

# 9. Setup workspace directory permissions
RUN mkdir -p /workspaces && chown -R user:user /workspaces

COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER user
WORKDIR /workspaces

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["sleep", "infinity"]
