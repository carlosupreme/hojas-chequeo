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

```

# set up ci/cd

```bash
sudo visudo
administrador  ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.4-fpm, /bin/systemctl reload nginx, /bin/systemctl restart >
```

# set user to php-fpm

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

## Change these two lines:

```
user = administrador
group = administrador
```

## Then restart PHP-FPM:

```bash
sudo systemctl restart php8.4-fpm
```

# Ensure the folder is owned by the user

```bash
chown -R administrador:administrador /var/www/v2
```

# Change systemd owner

```bash
sudo nano /etc/systemd/system/reverb.service
```

## Then reload and restart:

```bash
sudo systemctl daemon-reload
sudo systemctl restart reverb
sudo systemctl restart laravel-queue
```

# Run the script

```bash
chmod +x deploy.sh && ./deploy.sh
```

# If fails, install node 

```bash
sudo apt remove nodejs
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.4/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 24
nvm use 24
node -v
```



