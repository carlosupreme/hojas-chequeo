#!/bin/bash

# ====================================================================
# SCRIPT DE ACTUALIZACIÓN AUTOMÁTICA - APLICACIÓN LARAVEL
# ====================================================================

# Configuración de colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Crear archivo de registro
LOG_FILE="actualizacion_$(date +%Y-%m-%d_%H-%M-%S).log"
touch "$LOG_FILE"

# Función para escribir en el log y mostrar en pantalla
log() {
    echo -e "${2:-$BLUE}$1${NC}"
    echo "[$(date +%Y-%m-%d" "%H:%M:%S)] $1" >> "$LOG_FILE"
}

# Función para mostrar progreso
show_progress() {
    echo -ne "${BLUE}$1... ${NC}"
    echo -n "$1... " >> "$LOG_FILE"
}

# Función para mostrar completado
show_completed() {
    echo -e "${GREEN}Completado${NC}"
    echo "Completado" >> "$LOG_FILE"
}

# Función para manejar errores
handle_error() {
    log "ERROR: $1" "$RED"
    log "Revisa el archivo de registro $LOG_FILE para más detalles" "$YELLOW"
    
    read -p "¿Deseas continuar con la actualización a pesar del error? (s/n): " CONTINUE
    if [[ ! "$CONTINUE" =~ ^[Ss]$ ]]; then
        log "Actualización cancelada por el usuario" "$RED"
        exit 1
    fi
}

