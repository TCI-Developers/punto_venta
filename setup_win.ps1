Write-Host "📦 Instalando entorno para Laravel NativePHP"

# Verificar PHP
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "❌ PHP no encontrado. Instálalo desde https://windows.php.net/download/"
    exit 1
}

# Verificar pdo_mysql
$pdoCheck = php -m | Select-String "pdo_mysql"
if (-not $pdoCheck) {
    Write-Host "❌ La extensión pdo_mysql no está habilitada en tu PHP. Edita php.ini"
    exit 1
}

# Verificar Composer
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Composer no encontrado. Instálalo desde https://getcomposer.org/"
    exit 1
}

# Instalar dependencias
composer install

# Copiar .env si no existe
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

# Generar key
php artisan key:generate

Write-Host "✅ Entorno listo. Puedes correr 'php artisan native:serve'"
