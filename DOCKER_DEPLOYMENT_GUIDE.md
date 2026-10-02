# Panduan Deployment Aplikasi ke VPS Menggunakan Docker

Dokumen ini berisi panduan langkah demi langkah untuk menjalankan aplikasi **Realisasi Belanja Pegawai & Rekonsiliasi SIMGAJI (KONBELPEG)** di VPS (Virtual Private Server) menggunakan Docker & Docker Compose.

---

## 1. Prasyarat di Server VPS

Pastikan VPS Anda (disarankan **Ubuntu 22.04 LTS / 24.04 LTS**) sudah terinstal Docker dan Docker Compose.

### A. Update Server & Instal Docker
Jika VPS masih baru, jalankan perintah berikut:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git ufw

# Instal Docker & Docker Compose Plugin resmi
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Berikan izin ke user non-root (opsional tapi disarankan)
sudo usermod -aG docker $USER
newgrp docker
```

### B. Siapkan Swap Memory (Sangat Disarankan untuk VPS RAM 1GB - 2GB)
Aplikasi memproses file DBF SIMGAJI dan ekspor Excel/PDF yang membutuhkan alokasi memori saat proses batch.
```bash
# Buat file swap sebesar 2 GB (atau 4 GB)
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Jadikan permanen saat reboot
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 2. Langkah Deployment Aplikasi

### Langkah 1: Clone Repositori ke VPS
```bash
cd /var/www  # atau di home directory, misal: ~/apps
git clone <URL_REPOSITORY_ANDA> konbelpeg
cd konbelpeg
```

### Langkah 2: Buat File `.env` dari Template Docker
```bash
cp .env.docker.example .env
```
Buka file `.env` menggunakan nano/vim:
```bash
nano .env
```
Sesuaikan nilai-nilai penting:
* `APP_URL`: Ganti dengan domain atau IP publik VPS Anda (misal `http://belanja.pemda.go.id` atau `http://103.xxx.xxx.xxx`).
* `DB_PASSWORD`: Ganti dengan password database yang kuat.
* `DB_ROOT_PASSWORD`: Ganti dengan root password database yang kuat.
* `APP_PORT`: Port web (default `80`, atau ganti misal `8080` jika di VPS sudah ada Nginx host).

### Langkah 3: Build & Jalankan Container
Jalankan perintah berikut:
```bash
docker compose up -d --build
```

Container akan otomatis melakukan:
1. Build environment PHP 8.4-FPM beserta ekstensi GD, Zip, PDO MySQL, OPcache, dll.
2. Menyiapkan web server Nginx dengan batas upload file hingga **100MB** (aman untuk DBF SIMGAJI besar).
3. Menjalankan database MySQL 8.0.
4. Menjalankan skrip `docker/entrypoint.sh` yang otomatis melakukan:
   - Generate Application Key (`APP_KEY`) jika belum ada
   - Symbolic link storage (`php artisan storage:link`)
   - Migrasi database (`php artisan migrate --force`)
   - Seeding akun administrator default (`admin@pemda.go.id`)
   - Optimasi cache config, routes, dan views untuk production

---

## 3. Akun Administrator Bawaan (Default Login)

Setelah container berjalan, buka browser di domain atau IP VPS Anda:
* **URL Login**: `http://IP_VPS_ANDA/login`
* **Email**: `admin@pemda.go.id`
* **Password**: `password`

> [!IMPORTANT]
> Segera ganti kata sandi atau buat akun baru begitu aplikasi berhasil online untuk keamanan data daerah Anda.

---

## 4. Perintah Operasional & Manajemen Container

### Melihat Status Container
```bash
docker compose ps
```

### Melihat Log Aplikasi & Error Secara Real-Time
```bash
# Log seluruh container
docker compose logs -f

# Log container PHP aplikasi saja
docker compose logs -f app

# Log container Nginx
docker compose logs -f webserver
```

### Menjalankan Perintah Artisan di Dalam Container
```bash
# Contoh: Menjalankan migrasi manual
docker compose exec app php artisan migrate

# Contoh: Membersihkan cache
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize

# Masuk ke Tinker
docker compose exec app php artisan tinker
```

### Backup Database MySQL
```bash
docker compose exec db mysqldump -u konbelpeg_user -p konbelpeg > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Menghentikan dan Menyalakan Kembali Aplikasi
```bash
# Menghentikan
docker compose down

# Menyalakan kembali
docker compose up -d
```

### Cara Update Aplikasi Jika Ada Perubahan Kode Baru (Git Pull)
```bash
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

---

## 5. Menambahkan SSL / HTTPS (Domain Berbayar)

Jika Anda sudah mengarahkan domain (misal `belanja.pemda.go.id`) ke IP VPS, Anda bisa memasang Certbot Nginx di host server atau menggunakan Cloudflare SSL Flexible/Full.

Jika menggunakan Nginx Reverse Proxy di host VPS:
1. Set `APP_PORT=8080` di file `.env`.
2. Install Nginx & Certbot di host VPS:
   ```bash
   sudo apt install -y nginx certbot python3-certbot-nginx
   ```
3. Arahkan reverse proxy Nginx host ke `http://127.0.0.1:8080`.
4. Jalankan `sudo certbot --nginx -d belanja.pemda.go.id`.
