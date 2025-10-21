# Sistem API Toko Buku

REST API berbasis Flask untuk mengelola data Toko Buku dengan integrasi database MySQL.

## Fitur

- Operasi CRUD lengkap untuk toko buku
- Fungsi pencarian
- Dukungan pagination
- Validasi input
- Error handling
- Fungsi soft delete
- Integrasi database MySQL

## Petunjuk Instalasi

### Prasyarat

- Python 3.7+
- MySQL Server
- pip (Python package installer)

### Instalasi

1. **Navigasi ke direktori proyek**

   ```powershell
   cd be
   ```

2. **Buat virtual environment**

   ```powershell
   python -m venv venv
   ```

3. **Aktifkan virtual environment**

   ```powershell
   .\venv\bin\activate
   ```

4. **Install dependencies**

   ```powershell
   pip install -r requirements.txt
   ```

5. **Setup database MySQL**

   - Pastikan MySQL Server sudah terinstall dan berjalan
   - Koneksi ke MySQL dan buat database:

   ```sql
   CREATE DATABASE toko_buku_db;
   ```

6. **Konfigurasi environment variables**

   - Edit file `.env` dan update konfigurasi database:

   ```env
   # Konfigurasi Database
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=toko_buku_db
   DB_USER=root
   DB_PASSWORD=

   # Konfigurasi Aplikasi
   SECRET_KEY=kunci-rahasia-anda
   FLASK_ENV=development
   FLASK_DEBUG=True
   ```

7. **Inisialisasi database dengan data contoh**

   ```powershell
   python init_db.py
   ```

   > **Opsional:** Untuk reset database (hapus semua data dan buat ulang):
   >
   > ```powershell
   > python init_db.py --reset
   > ```

8. **Jalankan aplikasi**

   ```powershell
   python app.py
   ```

9. **Test API**
   Buka browser dan kunjungi: `http://localhost:5000/api/health`

API akan tersedia di `http://localhost:5000`

## Endpoint API

### Health Check

- `GET /api/health` - Cek apakah API berjalan

- `GET /api/bukus` - Dapatkan semua buku (dengan pagination dan pencarian)
- `GET /api/buku/{id}` - Dapatkan buku tertentu
- `POST /api/buku` - Buat buku baru
- `PUT /api/buku/{id}` - Update buku yang ada
- `DELETE /api/buku/{id}` - Hapus buku (soft delete)

### Parameter Query untuk GET /api/buku

- `page` - Nomor halaman (default: 1)
- `per_page` - Item per halaman (default: 10, max: 100)
- `search` - Query pencarian (mencari di title, author, genre)

## Struktur Data Buku

```json
{
  "id": 1,
  "title": "The Great Gatsby",
  "author": "F. Scott Fitzgerald",
  "published_date": "1925-04-10",
  "genre": "Fiction",
  "price": 10.99,
  "stock": 30,
  "description": "A novel set in the Roaring Twenties.",
  "created_at": "2025-10-19T09:31:04",
  "updated_at": "2025-10-19T09:31:04"
}
```

## Teknologi yang Digunakan

- Flask - Web framework
- SQLAlchemy - ORM
- MySQL - Database
- Marshmallow - Serialization/Validation
- Flask-CORS - Cross-origin resource sharing
- PyMySQL - MySQL connector

Terimakasih 😁😁😁
