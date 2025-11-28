#!/bin/bash

# Script untuk melakukan auto-update kode dari repositori GitHub tanpa menghapus data pengguna.
#
# Langkah:
# 1. Masuk ke direktori /var/www/html.
# 2. Simpan folder downloads/ dan file cookies.txt ke direktori sementara.
# 3. Tarik pembaruan dari remote (origin/main) dan reset keras.
# 4. Kembalikan data yang disimpan.
# 5. Atur permission folder downloads/.
# 6. Restart Apache.
# 7. Cetak pesan sukses.

set -e

TARGET_DIR="/var/www/html"

echo "==> Memulai proses update..."

# Pastikan direktori target ada
if [ ! -d "$TARGET_DIR" ]; then
  echo "Direktori $TARGET_DIR tidak ditemukan!" >&2
  exit 1
fi

cd "$TARGET_DIR"

# Buat direktori sementara untuk menyimpan data pengguna
TMP_DIR=$(mktemp -d)

echo "==> Menyimpan data pengguna..."

# Simpan cookies jika ada
if [ -f cookies.txt ]; then
  cp cookies.txt "$TMP_DIR/cookies.txt"
fi

# Simpan folder downloads jika ada
if [ -d downloads ]; then
  cp -r downloads "$TMP_DIR/downloads"
fi

echo "==> Mengambil pembaruan dari repositori..."
git fetch --all
git reset --hard origin/main

echo "==> Memulihkan data pengguna..."
# Pulihkan cookies
if [ -f "$TMP_DIR/cookies.txt" ]; then
  cp "$TMP_DIR/cookies.txt" ./cookies.txt
fi

# Pulihkan downloads
if [ -d "$TMP_DIR/downloads" ]; then
  # Hapus folder downloads hasil update bila ada lalu kembalikan yang lama
  rm -rf downloads
  mv "$TMP_DIR/downloads" ./downloads
fi

# Bersihkan direktori sementara
rm -rf "$TMP_DIR"

echo "==> Mengatur permission folder downloads..."
chmod -R 777 downloads || true

echo "==> Merestart Apache..."
sudo systemctl restart apache2

echo "🚀 UPDATE SUKSES! Website sudah versi terbaru."