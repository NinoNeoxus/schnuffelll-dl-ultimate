#!/bin/bash

# This script installs all dependencies, clones the repository into the web root,
# sets appropriate permissions, and restarts Apache on an Ubuntu VPS.

set -e

echo "==> Memperbarui paket..."
sudo apt-get update -y

echo "==> Menginstal dependensi..."
sudo apt-get install -y apache2 php libapache2-mod-php python3 python3-pip ffmpeg git unzip

echo "==> Memasang yt-dlp..."
# Upgrade pip and install the latest yt-dlp
sudo python3 -m pip install --upgrade pip
sudo python3 -m pip install --no-cache-dir -U yt-dlp

echo "==> Menyiapkan direktori web..."
sudo rm -rf /var/www/html/*

echo "==> Mengkloning repositori..."
# Ganti NinoNeoxus dengan username GitHub Anda jika diperlukan
sudo git clone https://github.com/NinoNeoxus/schnuffelll-dl-ultimate /var/www/html

echo "==> Mengatur izin..."
sudo mkdir -p /var/www/html/downloads
sudo chmod -R 777 /var/www/html/downloads

echo "==> Memulai ulang layanan Apache..."
sudo systemctl restart apache2

IP=$(hostname -I | awk '{print $1}')
echo "✅ INSTALASI SELESAI! Buka Website: http://$IP"