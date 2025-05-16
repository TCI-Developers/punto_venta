#!/bin/bash

# Detectar la versión de PHP activa
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
INI_PATH="/usr/local/etc/php/${PHP_VERSION}/php.ini"

# Verifica que el archivo php.ini exista
if [ ! -f "$INI_PATH" ]; then
  echo "❌ No se encontró el archivo php.ini en: $INI_PATH"
  exit 1
fi

# Habilita la extensión pdo_mysql si está comentada
if grep -q ";extension=pdo_mysql" "$INI_PATH"; then
  echo "✅ Activando extensión pdo_mysql en $INI_PATH..."
  sed -i '' 's/;extension=pdo_mysql/extension=pdo_mysql/' "$INI_PATH"
else
  echo "✅ La extensión pdo_mysql ya está activa."
fi

# Verifica que esté activada
php -m | grep -q pdo_mysql && echo "🎉 La extensión pdo_mysql está activa." || echo "⚠️ Aún no se detecta pdo_mysql."

# Preguntar si desea reiniciar Laravel Native
read -p "¿Deseas reiniciar Laravel Native (php artisan native:serve)? (s/n): " respuesta
if [[ "$respuesta" == "s" || "$respuesta" == "S" ]]; then
  php artisan native:serve
fi