# Función para restaurar backup si es necesario
restore_backup() {
    log "Restaurando copia de seguridad..." "$YELLOW"
    if [ -d "backup_$(date +%Y-%m-%d)" ]; then
        cp -r backup_$(date +%Y-%m-%d)/* .
        log "Copia de seguridad restaurada" "$GREEN"
    else
        log "No se encontró copia de seguridad para hoy" "$RED"
    fi
}

clear
log "===============================================" "$GREEN"
log "   ACTUALIZACIÓN AUTOMÁTICA DE LA APLICACIÓN   " "$GREEN"
log "===============================================" "$GREEN"
log "Fecha: $(date +%Y-%m-%d" "%H:%M:%S)" "$BLUE"
echo ""

# Verificar conexión a internet
show_progress "Verificando conexión a internet"
if ping -c 1 github.com > /dev/null 2>&1; then
    show_completed
else
    log "No hay conexión a internet. La actualización requiere conexión a GitHub." "$RED"
    exit 1
fi

# Verificar y configurar la conexión con el remoto
show_progress "Verificando configuración del repositorio Git"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
REMOTE_CONFIGURED=$(git config --get branch.$CURRENT_BRANCH.remote || echo "")

if [ -z "$REMOTE_CONFIGURED" ]; then
    show_completed
    log "No hay 'upstream' configurado para la rama '$CURRENT_BRANCH'" "$YELLOW"
    
    # Verificar si existe el remoto 'origin'
    if git remote | grep -q "origin"; then
        log "Configurando la rama '$CURRENT_BRANCH' para seguir a 'origin/$CURRENT_BRANCH'" "$BLUE"
        git branch --set-upstream-to=origin/$CURRENT_BRANCH $CURRENT_BRANCH || handle_error "No se pudo configurar el upstream"
        log "Configuración completada exitosamente" "$GREEN"
    else
        log "No se encontró un remoto 'origin' configurado" "$RED"
        read -p "Ingresa la URL del repositorio remoto: " REMOTE_URL
        if [ -n "$REMOTE_URL" ]; then
            git remote add origin "$REMOTE_URL" || handle_error "No se pudo añadir el remoto"
            git branch --set-upstream-to=origin/$CURRENT_BRANCH $CURRENT_BRANCH || handle_error "No se pudo configurar el upstream"
            log "Remoto 'origin' añadido y configurado como upstream" "$GREEN"
        else
            handle_error "No se proporcionó una URL de repositorio remoto"
        fi
    fi
else
    show_completed
fi

# Modificar la sección de obtener cambios
show_progress "Obteniendo cambios del repositorio"
if git pull; then
    show_completed
else
    log "Error al hacer pull desde GitHub" "$RED"
    log "Intentando obtener cambios específicamente de 'origin/$CURRENT_BRANCH'" "$YELLOW"
    
    if git pull origin $CURRENT_BRANCH; then
        show_completed
        log "Cambios obtenidos exitosamente de 'origin/$CURRENT_BRANCH'" "$GREEN"
    else
        handle_error "No se pudieron obtener los cambios del repositorio remoto"
    fi
fi

# Crear copia de seguridad
log "Creando copia de seguridad..." "$YELLOW"
BACKUP_DIR="backup_$(date +%Y-%m-%d)"
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    cp -r .env vendor composer.lock package.json package-lock.json public/build "$BACKUP_DIR" 2>/dev/null || true
    log "Copia de seguridad creada en el directorio: $BACKUP_DIR" "$GREEN"
else
    log "Ya existe una copia de seguridad de hoy. Se utilizará en caso de error." "$YELLOW"
fi

# Verificar cambios pendientes
show_progress "Verificando cambios locales"
if [[ -n $(git status -s) ]]; then
    show_completed
    log "ADVERTENCIA: Hay cambios locales sin confirmar que un desarrollador debe ver" "$YELLOW"
    git status -s | while read line; do
        log "  $line" "$YELLOW"
    done
    
    read -p "¿Deseas continuar con la actualización? Los cambios locales podrían perderse (s/n): " CONTINUE
    if [[ ! "$CONTINUE" =~ ^[Ss]$ ]]; then
        log "Actualización cancelada por el usuario" "$RED"
        exit 1
    fi
else
    show_completed
fi

# Guardar versión actual para referencia
CURRENT_VERSION=$(git rev-parse HEAD)
log "Versión actual: ${CURRENT_VERSION:0:8}" "$BLUE"

# Obtener los últimos cambios
log "Obteniendo los últimos cambios desde GitHub..." "$BLUE"
git fetch origin || handle_error "No se pudieron obtener los cambios de GitHub"

# Verificar si hay actualizaciones disponibles
UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse "$UPSTREAM")

if [ "$LOCAL" = "$REMOTE" ]; then
    log "La aplicación ya está actualizada. No hay cambios disponibles." "$GREEN"
    read -p "Presiona Enter para salir..."
    exit 0
fi

# Listar cambios disponibles
log "Cambios disponibles desde la última actualización:" "$BLUE"
git log --oneline HEAD..origin/main | head -n 5 | while read line; do
    log "  → $line" "$BLUE"
done

# Número total de commits nuevos
TOTAL_COMMITS=$(git log --oneline HEAD..origin/main | wc -l)
if [ $TOTAL_COMMITS -gt 5 ]; then
    log "  ... y $(($TOTAL_COMMITS - 5)) commits más" "$BLUE"
fi

# Preguntar si desea continuar
read -p "¿Deseas actualizar la aplicación ahora? (s/n): " UPDATE
if [[ ! "$UPDATE" =~ ^[Ss]$ ]]; then
    log "Actualización cancelada por el usuario" "$YELLOW"
    exit 0
fi

# Actualización por pasos con progreso visible
echo ""
log "INICIANDO PROCESO DE ACTUALIZACIÓN" "$GREEN"
log "=================================" "$GREEN"

# Paso 1: Guardar configuración actual
show_progress "Guardando configuración actual"
cp .env .env.backup
show_completed

# Paso 2: Obtener cambios
show_progress "Obteniendo cambios del repositorio"
if git pull origin main; then
    show_completed
else
    log "Error al hacer pull desde GitHub" "$RED"
    read -p "¿Deseas continuar de todos modos? (s/n): " CONTINUE
    if [[ ! "$CONTINUE" =~ ^[Ss]$ ]]; then
        log "Actualización cancelada. Restaurando estado anterior..." "$RED"
        git reset --hard "$CURRENT_VERSION"
        exit 1
    fi
fi

# Verificar si hay cambios en composer.json
COMPOSER_CHANGED=0
if git diff "$CURRENT_VERSION" HEAD --name-only | grep -q "composer.json"; then
    COMPOSER_CHANGED=1
    log "Se detectaron cambios en composer.json, actualizando dependencias..." "$YELLOW"
    
    show_progress "Instalando dependencias de Composer"
    if composer install --no-dev --optimize-autoloader; then
        show_completed
    else
        handle_error "Error al actualizar dependencias de Composer"
    fi
fi

# Verificar si hay cambios en package.json
NPM_CHANGED=0
if git diff "$CURRENT_VERSION" HEAD --name-only | grep -q "package.json"; then
    NPM_CHANGED=1
    log "Se detectaron cambios en package.json, actualizando dependencias..." "$YELLOW"
    
    show_progress "Instalando dependencias de npm"
    if npm ci; then
        show_completed
    else
        handle_error "Error al actualizar dependencias de npm"
    fi
    
    show_progress "Compilando assets"
    if npm run build; then
        show_completed
    else
        handle_error "Error al compilar assets"
    fi
fi

# Verificar si hay cambios en migraciones
MIGRATIONS_CHANGED=0
if git diff "$CURRENT_VERSION" HEAD --name-only | grep -q "database/migrations/"; then
    MIGRATIONS_CHANGED=1
    log "Se detectaron nuevas migraciones" "$YELLOW"
    
    read -p "¿Ejecutar migraciones de base de datos? (s/n): " RUN_MIGRATION
    if [[ "$RUN_MIGRATION" =~ ^[Ss]$ ]]; then
        show_progress "Ejecutando migraciones"
        if php artisan migrate --force; then
            show_completed
        else
            handle_error "Error al ejecutar migraciones"
        fi
    fi
fi

# Publicar assets de Filament si hay cambios relacionados
FILAMENT_CHANGED=0
if git diff "$CURRENT_VERSION" HEAD --name-only | grep -q "filament"; then
    FILAMENT_CHANGED=1
    show_progress "Publicando assets de Filament"
    if php artisan filament:assets; then
        show_completed
    else
        handle_error "Error al publicar assets de Filament"
    fi
fi

# Limpiar y reconstruir caché
show_progress "Limpiando caché"
php artisan optimize:clear
php artisan clear-compiled
show_completed

show_progress "Reconstruyendo caché"
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
show_completed

# Verificar archivos de configuración
if [ -f .env.example ]; then
    show_progress "Verificando variables de entorno"
    NEW_VARS=$(grep -v '^#' .env.example | grep '=' | cut -d '=' -f 1)
    CURRENT_VARS=$(grep -v '^#' .env | grep '=' | cut -d '=' -f 1)
    
    MISSING_VARS=0
    for var in $NEW_VARS; do
        if ! echo "$CURRENT_VARS" | grep -q "$var"; then
            if [ $MISSING_VARS -eq 0 ]; then
                log "ADVERTENCIA: Faltan algunas variables de entorno en tu archivo .env:" "$YELLOW"
            fi
            MISSING_VARS=1
            VALUE=$(grep "^$var=" .env.example | cut -d '=' -f 2-)
            log "  → $var=$VALUE" "$YELLOW"
            
            # Añadir variable faltante al .env
            echo "$var=$VALUE" >> .env
        fi
    done
    
    if [ $MISSING_VARS -eq 0 ]; then
        show_completed
    else
        log "Se han añadido las variables faltantes al archivo .env. Revisa y configura sus valores." "$YELLOW"
    fi
fi

# Resumen de la actualización
echo ""
log "===== RESUMEN DE LA ACTUALIZACIÓN =====" "$GREEN"
log "✓ Actualización de código completada" "$GREEN"
if [ $COMPOSER_CHANGED -eq 1 ]; then log "✓ Dependencias de Composer actualizadas" "$GREEN"; fi
if [ $NPM_CHANGED -eq 1 ]; then log "✓ Dependencias de npm y assets actualizados" "$GREEN"; fi
if [ $MIGRATIONS_CHANGED -eq 1 ]; then log "✓ Migraciones de base de datos aplicadas" "$GREEN"; fi
if [ $FILAMENT_CHANGED -eq 1 ]; then log "✓ Assets de Filament publicados" "$GREEN"; fi
log "✓ Caché reconstruida" "$GREEN"
log "✓ Aplicación optimizada" "$GREEN"
log "=====================================" "$GREEN"

log "La aplicación ha sido actualizada correctamente a la versión: $(git rev-parse HEAD | cut -c 1-8)" "$GREEN"
log "Archivo de registro guardado en: $LOG_FILE" "$BLUE"

echo ""
read -p "Presiona Enter para terminar..."

exit 0