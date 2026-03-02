# imports

```bash
artisan db:wipe && artisan migrate && artisan db:seed --class=AnswerSeeder && \
artisan import:equipos --database hojaschequeo --username carlos --password carlos1030 && \
artisan db:seed --class=UserSeeder && \
artisan import:perfils --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:users --database hojaschequeo --username carlos --password carlos1030 &&  \
artisan import:tarjetons --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:reportes --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:hojas --database hojaschequeo --username carlos --password carlos1030 && \
php artisan db:seed --class=RecorridoTintoreriaSeeder && \
php artisan db:seed --class=RecorridoLavanderiaSeeder && \
php artisan db:seed --class=RecorridoGerentesSeeder && \
php artisan db:seed --class=RecorridoGeneralSeeder

php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan optimize:clear && \
php artisan optimize && \
sudo rm -rf /var/lib/php/opcache/* && \
sudo systemctl restart php8.4-fpm && \
sudo systemctl reload nginx

```
