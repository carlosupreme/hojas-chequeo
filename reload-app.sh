#!/bin/bash

php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan optimize:clear && \
php artisan optimize && \
sudo rm -rf /var/lib/php/opcache/* && \
sudo systemctl restart php8.4-fpm && \
sudo systemctl reload nginx

GREEN='\033[0;32m'
echo -e "${GREEN}🎉 Application reloaded successfully.${NC}"