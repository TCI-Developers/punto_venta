#!/bin/bash

echo "📦 Instalando entorno para Laravel + NativePHP"

# 1. Verifica que Homebrew esté instalado
if ! command -v brew &> /dev/null; then
    echo "🔧 Instalando Homebrew..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
fi

# 2. Instala PHP con Homebrew
echo "🧪 Instalando PHP..."
brew install php

# 3. Verifica que pdo_mysql esté activo
php -m | grep pdo_mysql
if [ $? -ne 0 ]; then
    echo "❌ ERROR: pdo_mysql no está activado. Revisa tu instalación de PHP."
    exit 1
fi

# 4. Instala Composer
if ! command -v composer &> /dev/null; then
    echo "📦 Instalando Composer..."
    brew install composer
fi

# 5. Instala dependencias de Laravel
echo "🎯 Instalando dependencias del proyecto..."
composer install

# 6. Copia archivo .env si no existe
if [ ! -f .env ]; then
    echo "🔐 Copiando .env de ejemplo..."
    cp .env.example .env
fi

# 7. Genera clave de app
php artisan key:generate

echo "✅ Listo. Ahora puedes correr: php artisan native:serve"
