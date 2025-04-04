#!/bin/bash

echo "=========================================================="
echo "  Configuración de Variables de Entorno para Laravel 11"
echo "=========================================================="

# Verificar si existe .env, si no, copiar desde .env.example
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "Archivo .env creado a partir de .env.example"
    else
        echo "ADVERTENCIA: No se encontró .env.example. Creando .env vacío."
        touch .env
    fi
fi

# Solicitar URL de la aplicación
read -p "Ingrese la URL de la aplicación (ej. http://localhost): " APP_URL
APP_URL=${APP_URL:-"http://localhost"}

# Base de datos - MySQL por defecto
echo -e "\n--- Configuración de Base de Datos MySQL ---"
DB_CONNECTION="mysql"
read -p "Ingrese el host de la base de datos [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-"localhost"}

read -p "Ingrese el puerto de la base de datos [3306]: " DB_PORT
DB_PORT=${DB_PORT:-"3306"}

read -p "Ingrese el nombre de la base de datos: " DB_DATABASE
while [[ -z "$DB_DATABASE" ]]; do
    echo "El nombre de la base de datos no puede estar vacío."
    read -p "Ingrese el nombre de la base de datos: " DB_DATABASE
done

read -p "Ingrese el usuario de la base de datos [root]: " DB_USERNAME
DB_USERNAME=${DB_USERNAME:-"root"}

read -p "Ingrese la contraseña de la base de datos []: " DB_PASSWORD
DB_PASSWORD=${DB_PASSWORD:-""}

# Actualizar o añadir las variables en el archivo .env
sed -i "s#APP_URL=.*#APP_URL=$APP_URL#g" .env
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/g" .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/g" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/g" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/g" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/g" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/g" .env

echo -e "\nVariables de entorno actualizadas correctamente."

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo "Generando clave de aplicación..."
    php artisan key:generate
fi

echo -e "\n--- Continuando con la construcción de la aplicación ---\n"

# Instalar dependencias de Composer optimizadas para producción
composer install --no-dev --optimize-autoloader

# Instalar dependencias de npm de forma reproducible
npm ci

# Compilar assets para producción
npm run build

# Publicar assets de Filament
php artisan filament:assets

# Optimizar la aplicación
php artisan clear-compiled
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Preguntar si desea ejecutar migraciones
read -p "¿Desea ejecutar las migraciones de la base de datos? (s/n): " RUN_MIGRATION
if [[ "$RUN_MIGRATION" =~ ^[Ss]$ ]]; then
    php artisan migrate --seed --force
fi

echo -e "\n=========================================================="
echo "  ¡Aplicación construida y optimizada para producción!"
echo "=========================================================="
