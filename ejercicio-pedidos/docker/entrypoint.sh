#!/bin/sh
# Prepara Laravel con las variables de entorno REALES del contenedor y arranca los procesos.
set -e
cd /var/www/html

# Todo artisan corre como www-data para que los logs/cachés no queden como root
artisan() { su-exec www-data php artisan "$@"; }

artisan package:discover --ansi
artisan config:cache
artisan view:cache
# route:cache NO se usa: routes/api.php y web.php tienen rutas con Closure

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "Ejecutando migraciones (reintenta mientras MySQL termina de arrancar)..."
  n=0
  until artisan migrate --force; do
    n=$((n + 1))
    if [ "$n" -ge 10 ]; then
      echo "No se pudo conectar a la base de datos tras $n intentos." >&2
      exit 1
    fi
    sleep 3
  done
fi

exec "$@"
