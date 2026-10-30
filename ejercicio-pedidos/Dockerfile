# syntax=docker/dockerfile:1.7
# =============================================================================
# API Laravel 12 - imagen de producción multi-stage
#   vendor  -> Composer instala dependencias SIN dev (capa cacheada por composer.lock)
#   runtime -> PHP 8.4-FPM sobre Alpine + Nginx (proxy inverso) + supervisord
# PHP 8.4 porque composer.json exige "php": "^8.4".
# =============================================================================

# ---------- Etapa 1: dependencias de Composer ----------
FROM composer:2 AS vendor
WORKDIR /app

# Solo los manifiestos: mientras composer.lock no cambie, esta capa sale del caché
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
    composer install \
      --no-dev --no-scripts --no-autoloader \
      --prefer-dist --no-interaction --ignore-platform-reqs

# Código necesario para generar el autoload optimizado (classmap autoritativo)
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ---------- Etapa 2: runtime ----------
FROM php:8.4-fpm-alpine AS runtime

# nginx + supervisor (dos procesos en un contenedor) + su-exec (bajar privilegios)
# pdo_mysql y opcache son las únicas extensiones que hay que compilar: el resto
# (mbstring, openssl, tokenizer, xml, ctype, json, curl...) ya viene en la imagen oficial.
RUN apk add --no-cache nginx supervisor su-exec \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql opcache \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && php -m | grep -iE '^(pdo_mysql|mbstring|openssl|tokenizer|xml|ctype|json)$' | sort

WORKDIR /var/www/html

# Configuración (cambia poco -> va antes que el código)
COPY docker/php.ini           $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/php-fpm.conf      /usr/local/etc/php-fpm.d/zzz-app.conf
COPY docker/nginx.conf        /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf  /etc/supervisord.conf
COPY docker/entrypoint.sh     /usr/local/bin/entrypoint.sh
# Quita CRLF por si el archivo se editó en Windows
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Solo la carpeta vendor/ viene de la etapa de build (sin Composer, git ni paquetes dev)
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
# Código de la aplicación (lo que excluye .dockerignore no entra)
COPY --chown=www-data:www-data . .

# Directorios que Laravel necesita escribir
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage/framework storage/logs bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
  CMD wget -qO- http://127.0.0.1/up >/dev/null 2>&1 || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
