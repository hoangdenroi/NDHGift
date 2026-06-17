# ============================================================
# Stage 1: Build frontend assets (Vite + TailwindCSS)
# ============================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

# ============================================================
# Stage 2: Cài đặt PHP dependencies
# ============================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev

# ============================================================
# Stage 3: Production image (PHP 8.3 FPM + Nginx trên Alpine)
# ============================================================
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="NDHShop <nguyenduchoang522@gmail.com>"

# --- Cài system dependencies ---
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    curl-dev \
    linux-headers

# --- Cài PHP extensions ---
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        xml \
        opcache \
        pcntl \
        exif

# --- Cấu hình PHP cho production ---
COPY deploy/php-production.ini /usr/local/etc/php/conf.d/99-production.ini

# --- Thư mục làm việc ---
WORKDIR /var/www/html

# --- Copy code từ các stage trước ---
COPY --from=composer-builder /app/vendor vendor/
COPY --from=node-builder /app/public/build public/build/

# --- Copy toàn bộ source code ---
COPY . .

# --- Xóa file dev/không cần thiết ---
RUN rm -rf node_modules tests .git .github .env .env.example \
    deploy/server-setup.sh docker-compose.yml

# --- Copy cấu hình server ---
COPY deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY deploy/supervisord-queue.conf /etc/supervisor/conf.d/supervisord-queue.conf
COPY deploy/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# --- Tạo thư mục nginx cần thiết ---
RUN mkdir -p /run/nginx

# --- Set quyền ---
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
