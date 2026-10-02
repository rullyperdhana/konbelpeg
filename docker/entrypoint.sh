#!/bin/sh
set -e

echo "==> Memulai inisialisasi aplikasi Laravel..."

# Pastikan folder storage & cache ada
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

# Atur permission
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Generate app key jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "==> Generating Application Key..."
    php artisan key:generate --force
fi

# Buat symbolic link storage jika belum ada
php artisan storage:link --force || true

# Tunggu database MySQL siap (jika menggunakan MySQL)
if [ "$DB_CONNECTION" = "mysql" ] && [ -n "$DB_HOST" ]; then
    echo "==> Menunggu koneksi MySQL di $DB_HOST:${DB_PORT:-3306}..."
    until nc -z -v -w30 "$DB_HOST" "${DB_PORT:-3306}"; do
        echo "Menunggu database siap..."
        sleep 2
    done
    echo "==> Database terhubung!"
fi

# Jalankan migrasi database
echo "==> Menjalankan migrasi database..."
php artisan migrate --force

# Seed default admin jika users kosong
echo "==> Memeriksa admin default..."
php artisan db:seed --class=UserSeeder --force || true

# Optimasi untuk environment production
if [ "$APP_ENV" = "production" ]; then
    echo "==> Mengaktifkan cache konfigurasi, route, dan view untuk production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "==> Inisialisasi selesai! Menjalankan proses utama: $@"
exec "$@"
